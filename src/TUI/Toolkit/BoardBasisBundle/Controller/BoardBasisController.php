<?php

namespace TUI\Toolkit\BoardBasisBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use TUI\Toolkit\BoardBasisBundle\Entity\BoardBasis;
use TUI\Toolkit\BoardBasisBundle\Form\BoardBasisType;

/**
 * BoardBasis controller.
 *
 * @Route("/manage/boardbasis")
 */
class BoardBasisController extends Controller
{

    /**
     * Lists all BoardBasis entities.
     *
     * @Route("/", name="_manage_boardbasis")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BoardBasisBundle:BoardBasis')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new BoardBasis entity.
     *
     * @Route("/", name="_manage_boardbasis_create")
     * @Method("POST")
     * @Template("BoardBasisBundle:BoardBasis:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new BoardBasis();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('_manage_boardbasis_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a BoardBasis entity.
     *
     * @param BoardBasis $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(BoardBasis $entity)
    {
        $form = $this->createForm(new BoardBasisType(), $entity, array(
            'action' => $this->generateUrl('_manage_boardbasis_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('boardBasis.actions.create')));

        return $form;
    }

    /**
     * Displays a form to create a new BoardBasis entity.
     *
     * @Route("/new", name="_manage_boardbasis_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new BoardBasis();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a BoardBasis entity.
     *
     * @Route("/{id}", name="_manage_boardbasis_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BoardBasisBundle:BoardBasis')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BoardBasis entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing BoardBasis entity.
     *
     * @Route("/{id}/edit", name="_manage_boardbasis_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BoardBasisBundle:BoardBasis')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BoardBasis entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a BoardBasis entity.
    *
    * @param BoardBasis $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(BoardBasis $entity)
    {
        $form = $this->createForm(new BoardBasisType(), $entity, array(
            'action' => $this->generateUrl('_manage_boardbasis_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('boardBasis.actions.update')));

        return $form;
    }
    /**
     * Edits an existing BoardBasis entity.
     *
     * @Route("/{id}", name="_manage_boardbasis_update")
     * @Method("PUT")
     * @Template("BoardBasisBundle:BoardBasis:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BoardBasisBundle:BoardBasis')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BoardBasis entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('_manage_boardbasis_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a BoardBasis entity.
     *
     * @Route("/{id}", name="_manage_boardbasis_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BoardBasisBundle:BoardBasis')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find BoardBasis entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('_manage_boardbasis'));
    }

    /**
     * Creates a form to delete a BoardBasis entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('_manage_boardbasis_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('boardBasis.actions.delete')))
            ->getForm()
        ;
    }
}
