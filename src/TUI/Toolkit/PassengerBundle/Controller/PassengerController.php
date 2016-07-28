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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;


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
        $paxCount = 0;

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
                $paxCount = $paxCount +1;
            }
            $tour->setRegistrations($tour->getRegistrations() + $paxCount);
            $em->persist($tour);

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
                $institution = $tour->getInstitution();
                $organizer_name = $tour->getOrganizer() != null ? $tour->getOrganizer()->getLastName() . ' ' . $tour->getOrganizer()->getFirstName(): NULL;
                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('passenger.emails.thank_you') . ' ' . $tourName . ', ' . $institution)
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
                                'organizer_name' => $organizer_name,
                            )
                        ), 'text/html');
                $this->get('mailer')->send($message);

            }

            $em->flush();
//            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('passenger.flash.save'));

            // Figure out if an organizer
            $flash_type = 'passenger.flash.save';
            $securityContext = $this->container->get('security.authorization_checker');
            if ($securityContext->isGranted('ROLE_CUSTOMER')) {
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $permissions = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user);

                if(in_array('organizer', $permissions)) {
                    // Show organizer specific flash message
                    $flash_type = 'passenger.flash.organizer_save';
                }
            }

            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans($flash_type));

            $serializer = $this->container->get('jms_serializer');
            $passengerObjects = $serializer->serialize($newPassengers, 'json');

            $response = new Response($passengerObjects);
            $response->headers->set('Content-Type', 'application/json');
            return $response;

//            return $this->redirect($request->server->get('HTTP_REFERER'));

        }

        //$errors = $this->get("passenger.actions")->getErrorMessages($form);
        $errors = $this->get("app.form.validation")->getNestedErrorMessages($form);
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

        $form_label = 'Submit Sign-up Request';
        // Figure out if an organizer
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('ROLE_CUSTOMER')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permissions = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user);

            if(in_array('organizer', $permissions)) {
                $form_label = 'Add Passenger';
            }
        }

        $form->add('submit', 'submit', array('label' => $form_label));

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

        $is_org = false;
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('ROLE_CUSTOMER')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permissions = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user);
            $is_org = in_array('organizer', $permissions);
        }
        

        return $this->render('PassengerBundle:Passenger:new.html.twig', array(
            'entity' => $entity,
            'locale' => $locale,
            'date_format' => $date_format,
            'tour' => $tour,
            'tourId' => $tourId,
            'user_is_org' => $is_org,
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

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $tour_permission = $this->get("permission.set_permission")->getPermission($entity->getTourReference()->getId(), 'tour', $user->getId());
            $passenger_permission = $this->get("permission.set_permission")->getPermission($id, 'passenger', $user->getId());
            $permission_pass = FALSE;

            if ($passenger_permission != NULL && in_array('parent', $passenger_permission)) {
                $permission_pass = TRUE;
            }

            if ($tour_permission != NULL && (in_array('organizer', $tour_permission) || in_array('assistant', $tour_permission))) {
                $permission_pass = TRUE;
            }

            if (!$permission_pass) {
                throw $this->createAccessDeniedException();
            }
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


        //Get Number of possible tasks on the tour
        $possibleTasksCount = $this->get("passenger.actions")->getPossibleTourTasks($entity->getTourReference()->getId());

        //Get total number of completed tasks for the passenger
        $completedTasksCount = $this->get("passenger.actions")->getPassengerCompletedTasks($id);


        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        // get payments from passenger
        $payments = $this->get('payment.getPayments')->getPassengersPaymentsPaid($id);

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PassengerBundle:Passenger:show.html.twig', array(
            'entity' => $entity,
            'brand' => $brand,
            'parent' => $parent,
            'delete_form' => $deleteForm->createView(),
            'isOrganizer' => $isOrganizer,
            'completedTasksCount' => $completedTasksCount,
            'possibleTasksCount' => $possibleTasksCount,
            'payments' => $payments,
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

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $tour_permission = $this->get("permission.set_permission")->getPermission($entity->getTourReference()->getId(), 'tour', $user->getId());
            $passenger_permission = $this->get("permission.set_permission")->getPermission($id, 'passenger', $user->getId());
            $permission_pass = FALSE;

            if ($passenger_permission != NULL && in_array('parent', $passenger_permission)) {
                $permission_pass = TRUE;
            }

            if ($tour_permission != NULL && (in_array('organizer', $tour_permission) || in_array('assistant', $tour_permission))) {
                $permission_pass = TRUE;
            }

            if (!$permission_pass) {
                throw $this->createAccessDeniedException();
            }
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

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $tour_permission = $this->get("permission.set_permission")->getPermission($entity->getTourReference()->getId(), 'tour', $user->getId());
            $passenger_permission = $this->get("permission.set_permission")->getPermission($id, 'passenger', $user->getId());
            $permission_pass = FALSE;

            if ($passenger_permission != NULL && in_array('parent', $passenger_permission)) {
                $permission_pass = TRUE;
            }

            if ($tour_permission != NULL && (in_array('organizer', $tour_permission) || in_array('assistant', $tour_permission))) {
                $permission_pass = TRUE;
            }

            if (!$permission_pass) {
                throw $this->createAccessDeniedException();
            }
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

            $dob = $entity->getDateOfBirth();
            $age = date_diff($dob, date_create('now'))->y;

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

        $errors = $this->get("app.form.validation")->getErrorMessages($editForm);
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
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        $tour_organizer = false;

        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
            $tour_organizer = in_array('organizer', $permission);
        }

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);

        $passengers = $this->addPassengerParents($all, $em);

        // get counts of status for passengers and organizers
        $participantCounts = $this->getParticipantCounts($passengers);

        //Get Pending Invite organizer list (have no passenger object created yet so we'll fake it)
        $organizers = $this->get("passenger.actions")->getOrganizers($tourId);
        //array_unshift($organizers, $tour->getOrganizer());

        $organizersObjects = $this->addOrganizerPassengers($organizers, $tourId, $em);
        if ($organizersObjects == null){
            $organizersObjects = array();
        }

        $unactivatedCount = $this->get("passenger.actions")->getUnActivatedUsers($tourId);

        // merge all records
//        $passengers = array_merge($passengers, $organizersObjects);

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
            'user_is_organzier' => $tour_organizer,
            'statusCounts' => $participantCounts,
            'brand' => $brand,
            'passengers' => $passengers,
            'unactivatedCount' => $unactivatedCount
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
                return array();
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
                $permissions = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $parentObject);

                $isOrganizer = FALSE;

                if (is_array($permissions)){
                    foreach($permissions as $permission){
                        if (($permission == 'organizer' || $permission == 'assistant') && $passenger->getSelf() == true) {
                            $isOrganizer = TRUE;
                        }
                    }
                }


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
                        $paxObject->getSelf() == TRUE &&
                        $tourId == $paxObject->getTourReference()->getId())
                    {
                        $passengerObject = $paxObject;
                        break;
                    }
                }

            } else{

                // need to add name data to fake passenger data
                $passengerObject->setfName($organizer->getFirstName());
                $passengerObject->setlname($organizer->getLastName());

            }
            $combinedObjects[]= array($passengerObject, $organizer, $isOrganizer);
        }

        return $combinedObjects;
    }

    /*
     * Get a count for each passenger and organizer statuses
     * @param $passengers an array of passenger and user objects, with a third param for isOrganizer
     * call this after calling addPassengerParents to create combined array
     */
    public function getParticipantCounts($passengers)
    {
        $count=array(
            'organizer'=>array(
                'accepted' => 0,
                'waitlist' => 0,
                'free' => 0,
            ),
            'passenger' => array(
                'accepted' => 0,
                'waitlist' => 0,
                'free' => 0,
            ),
        );

        foreach($passengers as $passenger) {
            //loop to see if organizer
            if($passenger[2]===true){
                $category = 'organizer';
            } else {
                $category = 'passenger';
            }

            if($passenger[0]->getStatus() == 'accepted' && $passenger[0]->getFree() == FALSE) {
                $count[$category]['accepted'] ++;
            }
            if($passenger[0]->getStatus() == 'waitlist') {
                $count[$category]['waitlist'] ++;
            }
            if($passenger[0]->getFree() == TRUE) {
                $count[$category]['free'] ++;
            }
        }

        return $count;
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
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == null || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $payingPlaces = $tour->getPayingPlaces();
        $freePlaces = $tour->getFreePlaces();
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);

        $passenger->setStatus("accepted");
        $passenger->setFree(false);

        $em->persist($passenger);
        $em->flush();

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);
        $passengers = $this->addPassengerParents($all, $em);

        // get counts of status for passengers and organizers
        $participantCounts = $this->getParticipantCounts($passengers);

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
            $participantCounts,
            $payingPlaces,
            $freePlaces,
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
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == null || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $payingPlaces = $tour->getPayingPlaces();
        $freePlaces = $tour->getFreePlaces();
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);

        $passenger->setStatus("waitlist");
        $passenger->setFree(false);


        $em->persist($passenger);
        $em->flush();

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);
        $passengers = $this->addPassengerParents($all, $em);

        // get counts of status for passengers and organizers
        $participantCounts = $this->getParticipantCounts($passengers);

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
            $participantCounts,
            $payingPlaces,
            $freePlaces,
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
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == null || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $payingPlaces = $tour->getPayingPlaces();
        $freePlaces = $tour->getFreePlaces();
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);

        $passenger->setStatus("accepted");
        $passenger->setFree(true);

        $em->persist($passenger);
        $em->flush();

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);
        $passengers = $this->addPassengerParents($all, $em);

        // get counts of status for passengers and organizers
        $participantCounts = $this->getParticipantCounts($passengers);

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
            $participantCounts,
            $payingPlaces,
            $freePlaces,
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
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

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
            )))
            ->add('email', 'email', array(
                'label' => $this->get('translator')->trans('passenger.form.invite.email'),
                'constraints' => array(
                    new NotBlank(array('message' => 'Email is a required field')),
                    new Email(array('message' => 'Please use a valid email address')),
                 ),
                ))
            ->add('firstname', 'text', array(
                'label' => $this->get('translator')->trans('passenger.form.invite.organizer_first'),
                'constraints' => array(
                    new NotBlank(array('message' => 'First Name is a required field')),
                ),
            ))
            ->add('lastname', 'text', array(
                 'label' => $this->get('translator')->trans('passenger.form.invite.organizer_last'),
                 'constraints' => array(
                    new NotBlank(array('message' => 'Last Name is a required field')),
                 ),
            ))
            ->add('message', 'textarea', array(
                'label' => $this->get('translator')->trans('passenger.form.invite.message'),
                'constraints' => array(
                    new NotBlank(array('message' => 'Please provide a custom message to this recipient.')),
                    ),
            ))
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
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $em = $this->getDoctrine()->getManager();

        //get locale and date format for emails sent
        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        //get current tour
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        //get tour organizer
        $organizer = $tour->getOrganizer();


        //get current user in case they arent the primary organizer
        $currUser = $this->get('security.context')->getToken()->getUser();

        $form = $this->createInviteForm($tourId);
        $form->handleRequest($request);
        if($form->isValid()) {

            $data = $form->getData();
            $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
            $subject = $this->get('translator')->trans('passenger.emails.invite-organizer.new-user-subject') . ' ' . $tour->getName();
            // check for existing user acct first
            $exists = $em->getRepository('TUIToolkitUserBundle:User')->findBy(array('email' => $data['email']));
            if(!empty($exists)){
                $user = array_shift($exists);

                // if an assistant we need to create a new passenger record if they are already registered
                $newPassenger = new Passenger();
                $newPassenger->setStatus("waitlist");
                $newPassenger->setFree(false);
                $newPassenger->setFName($data['firstname']);
                $newPassenger->setLName($data['lastname']);
                $newPassenger->setTourReference($tour);
                $newPassenger->setGender('undefined');
                $newPassenger->setDateOfBirth(new \DateTime("1987-01-01"));
                $newPassenger->setSignUpDate(new \DateTime("now"));
                $newPassenger->setSelf(true);

                $em->persist($newPassenger);
                $em->flush($newPassenger);

                // create permission for new user as assistant
                $assistant = new Permission();
                $assistant->setUser($user);
                $assistant->setClass('tour');
                $assistant->setObject($tourId);
                $assistant->setGrants('assistant');
                $em->persist($assistant);
                $em->flush();

                $newPermission = new Permission();
                $newPermission->setUser($user);
                $newPermission->setClass('passenger');
                $newPermission->setGrants('parent');
                $newPermission->setObject($newPassenger->getId());


                $em->persist($newPermission);
                $em->flush($newPermission);

                //send another email to the organizer just to confirm because they have already registered.

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($this->container->getParameter('user_system_email'))
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            'PassengerBundle:Emails:activatedPassengerOrganizerNotificationEmail.html.twig',
                            array(
                                'brand' => $brand,
                                'tour' => $tour,
                                'user' => $user,
                                'tour_name' => $tour->getName(),
                                'locale' => $locale,
                                'date_format' => $date_format,
                                'note' => $data['message'],
                                'inviter' => $currUser
                            )
                        ), 'text/html');
                $this->get('mailer')->send($message);


            } else {
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

                //Send Email to whoever was invited
                $newEmail = $user->getEmail();

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
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
            }

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

            //return successful ajax response
            $data = array(
                $user->getEmail(),
                $user->getFirstName(),
                $user->getLastName(),
                $organizersCount + 1,
            );
            $this->get('ras_flash_alert.alert_reporter')->addSuccess("An email invitation has been sent to " . $user->getFirstName() . " " . $user->getLastName() . " at " . $user->getEmail() .".");
            $responseContent = json_encode($data);
            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );
        }

        $errors = $this->get("app.form.validation")->getErrorMessages($form);
        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');

        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;
    }

    public function getPassengerStatusLabel($status) {
        if ($this->getParameter($status . "_label")) {
            return $this->getParameter($status . "_label");
        } else {
            return ucfirst($status);
        }
    }

    /*
 * Sends an email to a User when brand user clicks Notify User
 */
    public function parentRegisterConfirmationTriggerAction($id, $tourId)
    {
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == null || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $mailer = $this->container->get('mailer');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        // Create token
        $tokenGenerator = $this->container->get('fos_user.util.token_generator');

        //Get some user info
        $user->setConfirmationToken($tokenGenerator->generateToken());
        $userEmail = $user->getEmail();

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $subject = $tour->getName() . ', ' . $tour->getInstitution() . ' - ' . $this->get('translator')->trans('tour.email.registration.parent_subject');

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->container->getParameter('user_system_email'))
            ->setTo($userEmail)
            ->setBody(
                $this->renderView(
                    'PassengerBundle:Emails:inviteParentRegistrationEmail.html.twig',
                    array(
                        'brand' => $brand,
                        'user' => $user,
                    )
                ), 'text/html');

        $em->persist($user);
        $em->flush();

        $mailer->send($message);

        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.registration_notification') . ' ' .$user->getEmail());

        return $this->redirect($_SERVER['HTTP_REFERER']);

    }

    public function getActivateAllUsersAction($tourId)
    {
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $em = $this->getDoctrine()->getManager();
        $mailer = $this->container->get('mailer');

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

        $acceptedUsers = $this->get('passenger.actions')->getPassengersByStatus('accepted', $tourId);

        foreach ($acceptedUsers as $acceptedUser) {

            $object = $acceptedUser->getId();
            $user = $this->get("permission.set_permission")->getUser('parent', $object, 'passenger');
            $user = array_shift($user);
            $user = $em->getRepository('TUIToolkitUserBundle:User')->find($user);

            if($user->isEnabled() == false) {

                $tokenGenerator = $this->container->get('fos_user.util.token_generator');

                //Get some user info
                $user->setConfirmationToken($tokenGenerator->generateToken());
                $userEmail = $user->getEmail();

                $message = \Swift_Message::newInstance()
                    ->setSubject($this->get('translator')->trans('user.email.registration.subject'))
                    ->setFrom($this->container->getParameter('user_system_email'))
                    ->setTo($userEmail)
                    ->setBody(
                        $this->renderView(
                            'TUIToolkitUserBundle:Registration:register_email.html.twig',
                            array(
                                'brand' => $brand,
                                'user' => $user,
                            )
                        ), 'text/html');

                $em->persist($user);
                $em->flush();

                $mailer->send($message);
            }
        }


        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('passenger.flash.users_activation'));

        return $this->redirect($_SERVER['HTTP_REFERER']);

    }
}
