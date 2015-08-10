<?php

namespace TUI\Toolkit\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Validator\Constraints\Null;
use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\UserBundle\Form\UserType;
use TUI\Toolkit\UserBundle\Form\AjaxuserType;
use TUI\Toolkit\UserBundle\Form\UserMediaType;
use TUI\Toolkit\MediaBundle\Form\MediaType;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * User controller.
 *
 *
 */
class UserController extends Controller
{

    /**
     * Lists all unconverted Quote entities.
     *
     */
    public function indexAction(Request $request)
    {
        // list hidden columns
        $hidden = array();

        // Creates simple grid based on your entity (ORM)
        $source = new Entity('TUIToolkitUserBundle:User');

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        // Attach the source to the grid
        $grid->setSource($source);
        $grid->setId('usergrid');
        $grid->hideColumns($hidden);


        // add roles filter
        $column = $grid->getColumn('roles');
        $column->setFilterable(true);
        $column->setTitle('Role');
        $column->setFilterType('select');
        $column->setOperatorsVisible(false);

        // Add action column
        $editAction = new RowAction('Edit', 'user_edit');
        $grid->addRowAction($editAction);
        $showAction = new RowAction('View', 'user_show');
        $grid->addRowAction($showAction);
        $deleteAction = new RowAction('Delete', 'user_quick_delete');
        $deleteAction->setRole('ROLE_ADMIN');
        $deleteAction->setConfirm(true);
        $grid->addRowAction($deleteAction);

        //manipulate the Columns
        $column = $grid->getColumn('lastLogin');
        $column->setTitle('Last Login');

        // Set the default order of the grid
        $grid->setDefaultOrder('created', 'DESC');


        // Set the selector of the number of items per page
        $grid->setLimits(array(10, 25, 50, 100));

        //set no data message
        $grid->setNoDataMessage("There are no Users to show. Please check your filter settings and try again.");

        // Export of the grid
        $grid->addExport(new CSVExport("Users as CSV", "currentUsers", array('delimiter' => ','), "UTF-8", "ROLE_BRAND"));

        // Manage the grid redirection, exports and the response of the controller
        return $grid->getGridResponse('TUIToolkitUserBundle:User:index.html.twig');

    }


    /**
     * Lists all deleted User entities.
     *
     */
    public function deletedAction(Request $request)
    {
        // list hidden columns
        $hidden = array();
        $em = $this->getDoctrine()->getManager();
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');

        // Creates simple grid based on your entity (ORM)
        $source = new Entity('TUIToolkitUserBundle:User');

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
        $grid->setId('usergrid');
        $grid->hideColumns($hidden);

        // Add action column
        $restoreAction = new RowAction('Restore', 'user_restore');
        $grid->addRowAction($restoreAction);


        //manipulate the Columns
        $column = $grid->getColumn('lastLogin');
        $column->setTitle('Last Login');

        // Set the default order of the grid
        $grid->setDefaultOrder('created', 'DESC');


        // Set the selector of the number of items per page
        $grid->setLimits(array(10, 25, 50, 100));

        //set no data message
        $grid->setNoDataMessage("There are no Deleted Users to show. Please check your filter settings and try again.");

        // Export of the grid
        $grid->addExport(new CSVExport("Deleted Users as CSV", "deletedUsers", array('delimiter' => ','), "UTF-8", "ROLE_ADMIN"));

        // Manage the grid redirection, exports and the response of the controller
        return $grid->getGridResponse('TUIToolkitUserBundle:User:deleted.html.twig');

    }

    /**
     * Creates a new User entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
          $entity->setUsername($entity->getEmail());
          $entity->setPassword('');
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'User Saved: ' . $entity->getUsername());

            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
        }

        return $this->render('TUIToolkitUserBundle:User:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

  /**
   * Creates a new User entity.
   *
   */
  public function ajax_organizer_create(Request $request)
  {
    $entity = new User();
    $form = $this->create_ajaxCreateForm($entity);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $entity->setPassword('');
      $entity->setUsername($entity->getEmail());
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();

      return new Response($entity);
    }

    return $this->render('TUIToolkitUserBundle:User:ajax_new.html.twig', array(
      'entity' => $entity,
      'form'   => $form->createView(),
    ));
  }




  /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
        ));

        // get current user's roles and add form elements

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form->add('enabled', 'checkbox', array(
                'required' => false,
            ))
                ->add('roles', 'choice', array(
                    'choices' => array('ROLE_USER' => 'User', 'ROLE_CUSTOMER' => 'CUSTOMER', 'ROLE_BRAND' => 'BRAND', 'ROLE_ADMIN' => 'ADMIN',),
                    'multiple' => true,
                    'expanded' => TRUE,
                ));
        }

        if ($this->get('security.context')->isGranted('ROLE_BRAND')) {
            //what does Brand add?
        }


        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }


  /**
   * Creates a form to create a User entity.
   *
   * @param User $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function create_ajaxCreateForm(User $entity)
  {
    $form = $this->createForm(new AjaxuserType(), $entity, array(
      'action' => $this->generateUrl('user_ajax_create'),
      'method' => 'POST',
      'attr'  => array (
        'id' => 'ajax_organizer_form'
      ),
    ));


    $form->add('submit', 'submit', array('label' => 'Create'));

    return $form;
  }



  /**
     * Displays a form to create a new User entity.
     *
     */
    public function newAction()
    {
        $entity = new User();
        $form = $this->createCreateForm($entity);

        return $this->render('TUIToolkitUserBundle:User:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

  /**
   * Displays a form to create a new User entity.
   *
   */
  public function new_ajaxAction()
  {
    $entity = new User();
    $form   = $this->create_ajaxCreateForm($entity);

    return $this->render('TUIToolkitUserBundle:User:ajax_new.html.twig', array(
      'entity' => $entity,
      'form'   => $form->createView(),
    ));
  }


  /**
     * Finds and displays a User entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TUIToolkitUserBundle:User:show.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TUIToolkitUserBundle:User:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        // get current user's roles and add form elements

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form->add('enabled', 'checkbox', array(
                'required' => FALSE,
            ))
                ->add('roles', 'choice', array(
                    'choices' => array(
                        'ROLE_USER' => 'User',
                        'ROLE_CUSTOMER' => 'CUSTOMER',
                        'ROLE_BRAND' => 'BRAND',
                        'ROLE_ADMIN' => 'ADMIN',
                    ),
                    'multiple' => TRUE,
                    'expanded' => TRUE,
                ));
        }

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $media_data = null;
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if (Null != $editForm->getData()->getMedia()){
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
            $this->get('session')->getFlashBag()->add('notice', 'User Saved: ' . $entity->getUsername());

            return $this->redirect($this->generateUrl('user'));
        }

        return $this->render('TUIToolkitUserBundle:User:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a User entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'User Deleted: ' . $id);
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }

    /**
     * Restores a Deleted Quote entity.
     *
     */
    public function restoreAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        // dont forget to disable softdelete filter so doctrine can *find* the deleted entity
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');
        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity with id:.' . $id);
        }
        $entity->setDeleted(NULL);
        $em->persist($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice', 'User Restored: ' . $entity->getUsername());

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Quickly Delete a User entity.
     *
     */
    public function quickdeleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity with id:.' . $id);
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice', 'User Deleted: ' . $entity->getUsername());

        return $this->redirect($this->generateUrl('user'));
    }


    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}/upload", name="user_upload")
     * @Method("GET")
     * @Template("TUIToolkitUserBundle:User:dropzone.html.twig")
     */
    public function createDropZoneFormAction(Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        return $this->render('TUIToolkitUserBundle:User:dropzone.html.twig', array(
            'form' => $form->createView()
        ));
    }

  public function registerConfirmationTriggerAction($id) {

    $mailer = $this->container->get('fos_user.mailer');

    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository('TUIToolkitUserBundle:User')->find($id);
    // Create token
    $tokenGenerator = $this->container->get('fos_user.util.token_generator');
    $user->setConfirmationToken($tokenGenerator->generateToken());
    $em->persist($user);
    $em->flush();
    $mailer->sendConfirmationEmailMessage($user);

    $this->get('session')->getFlashBag()->add('notice', 'A Notification was sent to ' . $user->getEmail());

    return $this->redirect($this->generateUrl('user_show', array('id' => $user->getId())));
  }

}
