<?php

namespace TUI\Toolkit\PaymentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

use TUI\Toolkit\PaymentBundle\Entity\Payment;
use TUI\Toolkit\PaymentBundle\Form\PaymentType;
use TUI\Toolkit\TourBundle\Entity\PaymentTaskOverride;

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

                    }
                }
            }



            $em->flush();

            return $this->redirect($this->generateUrl('manage_passenger_show', array('id' => $passengerId)));
        }

        // form not valid or has errors
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

        return $this->render('PaymentBundle:Payment:passengerPaymentCard.html.twig', array(
            'due' => $due,
            'payments' => $payments,
            'paymentTasks' => $paymentTasks,
            'currency' => $currency,
            'locale' => $locale,
            'passenger' => $passenger,
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
        $form = $this->createCustomScheduleForm($tourId, $passenger);

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

        $tasks = $tour->getPaymentTasksPassenger();;
        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->setMethod('POST')
            ->setAction($this->generateUrl('payment_schedule_update', array('passengerId' => $passenger->getId())));

        foreach($tasks as $task) {
            $override=NULL;
            if($paymentOverride = $em->getRepository('TourBundle:PaymentTaskOverride')
                ->findBy(array('paymentTaskSource'=>$task->getId(), 'passenger'=>$passenger->getId()))){
            $task->setValue($paymentOverride[0]->getValue());
            $override = $paymentOverride[0]->getId();
            }
            $form->add('task' . $task->getId(), 'money', array(
                'currency' => $currency->getCode(),
                'label' => $task->getName() . ' (due '. $task->getDueDate()->format($date_format) . ')',
                'data' => $task->getValue(),
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
