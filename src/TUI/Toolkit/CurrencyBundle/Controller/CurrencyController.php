<?php

namespace TUI\Toolkit\CurrencyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\CurrencyBundle\Entity\Currency;
use TUI\Toolkit\CurrencyBundle\Form\CurrencyType;

/**
 * Currency controller.
 *
 */
class CurrencyController extends Controller
{

    /**
     * Lists all Currency entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CurrencyBundle:Currency')->findAll();

        return $this->render('CurrencyBundle:Currency:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Currency entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Currency();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('currency.flash.save'). $entity->getName());


          return $this->redirect($this->generateUrl('manage_currency_show', array('id' => $entity->getId())));
        }

        return $this->render('CurrencyBundle:Currency:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Currency entity.
     *
     * @param Currency $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Currency $entity)
    {
        $form = $this->createForm(new CurrencyType(), $entity, array(
            'action' => $this->generateUrl('manage_currency_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('currency.actions.create')));

        return $form;
    }

    /**
     * Displays a form to create a new Currency entity.
     *
     */
    public function newAction()
    {
        $entity = new Currency();
        $form   = $this->createCreateForm($entity);

        return $this->render('CurrencyBundle:Currency:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Currency entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CurrencyBundle:Currency')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Currency entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('CurrencyBundle:Currency:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Currency entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CurrencyBundle:Currency')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Currency entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('CurrencyBundle:Currency:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Currency entity.
    *
    * @param Currency $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Currency $entity)
    {
        $form = $this->createForm(new CurrencyType(), $entity, array(
            'action' => $this->generateUrl('manage_currency_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('currency.actions.update')));

        return $form;
    }
    /**
     * Edits an existing Currency entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CurrencyBundle:Currency')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Currency entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('currency.flash.save'). $entity->getName());

            return $this->redirect($this->generateUrl('manage_currency_edit', array('id' => $id)));
        }

        return $this->render('CurrencyBundle:Currency:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Currency entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();
          $entity = $em->getRepository('CurrencyBundle:Currency')->find($id);

          if (!$entity) {
            throw $this->createNotFoundException('Unable to find Currency entity.');
          }

          $em->remove($entity);
          $em->flush();
          $this->get('session')
            ->getFlashBag()
            ->add('notice', $this->get('translator')
                ->trans('currency.flash.delete') . $entity->getName());


          return $this->redirect($this->generateUrl('manage_currency'));
        }
    }

    /**
     * Creates a form to delete a Currency entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_currency_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('currency.actions.delete')))
            ->getForm()
        ;
    }
}
