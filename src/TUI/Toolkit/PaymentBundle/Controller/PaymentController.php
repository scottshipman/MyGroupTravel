<?php

namespace TUI\Toolkit\PaymentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use TUI\Toolkit\PaymentBundle\Entity\Payment;
use TUI\Toolkit\PaymentBundle\Form\PaymentType;

use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\TourBundle\Form\TourSetupType;
use TUI\Toolkit\TourBundle\Controller\TourController;

/**
 * Payment controller.
 *
 */
class PaymentController extends Controller
{

    /**
     * Lists all Payment entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PaymentBundle:Payment')->findAll();

        return $this->render('PaymentBundle:Payment:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Payment entity.
     *
     */
    public function createAction(Request $request, $tourId, $passengerId)
    {
        $entity = new Payment();
        $em = $this->getDoctrine()->getManager();

        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);
        $form = $this->createCreateForm($entity, $tour, $passenger);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setTour($tour);
            $entity->setPassenger($passenger);
            $em->persist($entity);
            $em->flush();

            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('payment.flash.save'));

            $serializer = $this->container->get('jms_serializer');
            $paymentSerialized = $serializer->serialize($entity, 'json');

            $response = new Response($paymentSerialized);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

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
        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');
        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;
    }

    /**
     * Creates a form to create a Payment entity.
     *
     * @param Payment $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Payment $entity, $tour, $passenger)

    {
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new PaymentType($locale, $tour, $passenger), $entity, array(
            'action' => $this->generateUrl('payment_create', array('tourId' => $tour->getId(), 'passengerId' => $passenger->getId())),
            'method' => 'POST',
            'attr'  => array(
                'id' => 'ajax_new_payment_form'
                )
            ));

        $form->add('submit', 'submit', array('label' => 'Log New Payment'));

        return $form;
    }

    /**
     * Displays a form to create a new Payment entity.
     *
     */
    public function newAction($tourId, $passengerId)
    {
        $entity = new Payment();

        $em = $this->getDoctrine()->getManager();

        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);
        $form   = $this->createCreateForm($entity, $tour, $passenger);

        return $this->render('PaymentBundle:Payment:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'tour'  => $tour,
            'passenger' => $passenger,
        ));
    }

    /**
     * Finds and displays a Payment entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PaymentBundle:Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PaymentBundle:Payment:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Payment entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PaymentBundle:Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PaymentBundle:Payment:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Payment entity.
    *
    * @param Payment $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Payment $entity)
    {
        $form = $this->createForm(new PaymentType(), $entity, array(
            'action' => $this->generateUrl('payment_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Payment entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PaymentBundle:Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('payment_edit', array('id' => $id)));
        }

        return $this->render('PaymentBundle:Payment:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Payment entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PaymentBundle:Payment')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Payment entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('payment'));
    }

    /**
     * Creates a form to delete a Payment entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('payment_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Getting the passenger dashboard
     * @param $tourId
     * @return Response
     */

    public function getPaymentDashboardAction($tourId)
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

        //combine all lists and get parents
        $all = $this->get("passenger.actions")->getPassengersByStatus('all', $tourId);
        $passengers = $this->get('passenger.actions')->addPassengerParents($all, $em);

        // get counts of status for passengers and organizers
        $participantCounts = $this->get('passenger.actions')->getParticipantCounts($passengers);

        //Get Pending Invite organizer list (have no passenger object created yet so we'll fake it)
        $organizers = $this->get("passenger.actions")->getOrganizers($tourId);
        array_unshift($organizers, $tour->getOrganizer());

        $organizersObjects = $this->get('passenger.actions')->addOrganizerPassengers($organizers, $tourId, $em);
        if ($organizersObjects == null){
            $organizersObjects = array();
        }

        // merge all records
        $passengers = array_merge($passengers, $organizersObjects);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('PaymentBundle:Payment:dashboard.html.twig', array(
            'entity' => $tour, // just to re-use the tour menu which relies on a variable called entity
            'tour' => $tour,
            'brand' => $brand,
            'passengers' => $passengers
        ));
    }

    public function getEditPaymentSettingsAction($tourId)
    {
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');
        $setupForm = $this->createTourSetupForm($tour);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $passenger_payment_tasks = $tour->getPaymentTasksPassenger();


        return $this->render('PaymentBundle:Payment:settings.html.twig', array(
            'tour' => $tour,
            'setup_form' => $setupForm->createView(),
            'date_format' => $date_format,
            'locale' => $locale,
            'brand' => $brand,
            'passenger_payment_tasks' => $passenger_payment_tasks,
        ));

    }

    /**
     * Creates a form to edit a Tour entity on first setup.
     *
     * @param Tour $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */

    public function createTourSetupForm(Tour $entity)
    {
        $locale = $this->container->getParameter('locale');
        $setupForm = $this->createForm(new TourSetupType($locale), $entity, array(
            'action' => $this->generateUrl('manage_tour_setup', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $setupForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.save')));

        return $setupForm;
    }
}
