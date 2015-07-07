<?php

namespace TUI\Toolkit\ContentBlocksBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\ContentBlocksBundle\Entity\LayoutType;
use TUI\Toolkit\ContentBlocksBundle\Form\LayoutTypeType;

/**
 * LayoutType controller.
 *
 */
class LayoutTypeController extends Controller
{

    /**
     * Lists all LayoutType entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ContentBlocksBundle:LayoutType')->findAll();

        return $this->render('ContentBlocksBundle:LayoutType:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new LayoutType entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new LayoutType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('manage_layouttype_show', array('id' => $entity->getId())));
        }

        return $this->render('ContentBlocksBundle:LayoutType:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a LayoutType entity.
     *
     * @param LayoutType $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(LayoutType $entity)
    {
        $form = $this->createForm(new LayoutTypeType(), $entity, array(
            'action' => $this->generateUrl('manage_layouttype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new LayoutType entity.
     *
     */
    public function newAction()
    {
        $entity = new LayoutType();
        $form   = $this->createCreateForm($entity);

        return $this->render('ContentBlocksBundle:LayoutType:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a LayoutType entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:LayoutType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find LayoutType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ContentBlocksBundle:LayoutType:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing LayoutType entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:LayoutType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find LayoutType entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ContentBlocksBundle:LayoutType:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a LayoutType entity.
    *
    * @param LayoutType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(LayoutType $entity)
    {
        $form = $this->createForm(new LayoutTypeType(), $entity, array(
            'action' => $this->generateUrl('manage_layouttype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing LayoutType entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:LayoutType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find LayoutType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('manage_layouttype_edit', array('id' => $id)));
        }

        return $this->render('ContentBlocksBundle:LayoutType:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a LayoutType entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ContentBlocksBundle:LayoutType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find LayoutType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('manage_layouttype'));
    }

    /**
     * Creates a form to delete a LayoutType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_layouttype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
