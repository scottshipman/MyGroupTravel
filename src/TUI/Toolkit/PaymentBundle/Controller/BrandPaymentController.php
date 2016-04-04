<?php

namespace TUI\Toolkit\PaymentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

use TUI\Toolkit\PaymentBundle\Entity\BrandPayment;
use TUI\Toolkit\PaymentBundle\Form\BrandPaymentType;

use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\TourBundle\Controller\TourController;

/**
 * Payment controller.
 *
 */
class BrandPaymentController extends Controller
{

    /**
     * Lists all BrandPayment entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PaymentBundle:BrandPayment')->findAll();

        return $this->render('PaymentBundle:BrandPayment:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Payment entity.
     *
     */
    public function createAction(Request $request, $tourId)
    {
        $entity = new BrandPayment();
        $em = $this->getDoctrine()->getManager();

        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $form = $this->createCreateForm($entity, $tour);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setTour($tour);
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
     * Creates a form to create a Payment entity.
     *
     * @param Payment $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(BrandPayment $entity, $tour)

    {
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new BrandPaymentType($locale, $tour), $entity, array(
            'action' => $this->generateUrl('brand_payment_create', array('tourId' => $tour->getId())),
            'method' => 'POST',
            'attr'  => array(
                'id' => 'ajax_new_brand_payment_form'
                )
            ));

        $form->add('submit', 'submit', array('label' => 'Log New Payment'));

        return $form;
    }

    /**
     * Displays a form to create a new Payment entity.
     *
     */
    public function newAction($tourId)
    {
        $entity = new BrandPayment();

        $em = $this->getDoctrine()->getManager();

        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $form   = $this->createCreateForm($entity, $tour);

        return $this->render('PaymentBundle:BrandPayment:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'tour'  => $tour,
        ));
    }

    /**
     * Finds and displays a Payment entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PaymentBundle:BrandPayment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find  Brand Payment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PaymentBundle:BrandPayment:show.html.twig', array(
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

        $entity = $em->getRepository('PaymentBundle:BrandPayment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Brand Payment entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PaymentBundle:BrandPayment:edit.html.twig', array(
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
        $form = $this->createForm(new BrandPaymentType(), $entity, array(
            'action' => $this->generateUrl('brand_payment_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
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
            $entity = $em->getRepository('PaymentBundle:BrandPayment')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Brand Payment entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('brand_payment'));
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
            ->setAction($this->generateUrl('brand_payment_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

}
