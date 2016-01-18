<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\PermissionBundle\Entity\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Form;


use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;
use TUI\Toolkit\PassengerBundle\Form\TourPassengerType;


/**
 * Passenger controller.
 *
 */
class PassengerController extends Controller
{

    /**
     * Lists all Passenger entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PassengerBundle:Passenger')->findAll();

        return $this->render('PassengerBundle:Passenger:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new Passenger entity.
     *
     */
    public function createAction(Request $request, $tourId)
    {
        $entity = new Passenger();
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $entity->setTourReference($tour);
        $form = $this->createCreateForm($entity, $tourId);
        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $userEmail = $form->get('email')->getData();
            $user = $em->getRepository('TUIToolkitUserBundle:User')->findOneByEmail($userEmail);

            if (!$user) {
                //do some stuff
                $user = new User();
                $user->setUsername($form->get('email')->getData());
                $user->setPassword('');
                $user->setEmail($form->get('email')->getData());
                $user->setFirstName($form->get('firstName')->getData());
                $user->setLastName($form->get('lastName')->getData());
                $user->setRoles(array('ROLE_CUSTOMER'));
                $em->persist($user);
                $em->flush();
            }

            $parentPermission = $this->get("permission.set_permission")->setPermission($tourId, 'tour', $user, 'parent');


            $newPassengers = array();
            foreach ($form->get('passengers') as $passenger) {
                //do more stuff
                $newPassenger = new Passenger();
                $newPassenger->setDateOfBirth($passenger->get('dateOfBirth')->getData());
                $newPassenger->setFName($passenger->get('fName')->getData());
                $newPassenger->setGender($passenger->get('gender')->getData());
                $newPassenger->setLName($passenger->get('lName')->getData());
                $newPassenger->setStatus("waitlist");
                $newPassenger->setFree(false);
                $newPassenger->setSignUpDate(new \DateTime());
                $newPassenger->setTourReference($tour);
                $em->persist($newPassenger);
                $em->flush();

                //Add passenger to the new passenger array to access later
                $newPassengers[] = $newPassenger;

                $permission = new Permission();
                $permission->setClass('passenger');
                $permission->setObject($newPassenger->getId());
                $permission->setGrants('parent');
                $permission->setUser($user);
                $em->persist($permission);
                $em->flush();
            }

            //brand stuff
            $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

            // look for a configured brand
            if($brand_id = $this->container->getParameter('brand_id')){
                $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
            }

            if(!$brand) {
                $brand = $default_brand;
            }

            //Query builder for waitlist
            $qb = $em->createQueryBuilder();
            $qb->select('p')
                ->from('PassengerBundle:Passenger', 'p')
                ->where($qb->expr()->andX(
                    $qb->expr()->eq('p.status', '?1'),
                    $qb->expr()->eq('p.tourReference', '?2')
                ));
            $qb->setParameters(array(1 => 'waitlist', 2 => $tourId));
            $query = $qb->getQuery();
            $waitList = $query->getScalarResult();

            $waitListObjects = array();

            foreach($waitList as $passenger) {
                $object = $passenger['p_id'];
                $parent = $this->get("permission.set_permission")->getUser('parent', $object, 'passenger');
                $parentObject = $em->getRepository('TUIToolkitUserBundle:User')->find($parent[1]);
                $waitListObjects[]= array($passenger, $parentObject);
            }

            //get number of paying places in the tour
            $payingPlaces = $tour->getPayingPlaces();

            //Send email to the organizer if the organizer account the organizer account is enabled
            if ( $tour->getOrganizer() != null ) {

                $organizerEmail = $tour->getOrganizer()->getEmail();
                $tourName = $tour->getName();

                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('passenger.emails.notifications'))
                    ->setFrom($this->container->getParameter('user_system_email'))
                    ->setTo($organizerEmail)
                    ->setBody(
                        $this->renderView(
                            'PassengerBundle:Emails:newPassengerOrganizerNotificationEmail.html.twig',
                            array(
                                'brand' => $brand,
                                'tour' => $tour,
                                'user' => $user,
                                'newPassengers' => $newPassengers,
                                'tour_name' => $tourName,
                                'payingPlaces' => $payingPlaces,
                                'waitlist' => $waitListObjects,
                                'locale' => $locale,
                                'date_format' => $date_format,
                            )
                        ), 'text/html');
                $this->get('mailer')->send($message);
            }

            //Send Email to parent who filled out the form
            if ($user->getEmail() != null ) {

                $parentEmail = $user->getEmail();
                $tourName = $tour->getName();

                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('passenger.emails.thank_you'))
                    ->setFrom($this->container->getParameter('user_system_email'))
                    ->setTo($parentEmail)
                    ->setBody(
                        $this->renderView(
                            'PassengerBundle:Emails:newPassengerEmail.html.twig',
                            array(
                                'brand' => $brand,
                                'tour' => $tour,
                                'tour_name' => $tourName,
                                'user' => $user,
                                'newPassengers' => $newPassengers,
                                'locale' => $locale,
                                'date_format' => $date_format,
                            )
                        ), 'text/html');
                $this->get('mailer')->send($message);

            }

            $em->flush();
//            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('passenger.flash.save'));
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('passenger.flash.save'));


            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        $errors = $this->get("passenger.actions")->getErrorMessages($form);

        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');

        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;

    }


    /**
     * Creates a form to create a Passenger entity.
     *
     * @param Passenger $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Passenger $entity, $tourId)
    {
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new TourPassengerType($locale, $tourId), $entity, array(
            'action' => $this->generateUrl('manage_passenger_create', array("tourId" => $tourId)),
            'method' => 'POST',
            'attr'  => array (
                'id' => 'ajax_passenger_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Passenger entity.
     *
     */
    public function newAction($tourId)
    {
        $entity = new Passenger();
        $form = $this->createCreateForm($entity, $tourId);
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        return $this->render('PassengerBundle:Passenger:new.html.twig', array(
            'entity' => $entity,
            'locale' => $locale,
            'date_format' => $date_format,
            'tour' => $tour,
            'tourId' => $tourId,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Passenger entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passenger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passenger entity.');
        }

        //Get the parent object of each passenger
        $parentId = $this->get("permission.set_permission")->getUser('parent', $id, 'passenger');
        $parent = $em->getRepository('TUIToolkitUserBundle:User')->find($parentId[1]);

            // is parent an organizer?
            $parentRoles = $this->get("permission.set_permission")->getPermission($entity->getTourReference()->getId(), 'tour', $parent);
            $isOrganizer=NULL;
            if(is_array($parentRoles) && in_array('assistant', $parentRoles)) {
                $isOrganizer = $this->get('translator')->trans('passenger.labels.assistant-organizer');
            }
            if(is_array($parentRoles) && in_array('organizer', $parentRoles)) {
                $isOrganizer = $this->get('translator')->trans('passenger.labels.primary-organizer');
            }


        // check permissions for who can see passenger
        $currUser = $this->get('security.context')->getToken()->getUser();
        $currRole =  $this->get("permission.set_permission")->getPermission($entity->getTourReference()->getId(), 'tour', $currUser);
        // is brand or admin
        if(!$this->get('security.context')->isGranted('ROLE_BRAND')){
            // is organizer or assistant of tour
            if(!in_array('assistant', $currRole) && !in_array('organizer', $currRole)) {
                // is parent of this passenger
                if(!$parentId[1] == $currUser->getId()) {
                    throw $this->createAccessDeniedException('You are not authorized to manage passengers for this tour!');
                }
            }
        }


        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PassengerBundle:Passenger:show.html.twig', array(
            'entity' => $entity,
            'brand' => $brand,
            'parent' => $parent,
            'delete_form' => $deleteForm->createView(),
            'isOrganizer' => $isOrganizer,
        ));
    }

    /**
     * Displays a form to edit an existing Passenger entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passenger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passenger entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PassengerBundle:Passenger:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Passenger entity.
     *
     * @param Passenger $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Passenger $entity)
    {

        $em = $this->getDoctrine()->getManager();

        $tourId = $entity->getTourReference()->getId();

        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new PassengerType($locale, $tourId), $entity, array(
            'action' => $this->generateUrl('manage_passenger_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'  => array (
                'id' => 'ajax_passenger_edit_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Passenger entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passenger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passenger entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        $media = $editForm->getData()->getMedia();

        //Get and set passenger media
        if ($media) {
            if(is_object($media) and !$media->getId()){
                $entity->setMedia(null);
            }elseif(is_object($media) and $media->getId()){
                $entity->setMedia($media);
            }else {
                $media = $em->getRepository('MediaBundle:Media')->find($media);
                $entity->setMedia($media);
            }
        }
        elseif (!$media){
            $entity->setMedia(null);
        }

        if ($editForm->isValid()) {
            $em->flush();

            $dob = $entity->getDateOfBirth()->format('Y');
            $age = date_diff(date_create($dob), date_create('now'))->y;

            $data = array (
                $entity->getFName(),
                $entity->getLName(),
                $age,
                $entity->getGender(),
                $entity->getId(),
            );


            if ($entity->getMedia() != null) {
                $data[] = $media->getRelativePath();
                $data[] = $media->getHashedFileName();
            }

            $responseContent =  json_encode($data);
            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );

        }

        $errors = $this->get("passenger.actions")->getErrorMessages($editForm);


        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');

        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;

    }

    /**
     * Deletes a Passenger entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PassengerBundle:Passenger')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Passenger entity.');
            }

            $em->remove($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('manage_passenger'));
        }

        return $this->redirect($this->generateUrl('manage_passenger'));
    }

    /**
     * Creates a form to delete a Passenger entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_passenger_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }



    /**
     * Getting the passenger dashboard
     * @param $tourId
     * @return Response
     */

    public function getPassengerDashboardAction($tourId)
    {
        //check permissions first
        $currUser = $this->get('security.context')->getToken()->getUser();
        $currRole =  $this->get("permission.set_permission")->getPermission($tourId, 'tour', $currUser);

        if(!$this->get('security.context')->isGranted('ROLE_BRAND')){
            if(!in_array('assistant', $currRole) && !in_array('organizer', $currRole)) {
                throw $this->createAccessDeniedException('You are not authorized to manage passengers for this tour!');
            }
        }



        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        //Get Waitlist Passengers
        $waitList = $this->get("passenger.actions")->getPassengersByStatus('waitlist', $tourId);
        $waitListCount = count($waitList);

        //Get accepted passengers
        $accepted = $this->get("passenger.actions")->getPassengersByStatus('accepted', $tourId);
        $acceptedCount = count($accepted);

        //Get free passengers
        $free = $this->get("passenger.actions")->getPassengersByStatus('free', $tourId);
        $freeCount = count($free);

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);
        $passengers = $this->addPassengerParents($all, $em);

        //Get organizer list
        $organizers = $this->get("passenger.actions")->getOrganizers($tourId);
        array_unshift($organizers, $tour->getOrganizer());
        $organizersCount = count($organizers);
        $organizersObjects = $this->addOrganizerPassengers($organizers, $tourId, $em);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('PassengerBundle:Passenger:dashboard.html.twig', array(
            'entity' => $tour, // just to re-use the tour menu which relies on a variable called entity
            'tour' => $tour,
            'waitlistcount' => $waitListCount,
            'acceptedcount' => $acceptedCount,
            'freecount' => $freeCount,
            'organizerscount' => $organizersCount,
            'organizerobjects' => $organizersObjects,
            'brand' => $brand,
            'passengers' => $passengers
        ));
    }

    /**
     * Helper function to build array of passenger, parent and boolean organizer flag
     * @param passengerList, entity manager
     * @return array
     *
     */
    public function addPassengerParents($passengers, $em)
        {
            $combinedObjects = array();

            if (empty($passengers)) {
                return NULL;
            }

            foreach($passengers as $passenger) {
            $object = $passenger->getId();
            $tourId = $passenger->getTourReference()->getId();
            $parent = $this->get("permission.set_permission")->getUser('parent', $object, 'passenger');

            if (!empty($parent)){
                $parentObject = $em->getRepository('TUIToolkitUserBundle:User')->find($parent[1]);
            } else {
                $parentObject = "";
            }
                $isOrganizer = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $parentObject)[0]=='organizer' ? TRUE : FALSE;
                $isOrganizer = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $parentObject)[0]=='assistant' ? TRUE : $isOrganizer;


                $combinedObjects[]= array($passenger, $parentObject, $isOrganizer);
             }

            return $combinedObjects;
        }

    /**
     * Helper function to build array of organizer, passenger and boolean organizer flag
     * @param organizerList, entity manager
     * @return array
     *
     */
    public function addOrganizerPassengers($organizers, $tourId, $em)
    {
        $combinedObjects = array();

        if (empty($organizers)) {
            return NULL;
        }

        foreach($organizers as $organizer) {

            $passengerObject = new Passenger();
            $passengerObject->setStatus('Pending Invite');

            $user = $organizer->getId();
            $passenger = $this->get("permission.set_permission")->getObject('parent', $user, 'passenger');
            $isOrganizer = TRUE;

            if (!empty($passenger)){
                // could be more than one passenger object
                foreach($passenger as $pax){
                    $paxObject = $em->getRepository('PassengerBundle:Passenger')->find($pax['object']);
                    if( $paxObject &&
                        /*strtolower(trim($organizer->getFirstName())) == strtolower(trim($paxObject->getFName())) &&
                        strtolower(trim($organizer->getLastName())) == strtolower(trim($paxObject->getLName())) &&*/
                        $paxObject->getSelf == TRUE &&
                        $tourId == $paxObject->getTourReference()->getId())
                    {
                        $passengerObject = $paxObject;
                        $break;
                    }
                }

            }
            $combinedObjects[]= array($passengerObject, $organizer, $isOrganizer);
        }

        return $combinedObjects;
    }

    /**
     * Getting the parent passenger dashboard
     * @param $tourId
     * @return Response
     */

    public function getParentDashboardAction($tourId) {

        //check permissions first
        $currUser = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('PassengerBundle:Passenger:parentDashboard.html.twig', array(
            'entity' => $tour, // just to re-use the tour menu which relies on a variable called entity
            'tour' => $tour,
            'brand' => $brand,
        ));
    }

    /**
     * @param Request $request
     * @param $tourId
     * @param $passengerId
     * @return Response
     */

    public function moveToAcceptedAction(Request $request, $tourId, $passengerId)
    {

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $payingPlaces = $tour->getPayingPlaces();
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);

        $passenger->setStatus("accepted");
        $passenger->setFree(false);

        $em->persist($passenger);
        $em->flush();

        //Get Waitlist Passengers
        $waitList = $this->get("passenger.actions")->getPassengersByStatus('waitlist', $tourId);
        $waitListCount = count($waitList);

        //Get accepted passengers
        $accepted = $this->get("passenger.actions")->getPassengersByStatus('accepted', $tourId);
        $acceptedCount = count($accepted);

        //Get free passengers
        $free = $this->get("passenger.actions")->getPassengersByStatus('free', $tourId);
        $freeCount = count($free);

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);
        $passengers = $this->addPassengerParents($all, $em);

        //Get organizer list
        $organizers = $this->get("passenger.actions")->getOrganizers($tourId);
        $organizersCount = count($organizers);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }


        $data = array (
            $passenger,
            $acceptedCount,
            $waitListCount,
            $freeCount,
            $payingPlaces,
            $organizersCount,
            $passengers,
        );

        $responseContent =  json_encode($data);
        return new Response($responseContent,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }

    public function moveToWaitlistAction(Request $request, $tourId, $passengerId)
    {

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $payingPlaces = $tour->getPayingPlaces();
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);

        $passenger->setStatus("waitlist");
        $passenger->setFree(false);


        $em->persist($passenger);
        $em->flush();

        //Get Waitlist Passengers
        $waitList = $this->get("passenger.actions")->getPassengersByStatus('waitlist', $tourId);
        $waitListCount = count($waitList);

        //Get accepted passengers
        $accepted = $this->get("passenger.actions")->getPassengersByStatus('accepted', $tourId);
        $acceptedCount = count($accepted);

        //Get free passengers
        $free = $this->get("passenger.actions")->getPassengersByStatus('free', $tourId);
        $freeCount = count($free);

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);
        $passengers = $this->addPassengerParents($all, $em);

        //Get organizer list
        $organizers = $this->get("passenger.actions")->getOrganizers($tourId);
        $organizersCount = count($organizers);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }


        $data = array (
            $passenger,
            $acceptedCount,
            $waitListCount,
            $freeCount,
            $payingPlaces,
            $organizersCount,
            $passengers,
        );

        $responseContent =  json_encode($data);
        return new Response($responseContent,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }

    public function moveToFreeAction(Request $request, $tourId, $passengerId)
    {

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $payingPlaces = $tour->getPayingPlaces();
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);

        $passenger->setStatus("accepted");
        $passenger->setFree(true);

        $em->persist($passenger);
        $em->flush();

        //Get Waitlist Passengers
        $waitList = $this->get("passenger.actions")->getPassengersByStatus('waitlist', $tourId);
        $waitListCount = count($waitList);

        //Get accepted passengers
        $accepted = $this->get("passenger.actions")->getPassengersByStatus('accepted', $tourId);
        $acceptedCount = count($accepted);

        //Get free passengers
        $free = $this->get("passenger.actions")->getPassengersByStatus('free', $tourId);
        $freeCount = count($free);

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);
        $passengers = $this->addPassengerParents($all, $em);

        //Get organizer list
        $organizers = $this->get("passenger.actions")->getOrganizers($tourId);
        $organizersCount = count($organizers);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }


        $data = array (
            $passenger,
            $acceptedCount,
            $waitListCount,
            $freeCount,
            $payingPlaces,
            $organizersCount,
            $passengers,
        );

        $responseContent =  json_encode($data);
        return new Response($responseContent,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }

    /**
     * Create action for invite organizer.
     *
     * @param tourId
     *
     * @return twig with \Symfony\Component\Form\Form The form
     */
    public function inviteOrganizerAction(Request $request, $tourId)
    {
        $form  = $this->createInviteForm($tourId);

        return $this->render('PassengerBundle:Passenger:inviteOrganizer.html.twig', array(
            'tourId' => $tourId,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Invite Organizer form.
     *
     * @param tourId
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createInviteForm($tourId)
    {
        $data=array();
        $form = $this->createFormBuilder($data, array(
            'action' => $this->generateUrl('invite_organizer_submit', array('tourId' => $tourId)),
            'method' => 'POST',
            'attr'  => array (
                'id' => 'ajax_invite_organizer'
            ),))
            ->add('email', 'email', array('label' => $this->get('translator')->trans('passenger.form.invite.email')))
            ->add('firstname', 'text', array('label' => $this->get('translator')->trans('passenger.form.invite.first')))
            ->add('lastname', 'text', array('label' => $this->get('translator')->trans('passenger.form.invite.last')))
            ->add('message', 'textarea', array('label' => $this->get('translator')->trans('passenger.form.invite.message')))
            ->add('tourId', 'hidden', array(
                'data' => $tourId,
            ))
            ->getForm();

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('passenger.actions.invite')));

        return $form;
    }


    /**
     * Process Invite Organizer request
     *
     * @param tourId
     *
     *  1. Stub out a User
     *  2. Send invite email
     * @return ajax responce.
     */
    public function inviteOrganizerSubmitAction(Request $request, $tourId)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createInviteForm($tourId);
        $form->handleRequest($request);
        if($form->isValid()) {

            $data = $form->getData();

            $user = new User();
            $user->setUsername($data['email']);
            $user->setPassword('');
            $user->setEmail($data['email']);
            $user->setFirstName($data['firstname']);
            $user->setLastName($data['lastname']);
            $user->setRoles(array('ROLE_CUSTOMER'));


            // Create token
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());

            $em->persist($user);
            $em->flush();

            // create permission for new user as assistant
            $assistant = new Permission();
            $assistant->setUser($user);
            $assistant->setClass('tour');
            $assistant->setObject($tourId);
            $assistant->setGrants('assistant');
            $em->persist($assistant);
            $em->flush();

            //get current tour
            $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

            //brand stuff
            $default_brand = $em->getRepository('BrandBundle:Brand')
                ->findOneByName('ToolkitDefaultBrand');
            // look for a configured brand
            if ($brand_id = $this->container->getParameter('brand_id')) {
                $brand = $em->getRepository('BrandBundle:Brand')
                    ->find($brand_id);
            }
            if (!$brand) {
                $brand = $default_brand;
            }

            //get current user in case they arent the primary organizer
            $currUser = $this->get('security.context')->getToken()->getUser();

            //get tour organizer
            $organizer = $tour->getOrganizer();

            //get organizer count to update on response
            $organizers = $this->get("passenger.actions")->getOrganizers($tourId);
            $organizersCount = count($organizers);

            //Send email to the organizer if the organizer account the organizer account is enabled
            $organizerEmail = $organizer->getEmail();
            $currUserEmail = $currUser->getEmail();

            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')
                    ->trans('passenger.emails.invite-organizer.organizer-subject'))
                ->setFrom($this->container->getParameter('user_system_email'))
                ->setTo(array($organizerEmail, $currUserEmail))
                ->setBody(
                    $this->renderView(
                        'PassengerBundle:Emails:inviteOrganizerNotifyOrganizer.html.twig',
                        array(
                            'brand' => $brand,
                            'tour' => $tour,
                            'user' => $user,
                            'currUser' => $currUser,
                            'organizer' => $organizer,
                            'message' => $data['message'],
                        )
                    ), 'text/html');
            $this->get('mailer')->send($message);


            //Send Email to whoever was invited
            $newEmail = $user->getEmail();

            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')
                    ->trans('passenger.emails.invite-organizer.new-user-subject'))
                ->setFrom($this->container->getParameter('user_system_email'))
                ->setTo($newEmail)
                ->setBody(
                    $this->renderView(
                        'PassengerBundle:Emails:inviteOrganizerRegistrationEmail.html.twig',
                        array(
                            'brand' => $brand,
                            'tour' => $tour,
                            'user' => $user,
                            'currUser' => $currUser,
                            'organizer' => $organizer,
                            'message' => $data['message'],
                        )
                    ), 'text/html');
            $this->get('mailer')->send($message);

            //return successful ajax response
            $data = array(
                $user->getEmail(),
                $user->getFirstName(),
                $user->getLastName(),
                $organizersCount + 1,
            );

            $responseContent = json_encode($data);
            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );
        }

        $errors = $this->get("passenger.actions")->getErrorMessages($form);


        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');

        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;
    }
}
