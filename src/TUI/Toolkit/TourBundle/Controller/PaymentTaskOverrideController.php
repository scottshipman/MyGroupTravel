<?php

namespace TUI\Toolkit\TourBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\TourBundle\Entity\PaymentTaskOverride;
use TUI\Toolkit\TourBundle\Form\PaymentTaskOverrideType;

/**
 * PaymentTaskOverride controller.
 *
 */
class PaymentTaskOverrideController extends Controller
{

    /**
     * Lists all PaymentTaskOverride entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TourBundle:PaymentTaskOverride')->findAll();

        return $this->render('TourBundle:PaymentTaskOverride:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new PaymentTaskOverride entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new PaymentTaskOverride();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('paymenttaskoverride_show', array('id' => $entity->getId())));
        }

        return $this->render('TourBundle:PaymentTaskOverride:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PaymentTaskOverride entity.
     *
     * @param PaymentTaskOverride $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PaymentTaskOverride $entity)
    {
        $form = $this->createForm(new PaymentTaskOverrideType(), $entity, array(
            'action' => $this->generateUrl('paymenttaskoverride_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new PaymentTaskOverride entity.
     *
     */
    public function newAction()
    {
        $entity = new PaymentTaskOverride();
        $form   = $this->createCreateForm($entity);

        return $this->render('TourBundle:PaymentTaskOverride:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a PaymentTaskOverride entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:PaymentTaskOverride')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PaymentTaskOverride entity.');
        }

        $tourId = $entity->getPassenger()->getTourReference()->getId();

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        return $this->render('TourBundle:PaymentTaskOverride:show.html.twig', array(
            'entity'      => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing PaymentTaskOverride entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:PaymentTaskOverride')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PaymentTaskOverride entity.');
        }

        $tourId = $entity->getPassenger()->getTourReference()->getId();

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $editForm = $this->createEditForm($entity);

        return $this->render('TourBundle:PaymentTaskOverride:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a PaymentTaskOverride entity.
    *
    * @param PaymentTaskOverride $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PaymentTaskOverride $entity)
    {
        $form = $this->createForm(new PaymentTaskOverrideType(), $entity, array(
            'action' => $this->generateUrl('paymenttaskoverride_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing PaymentTaskOverride entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:PaymentTaskOverride')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PaymentTaskOverride entity.');
        }

        $tourId = $entity->getPassenger()->getTourReference()->getId();

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('paymenttaskoverride_edit', array('id' => $id)));
        }

        return $this->render('TourBundle:PaymentTaskOverride:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }


}
