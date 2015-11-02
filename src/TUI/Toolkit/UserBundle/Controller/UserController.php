<?php

namespace TUI\Toolkit\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Validator\Constraints\Null;
use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\UserBundle\Form\ResettingFormType;
use TUI\Toolkit\UserBundle\Form\UserType;
use TUI\Toolkit\UserBundle\Form\AjaxuserType;
use TUI\Toolkit\UserBundle\Form\UserMediaType;
use TUI\Toolkit\UserBundle\Form\ActivateUserType;
use TUI\Toolkit\UserBundle\Form\SecurityType;
use TUI\Toolkit\MediaBundle\Form\MediaType;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Action\RowAction;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

use Symfony\Component\EventDispatcher\EventDispatcher,
  Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
  Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
        $column->setTitle($this->get('translator')->trans('user.grid.filter.title.role'));
        $column->setFilterType('select');
        $column->setOperatorsVisible(false);

        // add email filter
        $column = $grid->getColumn('email');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('user.grid.filter.title.email'));
        //$column->setFilterType('select');
        $column->setOperatorsVisible(false);

        // add enabled filter
        $column = $grid->getColumn('enabled');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('user.grid.filter.title.enabled'));
        //$column->setFilterType('select');
        $column->setOperatorsVisible(false);

        // add last name filter
        $column = $grid->getColumn('lastName');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('user.grid.filter.title.lname'));
        $column->setOperatorsVisible(false);

        // Add action column
        $editAction = new RowAction('Edit', 'user_edit');
        $grid->addRowAction($editAction);
        $notifyAction = new RowAction('Notify', 'user_registration_confirmation');
        $notifyAction->manipulateRender(
            function ($action, $row) { // only show if enabled is false
                if ($row->getField('enabled') == true) {
                    return null;
                }
                return $action;
            }
        );
        $grid->addRowAction($notifyAction);
        $showAction = new RowAction('View', 'user_show');
        $grid->addRowAction($showAction);
        $resetAction = new RowAction('Reset', 'user_password_reset_grid');
        $resetAction->setRole('ROLE_ADMIN');
        $resetAction->setConfirm(true);
        $grid->addRowAction($resetAction);
        $deleteAction = new RowAction('Delete', 'user_quick_delete');
        $deleteAction->setRole('ROLE_ADMIN');
        $deleteAction->setConfirm(true);
        $deleteAction->manipulateRender(
            function ($action, $row) { // only show if canDeleteUser is true
                if ($this->canDeleteUser($row->getField('id')) == false) {
                    return null;
                }
                return $action;
            }
        );
        $grid->addRowAction($deleteAction);

        //Get locale for date time and other purposes
        $locale = $this->container->getParameter('locale');


        //manipulate the Columns
        $column = $grid->getColumn('lastLogin');
        $column->setTitle($this->get('translator')->trans('user.grid.column.title.last_login'));
        if (strpos($locale, "en_GB") !== false) {
            $column->setFormat('d-M-Y');
        }

        $column = $grid->getColumn('created');
        if (strpos($locale, "en_GB") !== false) {
            $column->setFormat('d-M-Y');
        }




        // Set the default order of the grid
        $grid->setDefaultOrder('id', 'ASC');


        // Set the selector of the number of items per page
        $grid->setLimits(array(10, 25, 50, 100));

        //set no data message
        $grid->setNoDataMessage($this->get('translator')->trans('user.grid.no_result'));

        // Export of the grid
        $grid->addExport(new CSVExport($this->get('translator')->trans('user.grid.export'), "currentUsers", array('delimiter' => ','), "UTF-8", "ROLE_BRAND"));

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

        //Get locale for date time and other purposes
        $locale = $this->container->getParameter('locale');

        //manipulate the Columns
        $column = $grid->getColumn('lastLogin');
        $column->setTitle($this->get('translator')->trans('user.grid.column.title.last_login'));
        if (strpos($locale, "en_GB") !== false) {
            $column->setFormat('d-M-Y');
        }

        $column = $grid->getColumn('created');
        if (strpos($locale, "en_GB") !== false) {
            $column->setFormat('d-M-Y');
        }


        // Set the default order of the grid
        $grid->setDefaultOrder('created', 'DESC');


        // Set the selector of the number of items per page
        $grid->setLimits(array(10, 25, 50, 100));

        //set no data message
        $grid->setNoDataMessage($this->get('translator')->trans('user.grid.no_results-deleted'));

        // Export of the grid
        $grid->addExport(new CSVExport($this->get('translator')->trans('user.grid.export_deleted'), "deletedUsers", array('delimiter' => ','), "UTF-8", "ROLE_ADMIN"));

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
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.save') . $entity->getUsername());

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
    public function ajax_organizer_createAction(Request $request)
    {
        $entity = new User();
        $form = $this->create_ajaxCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($em->getRepository('TUIToolkitUserBundle:User')->findByEmail($entity->getEmail())) {
                $existingUser = $em->getRepository('TUIToolkitUserBundle:User')->findByEmail($entity->getEmail());
                $existingUser = $existingUser[0];
                return new Response($existingUser);

            } else {
                $entity->setPassword('');
                $entity->setUsername($entity->getEmail());
                $em->persist($entity);
                $em->flush();

                return new Response($entity);
            }
        }

        return $this->render('TUIToolkitUserBundle:User:ajax_new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
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
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new UserType($locale), $entity, array(
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


        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('user.actions.create')));

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
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new AjaxuserType($locale), $entity, array(
            'action' => $this->generateUrl('user_ajax_create'),
            'method' => 'POST',
            'attr' => array(
                'id' => 'ajax_organizer_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('user.actions.create')));

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
        $form = $this->create_ajaxCreateForm($entity);

        return $this->render('TUIToolkitUserBundle:User:ajax_new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
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
        if ($this->canDeleteUser($id)) {
            $deleteForm = $this->createDeleteForm($id)->createView();
        } else {
            $deleteForm = Null;
        }

        return $this->render('TUIToolkitUserBundle:User:show.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm,
        ));
    }

    /**
     * Displays a form to set an newly created User password.
     *
     */
    public function editAction($id)
    {
        // set a session var for referrer to return user back to it
        $referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : null;
        $_SESSION['user_edit_return'] = $referer;

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        // only allow brand or higher to edit other users
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('ROLE_BRAND') or $user->getID() == $id) {

            $editForm = $this->createEditForm($entity);
            if ($this->canDeleteUser($id)) {
                $deleteForm = $this->createDeleteForm($id)->createView();
            } else {
                $deleteForm = Null;
            }

            return $this->render('TUIToolkitUserBundle:User:edit.html.twig', array(
                'entity' => $entity,
                'edit_form' => $editForm->createView(),
                'delete_form' => $deleteForm,
            ));
        } else {
            throw new AccessDeniedException($this->get('translator')->trans('user.exception.access'));
        }
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
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new UserType($locale), $entity, array(
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

        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $form->add('enabled', 'checkbox', array(
                'required' => FALSE,
            ))
                ->add('roles', 'choice', array(
                    'choices' => array(
                        'ROLE_USER' => 'User',
                        'ROLE_CUSTOMER' => 'CUSTOMER',
                        'ROLE_BRAND' => 'BRAND',
                        'ROLE_ADMIN' => 'ADMIN',
                        'ROLE_SUPER_ADMIN' => 'SUPER_ADMIN',
                    ),
                    'multiple' => TRUE,
                    'expanded' => TRUE,
                ));
        }

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('user.actions.update')));

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
        if (Null != $editForm->getData()->getMedia()) {
            $fileId = $editForm->getData()->getMedia();
            $entities = $em->getRepository('MediaBundle:Media')
                ->findById($fileId);

            if (NULL !== $entities) {
                $media = array_shift($entities);
                $editForm->getData()->setMedia($media);
            }
        }


        if ($editForm->isValid()) {
            $entity->setUsername($editForm->getData()->getEmail());
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.save') . $entity->getUsername());

            if (null !== $_SESSION['user_edit_return']) {
                return $this->redirect($_SESSION['user_edit_return']);
            } elseif (false === $this->get('security.context')->isGranted('ROLE_BRAND')) {
                return $this->redirect('/profile');
            } else {
                return $this->redirect($_SESSION['user_edit_return']);
            }
        }

        return $this->render('TUIToolkitUserBundle:User:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a page with a form to activate an existing User entity.
     *
     */
    public function activateUserAction($token)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->findByConfirmationToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('This is no longer a valid One Time Use login token.');
        }

        if(true===$entity[0]->isEnabled()){
            throw $this->createNotFoundException('This activation link is no longer valid because the account is already activated.');
        }
        $setForm = $this->createActivateUserForm($entity[0]);

        return $this->render('TUIToolkitUserBundle:Registration:activation.html.twig', array(
            'form' => $setForm->createView(),
            'user'  => $entity[0],
            'token' => $token,
        ));
    }


    /**
     * Creates a form to activate a User.
     * sets password and security question, plus terms of service check
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createActivateUserForm(User $entity)
    {
        $form = $this->createForm(new ActivateUserType(), $entity, array(
            'action' => $this->generateUrl('activate_user_submit', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('user.actions.activate')));

        return $form;
    }


    /**
     * Activate a User account by validating form submission.
     *
     */
    public function activateUserSubmitAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity rendering activation form.');
        }

        $setForm = $this->createActivateUserForm($user);
        $setForm->handleRequest($request);

        if ($setForm->isValid()) {
            $user->setPassword($setForm->getData()->getPlainPassword());
            $user->setQuestion($setForm->getData()->getQuestion());
            $user->setAnswer($setForm->getData()->getAnswer());
            $user->setEnabled(true);
            $user->setConfirmationToken(null);
            $em->persist($user);
            $em->flush();

          // trigger "registration complete" event here in case other place are listening for the event
          $dispatcher = $this->get('event_dispatcher');
            $url = $this->generateUrl('fos_user_registration_confirmed');
            $response = new RedirectResponse($url);
          $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

          $token = new UsernamePasswordToken($user, $user->getPassword(),
            "public", $user->getRoles());

          $this->get("security.context")->setToken($token);

          // Trigger login event
          $loginEvent = new InteractiveLoginEvent($request, $token);
          $this->get("event_dispatcher")
            ->dispatch("security.interactive_login", $loginEvent);

            return $this->redirect($this->generateUrl('fos_user_profile_show'));
        }

        return $this->render('TUIToolkitUserBundle:Registration:activation.html.twig', array(
            'user' => $user,
            'form' => $setForm->createView(),
        ));
    }


    /**
     * Displays a page with a form to reset an existing User password.
     *
     */
    public function resetPasswordAction($token)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->findByConfirmationToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('This Password Reset token is no longer valid.');
        }
        $setForm = $this->createResetPasswordForm($entity[0]);
        $question = $entity[0]->getQuestion();

        return $this->render('TUIToolkitUserBundle:Resetting:reset.html.twig', array(
            'form' => $setForm->createView(),
            'user'  => $entity[0],
            'token' => $token,
            'question' => $question,
        ));
    }

    /**
     * Creates a form to reset a User password.
     * sets password and security question, plus terms of service check
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createResetPasswordForm(User $entity)
    {
        $form = $this->createForm(new ResettingFormType(), $entity, array(
            'action' => $this->generateUrl('user_password_reset_submit', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('user.actions.password')));

        return $form;
    }


    /**
     * Reset a User password by validating form submission.
     *
     */
    public function resetPasswordSubmitAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity rendering activation form.');
        }

        $setForm = $this->createResetPasswordForm($user);
        $setForm->handleRequest($request);
        $token = $user->getConfirmationToken();
        $question = $user->getQuestion();

        if ($setForm->isValid()) {
            $answer = $setForm['answerConfirm']->getData();
            if(trim(strtolower($answer)) == trim(strtolower($user->getAnswer()))) {


                $user->setPassword($setForm->getData()->getPlainPassword());
                $user->setConfirmationToken(null);
                $em->persist($user);
                $em->flush();

                $token = new UsernamePasswordToken($user, $user->getPassword(),
                    "public", $user->getRoles());

                $this->get("security.context")->setToken($token);

                // Trigger login event
                $loginEvent = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")
                    ->dispatch("security.interactive_login", $loginEvent);

                $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.password') . ' ' . $user->getEmail());
                return $this->redirect($this->generateUrl('fos_user_profile_show'));
            } else {
                $setForm->addError(new FormError('Your security answer did not match our records. Please try again, or contact your application\'s contact.'));
            }
        }


        return $this->render('TUIToolkitUserBundle:Resetting:reset.html.twig', array(
            'user' => $user,
            'form' => $setForm->createView(),
            'token' => $token,
            'question' => $question,
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

            // get list of quotes related to this user first (cant delete a user if they are attached)
            //TODO add a check for Tours, and eventually other objects like payments or something.
            if ($this->canDeleteUser($entity->getId())) {
              $em->remove($entity);
              $em->flush();
              $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.delete') . $id);
            } else {
              $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.cant_delete'));
            }


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
          ->add('submit', 'submit', array('label' => $this->get('translator')->trans('user.actions.delete')))
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
        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.restore') . ' ' . $entity->getUsername());

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

      if ($this->canDeleteUser($entity->getId())) {
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.delete') . $entity->getUsername());
      } else {
          $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('user.flash.cant_delete'));
          return $this->redirect($this->generateUrl('user'));
        }

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

  /*
   * Sends an email to a User when brand user clicks Notify User
   */
  public function registerConfirmationTriggerAction($id)
    {

        $mailer = $this->container->get('mailer');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('TUIToolkitUserBundle:User')->find($id);
        // Create token
        $tokenGenerator = $this->container->get('fos_user.util.token_generator');

        //Get some user info
        $user->setConfirmationToken($tokenGenerator->generateToken());
        $userEmail = $user->getEmail();

        //Get Brand Stuff
        $brand = $em->getRepository('BrandBundle:Brand')->findAll();
        $brand = $brand[0];

        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('user.email.registration.subject'))
            ->setFrom($this->container->getParameter('user_system_email'))
            ->setTo($userEmail)
            ->setBody(
                $this->renderView(
                    'TUIToolkitUserBundle:Registration:register_email.html.twig',
                    array(
                        'brand' => $brand,
                        'user' => $user,
                    )
                ), 'text/html');

        $em->persist($user);
        $em->flush();

        $mailer->send($message);;

        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.registration_notification') . ' ' .$user->getEmail());

        return $this->redirect($this->generateUrl('user'));

    }

  /*
   * Sends an email after the Brand person clicks the User reset action
   */
  public function resetUserPasswordAction($id)
    {
        $mailer = $this->container->get('mailer');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('TUIToolkitUserBundle:User')->find($id);
        $username = $user->getUserName();
        $userEmail = $user->getEmail();

        /** @var $user UserInterface */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->render('TUIToolkitUserBundle:Resetting:request.html.twig', array(
                'invalid_username' => $username
            ));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->render('TUIToolkitUserBundle:Resetting:passwordAlreadyRequested.html.twig');
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        //Get Brand Stuff
        $brand = $em->getRepository('BrandBundle:Brand')->findAll();
        $brand = $brand[0];

        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('user.email.password_reset.subject'))
            ->setFrom($this->container->getParameter('user_system_email'))
            ->setTo($userEmail)
            ->setBody(
                $this->renderView(
                    'TUIToolkitUserBundle:Resetting:reset_email.html.twig',
                    array(
                        'brand' => $brand,
                        'user' => $user,
                    )
                ), 'text/html');

        $em->persist($user);
        $em->flush();

        $mailer->send($message);

        $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);

        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('user.flash.registration_notification') . ' ' . $user->getEmail());

        return $this->redirect($this->generateUrl('user'));

    }



    /**
     * Request reset user password: submit form and send email
     */
    public function sendEmailAction(Request $request)
    {
        $mailer = $this->container->get('mailer');

        $em = $this->getDoctrine()->getManager();

        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->render('TUIToolkitUserBundle:Resetting:request.html.twig', array(
                'invalid_username' => $username
            ));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->render('TUIToolkitUserBundle:Resetting:passwordAlreadyRequested.html.twig');
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        //Get Brand Stuff
        $brand = $em->getRepository('BrandBundle:Brand')->findAll();
        $brand = $brand[0];


        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('user.email.password_reset.subject'))
            ->setFrom($this->container->getParameter('user_system_email'))
            ->setTo($username)
            ->setBody(
                $this->renderView(
                    'TUIToolkitUserBundle:Resetting:reset_email.html.twig',
                    array(
                        'brand' => $brand,
                        'user' => $user,
                    )
                ), 'text/html');

        $em->persist($user);
        $em->flush();

        $mailer->send($message);

        $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);

//        return $this->redirect($this->generateUrl('/user/'));

        return new RedirectResponse($this->generateUrl('fos_user_resetting_check_email',
            array('email' => $this->getObfuscatedEmail($user))
        ));
    }


    /**
     * Get the truncated email displayed when requesting the resetting.
     *
     * The default implementation only keeps the part following @ in the address.
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     *
     * @return string
     */
    protected function getObfuscatedEmail($user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }


    /**
     * Show a page with a security info edit form
     */
    public function securityResetAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity to edit security info.');
        }
        $currentUser = $this->get('security.context')->getToken()->getUser();
        if ($id != $currentUser->getId()) {
            throw $this->createAccessDeniedException('You cannot edit another User\'s security information.');
        }
            $question = $entity->getQuestion();
            $securityForm = $this->createSecurityForm($entity);

            return $this->render('TUIToolkitUserBundle:User:editSecurity.html.twig', array(
                'entity' => $entity,
                'form' => $securityForm->createView(),
                'question' => $question,

            ));

    }

    /**
     * Creates a form to edit a User's security info.
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSecurityForm(User $entity)
    {
        $form = $this->createForm(new SecurityType(), $entity, array(
            'action' => $this->generateUrl('user_security_submit', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('user.actions.security')));

        return $form;
    }

    /**
     * Updates an existing User's security info.
     *
     */
    public function securitySubmitAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity when submitting security info changes.');
        }



        $question = $entity->getQuestion();
        $securityForm = $this->createSecurityForm($entity);
        $securityForm->handleRequest($request);

        if ($securityForm->isValid()) {
            $answer = $securityForm['originalAnswer']->getData();
            if(trim(strtolower($answer)) == trim(strtolower($entity->getAnswer()))) {
                // $entity->setUsername($securityForm->getData()->getEmail());
                $fields = array();
                $pw = $securityForm['plainPassword']->getData();
                $newQuestion = $securityForm['newQuestion']->getData();
                $newAnswer = $securityForm['newAnswer']->getData();

                if (!empty($pw)){
                    $entity->setPassword($pw);
                    $fields[] = 'Password';
                }
                if (!empty($newQuestion)){
                    $entity->setQuestion($newQuestion);
                    $fields[] = 'Security Question';
                }
                if (!empty($newAnswer)){
                    $entity->setAnswer($newAnswer);
                    $fields[] = 'Security Answer';
                }
                $em->persist($entity);
                $em->flush();

                if(count($fields) == 0) {
                    $msg = "No data was changed.";
                } else {
                    $msg = $this->get('translator')
                            ->trans('user.flash.save') . $entity->getUsername() .  ' for the fields: ' . implode(', ', $fields);
                }

                $this->get('session')
                    ->getFlashBag()
                    ->add('notice', $msg);


                return $this->redirect('/profile');
            } else {
                $securityForm->addError(new FormError('Your security answer did not match our records. Please try again, or contact your application\'s contact.'));
            }
        }

        return $this->render('TUIToolkitUserBundle:User:editSecurity.html.twig', array(
            'entity' => $entity,
            'form' => $securityForm->createView(),
            'question' => $question,
        ));
    }



    /**
     * getQuotes
     *
     * Display a list of Quotes on a User Profile page
     *
     * @param id
     * return twig
     */
    public function getQuotesAction($id)
    {
        $locale = $this->container->getParameter('locale');
        switch ($locale) {
            case 'en_GB.utf8':
                $format = 'd-m-Y';
                break;
            default:
                $format = 'm-d-Y';
                break;
        }
        // if user is brand or admin, list quotes where they are salesAgent or SecondaryContact
        // dont show expired or converted quotes for brand/admins either
        // if user is customer, list quotes by Permission Entity
        $quotes = array();
        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('ROLE_BRAND')) {
            $today = new \DateTime();
            $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
            $qb
                ->select('qv', 'q')
                ->from('QuoteBundle:QuoteVersion', 'qv')
                ->leftJoin('qv.quoteReference', 'q', 'WITH', 'q.id = qv.quoteReference')
                ->where('q.salesAgent = ?1')
                ->orWhere('q.secondaryContact = ?2')
                ->AndWhere('qv.converted = false')
                ->AndWhere('qv.expiryDate < ?3')
                ->AndWhere('q.converted = false')
                ->AndWhere('qv.isTemplate = false');
            $qb->setParameter(1, $id);
            $qb->setParameter(2, $id);
            $qb->setParameter(3, $today->format($format));
            $query = $qb->getQuery();
            $quotes = $query->getResult();
        } else {
            $em = $this->getDoctrine()->getManager();
            $permissions = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $id, 'class' => 'quote'));
            // this only returns pointers to quotes, so loop through and build quotes array
            foreach ($permissions as $permission) {
                $quotes[] = $em->getRepository('QuoteBundle:Quote')->find($permission->getObject());
            }
        }

        return $this->render('TUIToolkitUserBundle:User:myQuotes.html.twig', array(
            'quotes' => $quotes,
            'locale' => $locale,
        ));
    }

    /**
     * getTours
     *
     * Display a list of Tours on a User Profile page
     *
     * @param id
     * return twig
     */
    public function getToursAction($id)
    {
        $locale = $this->container->getParameter('locale');
        // if user is brand or admin, list quotes where they are salesAgent or SecondaryContact
        // if user is customer, list quotes by Permission Entity
        $tours = array();
        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('ROLE_BRAND')) {
            $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
            $qb
                ->select('t')
                ->from('TourBundle:Tour', 't')
                ->where('t.salesAgent = ?1')
                ->orWhere('t.secondaryContact = ?2');
            $qb->setParameter(1, $id);
            $qb->setParameter(2, $id);
            $query = $qb->getQuery();
            $tours = $query->getResult();


        } else {
            $em = $this->getDoctrine()->getManager();
            $permissions = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $id, 'class' => 'tour'));
            // this only returns pointers to tours, so loop through and build tours array
            foreach ($permissions as $permission) {
                $tours[] = $em->getRepository('TourBundle:Tour')->find($permission->getObject());
            }
        }


        return $this->render('TUIToolkitUserBundle:User:myTours.html.twig', array(
            'tours' => $tours,
            'locale' => $locale,
        ));
    }

    public function canDeleteUser($id)
    {

        $em = $this->getDoctrine()->getManager();

        $permissions = $em->getRepository('PermissionBundle:Permission')->findOneBy(array('user' => $id));
        $primary_organizerq = $em->getRepository('QuoteBundle:Quote')->findOneBy(array('organizer' => $id));
        $primary_adminq = $em->getRepository('QuoteBundle:Quote')->findOneBy(array('salesAgent' => $id));
        $secondary_adminq = $em->getRepository('QuoteBundle:Quote')->findOneBy(array('secondaryContact' => $id));
        $primary_organizert = $em->getRepository('TourBundle:Tour')->findOneBy(array('organizer' => $id));
        $primary_admint = $em->getRepository('TourBundle:Tour')->findOneBy(array('salesAgent' => $id));
        $secondary_admint = $em->getRepository('TourBundle:Tour')->findOneBy(array('secondaryContact' => $id));

        if ($primary_organizerq || $primary_adminq || $secondary_adminq || $primary_organizert || $primary_admint || $secondary_admint || $permissions) {
            //$this->get('session')->getFlashBag()->add('error', 'Unable to delete the User because they are associated with Quotes or Tours');
            //return $this->redirect($this->generateUrl('user'));
            return false;
        } else {
            return true;
        }
    }

    public function getWelcomeMessageAction($token)
    {
      $em = $this->getDoctrine()->getManager();
      $user = $em->getRepository('TUIToolkitUserBundle:User')->findBy(array('confirmationToken' => $token));

      if (!$user) {
        throw $this->createNotFoundException('Unable to find User entity for activation welcome message display.');
      }

      if (in_array('ROLE_BRAND', $user[0]->getRoles())) {
        // use Brand msg
        $msg = $this->get('translator')->trans('user.activate.brand');
      } else {
        // see what object type the user is associated with in permission table
        $permission = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $user[0]));

          if(empty($permission)) {
              $role = 'participant';
          } else {
             $role = $permission[0]->getGrants();
          }
        if ($role =='organizer'){
          $msg = $this->get('translator')->trans('user.activate.organizer');
        }
        if ($role == 'participant' ){
          $msg = $this->get('translator')->trans('user.activate.participant');
        }
      }
      return $this->render('TUIToolkitUserBundle:Resetting:welcomeMessage.html.twig', array(
        'message' => $msg,
      ));

    }

}
