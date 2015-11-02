<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;

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
    public function createAction(Request $request)
    {
        $entity = new Passenger();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('manage_passenger_show', array('id' => $entity->getId())));
        }

        return $this->render('PassengerBundle:Passenger:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Passenger entity.
     *
     * @param Passenger $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Passenger $entity)
    {
        $form = $this->createForm(new PassengerType(), $entity, array(
            'action' => $this->generateUrl('manage_passenger_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Passenger entity.
     *
     */
    public function newAction()
    {
        $entity = new Passenger();
        $form   = $this->createCreateForm($entity);

        return $this->render('PassengerBundle:Passenger:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
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
            'entity'      => $entity,
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
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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
            ->getForm()
        ;
    }
}
