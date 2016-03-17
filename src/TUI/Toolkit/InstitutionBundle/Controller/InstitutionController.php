<?php

namespace TUI\Toolkit\InstitutionBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\InstitutionBundle\Entity\Institution;
use TUI\Toolkit\InstitutionBundle\Form\InstitutionType;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Institution controller.
 *
 */
class InstitutionController extends Controller
{

    /**
     * Lists all Institution entities.
     *
     */
    public function indexAction()
    {
        // list hidden columns
        $hidden = array();

        // Creates simple grid based on your entity (ORM)
        $source = new Entity('InstitutionBundle:Institution');

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        // Attach the source to the grid
        $grid->setSource($source);
        $grid->setId('institutiongrid');
        // set grid filter persistance so filters are remebered for whole session
        $grid->setPersistence(true);
        $grid->hideColumns($hidden);

        // Add action column
        $editAction = new RowAction('Edit', 'manage_institution_edit');
        $grid->addRowAction($editAction);
        $showAction = new RowAction('View', 'manage_institution_show');
        $grid->addRowAction($showAction);
        $deleteAction = new RowAction('Delete', 'manage_institution_quick_delete');
        $deleteAction->setRole('ROLE_ADMIN');
        $deleteAction->setConfirm(true);
        $grid->addRowAction($deleteAction);

        //manipulate the Columns
        /*      $column = $grid->getColumn('lastLogin');
              $column->setTitle('Last Login');*/

        // Set the default order of the grid
        $grid->setDefaultOrder('name', 'ASC');


        // Set the selector of the number of items per page
        $grid->setLimits(array(10, 25, 50, 100));

        //set no data message
        $grid->setNoDataMessage($this->get('translator')->trans('institution.grid.no_result'));

        // Export of the grid
        $grid->addExport(new CSVExport($this->get('translator')->trans('institution.grid.export'), "currentInstitutions", array('delimiter' => ','), "UTF-8", "ROLE_BRAND"));

        // Manage the grid redirection, exports and the response of the controller
        return $grid->getGridResponse('InstitutionBundle:Institution:index.html.twig');

    }

    /**
     * Lists all Institution entities.
     *
     */
    public function deletedAction()
    {
        // list hidden columns
        $hidden = array();
        $em = $this->getDoctrine()->getManager();
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');

        // Creates simple grid based on your entity (ORM)
        $source = new Entity('InstitutionBundle:Institution');

        //add WHERE clause
        $tableAlias = $source->getTableAlias();
        $source->manipulateQuery(
            function ($query) use ($tableAlias) {
                $query->andWhere($tableAlias . '.deleted IS NOT NULL');
            }
        );

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        // Attach the source to the grid
        $grid->setSource($source);
        $grid->setId('institutiongriddeleted');
        // set grid filter persistance so filters are remebered for whole session
        $grid->setPersistence(true);
        $grid->hideColumns($hidden);

        // Add action column
        $restoreAction = new RowAction('Restore', 'manage_institution_restore');
        $grid->addRowAction($restoreAction);

        //Add hard delete action
        $deleteAction = new RowAction('Delete', 'manage_institution_hard_delete');
        $deleteAction->setRole('ROLE_BRAND');
        $deleteAction->setConfirm(true);
        $grid->addRowAction($deleteAction);

        //manipulate the Columns
        /*      $column = $grid->getColumn('lastLogin');
              $column->setTitle('Last Login');*/

        // Set the default order of the grid
        $grid->setDefaultOrder('name', 'ASC');


        // Set the selector of the number of items per page
        $grid->setLimits(array(10, 25, 50, 100));

        //set no data message
        $grid->setNoDataMessage($this->get('translator')->trans('institution.grid.no_result_deleted'));

        // Export of the grid
        $grid->addExport(new CSVExport($this->get('translator')->trans('institution.grid.export_deleted'), "deletedInstitutions", array('delimiter' => ','), "UTF-8", "ROLE_BRAND"));

        // Manage the grid redirection, exports and the response of the controller
        return $grid->getGridResponse('InstitutionBundle:Institution:deleted.html.twig');

    }

    /**
     * Creates a new Institution entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Institution();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if (Null != $form->getData()->getMedia()){
            $fileId = $form->getData()->getMedia();
          $entities = $em->getRepository('MediaBundle:Media')
            ->findById($fileId);

          if (NULL !== $entities) {
            $media = array_shift($entities);
            $form->getData()->setMedia($media);
          }
        }


        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('institution.flash.save') . $entity->getName());

            return $this->redirect($this->generateUrl('manage_institution_show', array('id' => $entity->getId())));
        }

        return $this->render('InstitutionBundle:Institution:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }


  /**
   * Creates a new Institution entity.
   *
   */
  public function ajax_institution_createAction(Request $request)
  {
    $entity = new Institution();
    $form = $this->create_ajaxCreateForm($entity);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return new Response($entity);

     // need to return an http response

//      return $this->redirect($this->generateUrl('manage_institution_show', array('id' => $entity->getId())));
    }

      //return errors
      $errors = $this->get("app.form.validation")->getErrorMessages($form);
      $serializer = $this->container->get('jms_serializer');
      $errors = $serializer->serialize($errors, 'json');

      $response = new Response($errors);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode('400');
      return $response;
  }

    /**
     * Creates a form to create a Institution entity.
     *
     * @param Institution $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Institution $entity)
    {
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new InstitutionType($locale), $entity, array(
            'action' => $this->generateUrl('manage_institution_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('institution.actions.create')));

        return $form;
    }


  /**
   * Creates a form to create a Institution entity.
   *
   * @param Institution $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function create_ajaxCreateForm(Institution $entity)
  {
    $locale = $this->container->getParameter('locale');
    $form = $this->createForm(new InstitutionType($locale), $entity, array(
      'action' => $this->generateUrl('manage_institution_ajax_create'),
      'method' => 'POST',
      'attr'  => array (
          'id' => 'ajax_institution_form'
          ),
    ));

    $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('institution.actions.create')));

    return $form;
  }

    /**
     * Displays a form to create a new Institution entity.
     *
     */
    public function newAction()
    {
        $entity = new Institution();
        $form = $this->createCreateForm($entity);

        return $this->render('InstitutionBundle:Institution:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }


  /**
   * Displays a form to create a new Institution entity.
   *
   */
  public function new_ajaxAction()
  {
    $entity = new Institution();
    $form = $this->create_ajaxCreateForm($entity);

    return $this->render('InstitutionBundle:Institution:ajax_new.html.twig', array(
      'entity' => $entity,
      'form' => $form->createView(),
    ));
  }

    /**
     * Finds and displays a Institution entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('InstitutionBundle:Institution')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institution entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('InstitutionBundle:Institution:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Institution entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('InstitutionBundle:Institution')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institution entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('InstitutionBundle:Institution:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Institution entity.
     *
     * @param Institution $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Institution $entity)
    {
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new InstitutionType($locale), $entity, array(
            'action' => $this->generateUrl('manage_institution_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('institution.actions.update')));

        return $form;
    }

    /**
     * Edits an existing Institution entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('InstitutionBundle:Institution')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institution entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        //handling ajax request for media
        if (NULL != $editForm->getData()->getMedia()){
            $fileId = $editForm->getData()->getMedia();
          $entities = $em->getRepository('MediaBundle:Media')
            ->findById($fileId);

          if (NULL !== $entities) {
            $media = array_shift($entities);
            $editForm->getData()->setMedia($media);
          }
        }


        if ($editForm->isValid()) {
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('institution.flash.save') . $entity->getName());

            return $this->redirect($this->generateUrl('manage_institution'));
        }

        return $this->render('InstitutionBundle:Institution:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Institution entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('InstitutionBundle:Institution')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Institution entity.');
            }

            // get list of quotes related to this user first (cant delete a user if they are attached)
            // todo check for tours or other objects that might apply
            $quotes = $em->getRepository('QuoteBundle:Quote')->findOneBy(array('institution' => $entity->getId()));
            if($quotes){
                $this->get('ras_flash_alert.alert_reporter')->addError($this->get('translator')->trans('institution.flash.cant_delete'));
              return $this->redirect($this->generateUrl('manage_institution'));
            }

            $em->remove($entity);
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('institution.flash.delete') . $entity->getName());
        }

        return $this->redirect($this->generateUrl('manage_institution'));
    }

    /**
     * Creates a form to delete a Institution entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_institution_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('institution.actions.delete')))
            ->getForm();
    }

    /**
     * Quickly Delete an Institution entity.
     *
     */
    public function quickdeleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('InstitutionBundle:Institution')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find an Institution entity with id:.' . $id);
        }

        // get list of quotes related to this user first (cant delete a user if they are attached)
        // todo check for tours or other objects that might apply
        $quotes = $em->getRepository('QuoteBundle:Quote')->findOneBy(array('institution' => $entity->getId()));
        if($quotes){
            $this->get('ras_flash_alert.alert_reporter')->addError($this->get('translator')->trans('institution.flash.cant_delete'));
          return $this->redirect($this->generateUrl('manage_institution'));
        }

        $em->remove($entity);
        $em->flush();
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('institution.flash.delete') . $entity->getname());

        return $this->redirect($this->generateUrl('manage_institution'));
    }

    /**
     * hard Deletes Institution entity.
     *
     */
    public function harddeleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        // dont forget to disable softdelete filter so doctrine can *find* the deleted entity
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');

        $entity = $em->getRepository('InstitutionBundle:Institution')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find an Institution entity with id:.' . $id);
        }

        $quotes = $em->getRepository('QuoteBundle:Quote')->findOneBy(array('institution' => $entity->getId()));
        if($quotes){
            $this->get('ras_flash_alert.alert_reporter')->addError($this->get('translator')->trans('institution.flash.cant_delete'));
            return $this->redirect($this->generateUrl('manage_institution'));
        }

        $em->remove($entity);
        $em->flush();
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('quote.flash.delete'). ' ' . $entity->getName());

        return $this->redirect($this->generateUrl('manage_institution'));
    }

    /**
     * Restores a Deleted Institution entity.
     *
     */
    public function restoreAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        // dont forget to disable softdelete filter so doctrine can *find* the deleted entity
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');
        $entity = $em->getRepository('InstitutionBundle:Institution')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find an Institution entity with id:' . $id);
        }
        $entity->setDeleted(NULL);
        $em->persist($entity);
        $em->flush();
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('institution.flash.restore') . $entity->getname());

        return $this->redirect($this->generateUrl('manage_institution'));
    }


}
