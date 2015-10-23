<?php

namespace TUI\Toolkit\TourBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\TourBundle\Entity\PaymentTask;
use TUI\Toolkit\TourBundle\Form\PaymentTaskType;

/**
 * PaymentTask controller.
 *
 */
class PaymentTaskController extends Controller
{

    /**
     * Lists all PaymentTask entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TourBundle:PaymentTask')->findAll();

        return $this->render('TourBundle:PaymentTask:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new PaymentTask entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new PaymentTask();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('paymenttask_show', array('id' => $entity->getId())));
        }

        return $this->render('TourBundle:PaymentTask:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PaymentTask entity.
     *
     * @param PaymentTask $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PaymentTask $entity)
    {
        $form = $this->createForm(new PaymentTaskType(), $entity, array(
            'action' => $this->generateUrl('paymenttask_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.payment_task.actions.create')));

        return $form;
    }

    /**
     * Displays a form to create a new PaymentTask entity.
     *
     */
    public function newAction()
    {
        $entity = new PaymentTask();
        $form   = $this->createCreateForm($entity);

        return $this->render('TourBundle:PaymentTask:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a PaymentTask entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:PaymentTask')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PaymentTask entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TourBundle:PaymentTask:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing PaymentTask entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:PaymentTask')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PaymentTask entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TourBundle:PaymentTask:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a PaymentTask entity.
    *
    * @param PaymentTask $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PaymentTask $entity)
    {
        $form = $this->createForm(new PaymentTaskType(), $entity, array(
            'action' => $this->generateUrl('paymenttask_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.payment_task.actions.update')));

        return $form;
    }
    /**
     * Edits an existing PaymentTask entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:PaymentTask')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PaymentTask entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('paymenttask_edit', array('id' => $id)));
        }

        return $this->render('TourBundle:PaymentTask:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a PaymentTask entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TourBundle:PaymentTask')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find PaymentTask entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('paymenttask'));
    }

    /**
     * Creates a form to delete a PaymentTask entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('paymenttask_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.payment_task.actions.delete')))
            ->getForm()
        ;
    }

    /**
     * Deletes a PaymentTask entity via ajax.
     *
     */
    public function ajaxDeleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:PaymentTask')->find($id);

        if (!$entity) {
          throw $this->createNotFoundException('Unable to Delete PaymentTask entity via ajax.');
        }

        $em->remove($entity);
        $em->flush();


        return new Response('payment task ' . $id . ' removed.');
    }
}
