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

    private function getErrorMessages(Form $form) {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
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

            $newPassengers = array();
            foreach ($form->get('passengers') as $passenger) {
                //do more stuff
                $newPassenger = new Passenger();
                $newPassenger->setDateOfBirth($passenger->get('dateOfBirth')->getData());
                $newPassenger->setFName($passenger->get('fName')->getData());
                $newPassenger->setGender($passenger->get('gender')->getData());
                $newPassenger->setLName($passenger->get('lName')->getData());
                $newPassenger->setStatus("waitlist");
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
                    $qb->expr()->eq('p.status', '?1')
                ));
            $qb->setParameters(array(1 => 'waitlist' ));
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
                    ->setFrom('Notify@Toolkit.com')
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
                    ->setFrom('Notify@Toolkit.com')
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
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('passenger.flash.save'));


            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        $errors = $this->getErrorMessages($form);

//        $template = $this->renderView('PassengerBundle:Passenger:new.html.twig', array(
//            'entity' => $entity,
//            'errors' => $errors,
//            'form' => $form->createView(),
//        ));

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

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PassengerBundle:Passenger:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
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
        $form = $this->createForm(new PassengerType(), $entity, array(
            'action' => $this->generateUrl('manage_passenger_update', array('id' => $entity->getId())),
            'method' => 'PUT',
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

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('manage_passenger_edit', array('id' => $id)));
        }

        return $this->render('PassengerBundle:Passenger:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
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

    public function getPassengerDashboardAction($tourId)
    {
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        //Query builder for waitlist
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('PassengerBundle:Passenger', 'p')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('p.status', '?1')
            ));
        $qb->setParameters(array(1 => 'waitlist' ));
        $query = $qb->getQuery();
        $waitList = $query->getScalarResult();

        $waitListObjects = array();

        foreach($waitList as $passenger) {
            $object = $passenger['p_id'];
            $parent = $this->get("permission.set_permission")->getUser('parent', $object, 'passenger');
            $parentObject = $em->getRepository('TUIToolkitUserBundle:User')->find($parent[1]);
            $waitListObjects[]= array($passenger, $parentObject);
        }



        //Query builder for accepted
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('PassengerBundle:Passenger', 'p')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('p.status', '?1')
            ));
        $qb->setParameters(array(1 => 'accepted' ));
        $query = $qb->getQuery();
        $accepted = $query->getScalarResult();

        $acceptedObjects = array();
        foreach($accepted as $passenger) {
            $object = $passenger['p_id'];
            $parent = $this->get("permission.set_permission")->getUser('parent', $object, 'passenger');
            $parentObject = $em->getRepository('TUIToolkitUserBundle:User')->find($parent[1]);
            $acceptedObjects[]= array($passenger, $parentObject);
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

        return $this->render('PassengerBundle:Passenger:dashboard.html.twig', array(
            'tour' => $tour,
            'waitlistobjects' => $waitListObjects,
            'acceptedobjects' => $acceptedObjects,
            'brand' => $brand,
        ));
    }
}
