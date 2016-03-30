<?php

namespace TUI\Toolkit\PaymentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormError;

use TUI\Toolkit\PaymentBundle\Entity\Payment;
use TUI\Toolkit\PaymentBundle\Form\PaymentType;
use TUI\Toolkit\PaymentBundle\Form\RefundType;
use TUI\Toolkit\TourBundle\Entity\PaymentTaskOverride;

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

        $errors = $this->get("app.form.validation")->getErrorMessages($form);
        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');
        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;
    }


    /**
     * Creates a new Refund of payment entity.
     *
     */
    public function refundAction(Request $request, $tourId)
    {
        $entity = new Payment();
        $em = $this->getDoctrine()->getManager();

        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $passengers = $em->getRepository('PassengerBundle:Passenger')->findBy(array('tourReference' => $tour));
        $passenger = array();
        foreach($passengers as $pax) {
            $passenger[$pax->getId()] = $pax->getFName() . ' ' . $pax->getLName();
        }
        $form = $this->createRefundForm($entity, $tour, $passenger);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setTour($tour);
            $pax = $form->getData()->getPassenger();
            $paxObject = $em->getRepository('PassengerBundle:Passenger')->find($pax);
           // $entity->setPassenger($passenger);
            $entity->setValue(-abs($entity->getValue()));
            $entity->setPassenger($paxObject);
            $em->persist($entity);
            $em->flush();

            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('payment.flash.refund_save'));

            $serializer = $this->container->get('jms_serializer');
            $paymentSerialized = $serializer->serialize($entity, 'json');

            $response = new Response($paymentSerialized);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $errors = $this->get("app.form.validation")->getErrorMessages($form);
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
     * Creates a form to create a Refund Payment entity.
     *
     * @param Payment $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRefundForm(Payment $entity, $tour, $passenger)

    {
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new RefundType($locale, $tour, $passenger), $entity, array(
            'action' => $this->generateUrl('payment_refund', array('tourId' => $tour->getId())),
            'method' => 'POST',
            'attr'  => array(
                'id' => 'ajax_new_refund_form'
            )
        ));

        $form->add('submit', 'submit', array('label' => 'Issue Refund'));

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
     * Displays a form to create a new Refund Payment entity.
     *
     */
    public function newRefundAction($tourId)
    {
        $entity = new Payment();

        $em = $this->getDoctrine()->getManager();

        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $passengers = $em->getRepository('PassengerBundle:Passenger')->findBy(array('tourReference' => $tour));
        $passenger = array();
        foreach($passengers as $pax) {
          $passenger[$pax->getId()] = $pax->getFName() . ' ' . $pax->getLName();
        }
        $form   = $this->createRefundForm($entity, $tour, $passenger);

        return $this->render('PaymentBundle:Payment:refund.html.twig', array(
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
     * Edits an existing Payment Schedule for a passenger entity.
     *
     */
    public function scheduleUpdateAction(Request $request, $passengerId)
    {
        $em = $this->getDoctrine()->getManager();

        $paymentTasks = $this->get("payment.getPayments")->getPassengersPaymentTasks($passengerId);
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);
        $tour = $passenger->getTourReference();
        $form = $this->createCustomScheduleForm($tour->getId(), $passenger);
        $form->handleRequest($request);
        $updated=array();
        $vdata = $form->getData();
        foreach($vdata as $data => $value){
            if((strpos($data, 'task') !== false) and $value < 0){
                $form[$data]->addError(new FormError('Value must be greater than 0'));
            }
        }


        if ($form->isValid()) {
            $data = $form->getData();
            foreach($paymentTasks as $paymentTask) {
                $passengerOverride = NULL;
                $key = $paymentTask['item']->getId();
                if($data['override'.$key] !== NULL){
                    // This is an overriden payment task, use the value of $data['override'.$key] as the record to update
                    if($data['task' . $key] !== $paymentTask['item']->getValue()){
                        //new value  so change the record
                        $passengerOverride = $em->getRepository('TourBundle:PaymentTaskOverride')->find($data['override' . $key]);
                        if ($passengerOverride !== NULL) {
                            $passengerOverride->setValue($data['task'.$key]);
                            $em->persist($passengerOverride);
                            $updated[$key] = 'default';
                        }
                    }

                } else {
                    // This is the default task...if the value is different create a new overriden task
                    if($data['task' . $key] !== $paymentTask['item']->getValue()){
                        //new value so change the record
                        $newPaymentTask = new PaymentTaskOverride();
                        $newPaymentTask->setPassenger($passenger);
                        $newPaymentTask->setPaymentTaskSource($paymentTask['item']);
                        $newPaymentTask->setValue($data['task'.$key]);
                        $em->persist($newPaymentTask);
                        $updated[$newPaymentTask->getId()] = 'override' ;

                    }
                }
            }



            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess("Payment Schedule Updated for " . $passenger->getFName() . " " . $passenger->getLName() . ".");

           // return $this->redirect($this->generateUrl('manage_passenger_show', array('id' => $passengerId)));
            $r = array (
                $passengerId,
                $updated,
            );
            $responseContent =  json_encode($r);
            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );
        }

        // form not valid or has errors
        $errors = $this->get("app.form.validation")->getErrorMessages($form);
        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');
        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('403');
        return $response;

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
                throw $this->createAccessDeniedException('You are not authorized to manage payments for this tour!');
            }
        }
        $locale = $this->container->getParameter('locale');
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        //combine all lists and get parents
        $accepted = $this->get("passenger.actions")->getPassengersByStatus('accepted', $tourId);
        $passengers = $this->get('passenger.actions')->addPassengerParents($accepted, $em);
        $payments = $this->get("payment.getPayments")->getTourPaymentsPaid($tourId);
        $due = $this->get("payment.getPayments")->getTourPaymentsDue($tourId);

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
            'passengers' => $passengers,
            'due' => $due,
            'payments' => $payments,
            'locale' => $locale,
        ));
    }

    public function getEditPaymentSettingsAction(Request $request, $tourId)
    {
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        if (!$tour) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }
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
        if($this->getRequest()->headers->get('referer')){
            $_SESSION["tour_settings_referer"]=$this->getRequest()->headers->get('referer');
        }


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
            'method' => 'POST',
            'attr'  => array(
                'id' => 'ajax_tour_setup_form'
            )
        ));

        $setupForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.save')));

        return $setupForm;
    }


    /**
     * Action to render a passenger's payment card details
     *
     * @param $passengerId
     * @return mixed
     */
    public function getPassengerPaymentCardAction($passengerId){
        $locale = $this->container->getParameter('locale');
        $em = $this->getDoctrine()->getManager();
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);
        $currency = $passenger->getTourReference()->getCurrency();

        $due = $this->get("payment.getPayments")->getPassengerPaymentsDue($passengerId);
        $payments = $this->get("payment.getPayments")->getPassengersPaymentsPaid($passengerId);
        $paymentTasks = $this->get("payment.getPayments")->getPassengersPaymentTasks($passengerId);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('PaymentBundle:Payment:passengerPaymentCard.html.twig', array(
            'due' => $due,
            'payments' => $payments,
            'paymentTasks' => $paymentTasks,
            'currency' => $currency,
            'locale' => $locale,
            'passenger' => $passenger,
            'brand' => $brand,
        ));
    }


    /**
     * Action to render a passenger's payment card details
     *
     * @param $passengerId
     * @return mixed
     */
    public function getPassengerPaymentMiniCardAction($passenger) {
        $locale = $this->container->getParameter('locale');
        $currency = $passenger[0]->getTourReference()->getCurrency();

        $due = $this->get("payment.getPayments")->getPassengerPaymentsDue($passenger[0]->getId());
        $payments = $this->get("payment.getPayments")->getPassengersPaymentsPaid($passenger[0]->getId());
        $paymentTasks = $this->get("payment.getPayments")->getPassengersPaymentTasks($passenger[0]->getId());

        $overdueAmt = 0;
        $status= 'pending';
        foreach($paymentTasks as $paymentTask) {
            if ($paymentTask['overdueAmt'] > 0){
                $overdueAmt = $overdueAmt + $paymentTask['overdueAmt'];
                $status = "overdue";
            }
        }

        return $this->render('PaymentBundle:Payment:passengerPaymentMiniCard.html.twig', array(
            'due' => $due,
            'payments' => $payments,
         //   'paymentTasks' => $paymentTasks,
            'currency' => $currency,
            'locale' => $locale,
            'pax' => $passenger,
            'status'=>$status,
            'overdue' => $overdueAmt,
        ));
    }

    /**
     * Action to generate a Custom Payment Schedule form
     *
     * @param $tourId
     * @param $passengerId
     * @return mixed
     */
    public function customizeScheduleAction($tourId, $passengerId) {
        $em = $this->getDoctrine()->getManager();
        $paymentTasks = $this->get("payment.getPayments")->getPassengersPaymentTasks($passengerId);
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);
        $currency = $passenger->getTourReference()->getCurrency();
        $form = $this->createCustomScheduleForm($tourId, $passenger, array(
            'attr' => array(
                'id' => 'ajax_custom_schedule_form'
                ),
        ));

        return $this->render('PaymentBundle:Payment:passengerCustomScheduleForm.html.twig', array(
            'paymentTasks' => $paymentTasks,
            'currency' => $currency,
            'passenger' => $passenger,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to edit a Passenger's Payment schedule.
     *
     * @param TourId the Tour ID
     * @param passenger= the full passenger object
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCustomScheduleForm($tourId, $passenger)
    {
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $currency = $tour->getCurrency();
        $locale = $this->container->getParameter('locale');
        if($locale == 'en_GB') {
            $date_format = 'd M Y';
        } else {
            $date_format = 'M d Y';
        }

        $tasks = $tour->getPaymentTasksPassenger();
        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->setMethod('POST')
            ->setAction($this->generateUrl('payment_schedule_update', array('passengerId' => $passenger->getId())));

        foreach($tasks as $task) {
            $override=NULL;
            $value = $task->getValue();
            if($paymentOverride = $em->getRepository('TourBundle:PaymentTaskOverride')
                ->findBy(array('paymentTaskSource'=>$task->getId(), 'passenger'=>$passenger->getId()))){
                //$task->setValue($paymentOverride[0]->getValue());
                $value = $paymentOverride[0]->getValue();
                $override = $paymentOverride[0]->getId();
            }
            $form->add('task' . $task->getId(), 'money', array(
                'currency' => $currency->getCode(),
                'label' => $task->getName() . ' (due '. $task->getDueDate()->format($date_format) . ')',
                'data' => $value,
                'constraints' => array(
                    new NotBlank(),
                )))
                ->add('override' . $task->getId(), 'hidden', array(
                    'data' => $override,
                ));
        }


        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form->getForm();
    }
}
