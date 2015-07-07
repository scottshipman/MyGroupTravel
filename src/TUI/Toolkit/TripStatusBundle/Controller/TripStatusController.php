<?php

namespace TUI\Toolkit\TripStatusBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\TripStatusBundle\Entity\TripStatus;
use TUI\Toolkit\TripStatusBundle\Form\TripStatusType;

/**
 * TripStatus controller.
 *
 */
class TripStatusController extends Controller
{

    /**
     * Lists all TripStatus entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TripStatusBundle:TripStatus')->findAll();

        return $this->render('TripStatusBundle:TripStatus:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new TripStatus entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new TripStatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('manage_tripstatus_show', array('id' => $entity->getId())));
        }

        return $this->render('TripStatusBundle:TripStatus:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a TripStatus entity.
     *
     * @param TripStatus $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(TripStatus $entity)
    {
        $form = $this->createForm(new TripStatusType(), $entity, array(
            'action' => $this->generateUrl('manage_tripstatus_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new TripStatus entity.
     *
     */
    public function newAction()
    {
        $entity = new TripStatus();
        $form   = $this->createCreateForm($entity);

        return $this->render('TripStatusBundle:TripStatus:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a TripStatus entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TripStatusBundle:TripStatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TripStatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TripStatusBundle:TripStatus:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing TripStatus entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TripStatusBundle:TripStatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TripStatus entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TripStatusBundle:TripStatus:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a TripStatus entity.
    *
    * @param TripStatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(TripStatus $entity)
    {
        $form = $this->createForm(new TripStatusType(), $entity, array(
            'action' => $this->generateUrl('manage_tripstatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing TripStatus entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TripStatusBundle:TripStatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TripStatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('manage_tripstatus_edit', array('id' => $id)));
        }

        return $this->render('TripStatusBundle:TripStatus:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a TripStatus entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TripStatusBundle:TripStatus')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find TripStatus entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('manage_tripstatus'));
    }

    /**
     * Creates a form to delete a TripStatus entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_tripstatus_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
