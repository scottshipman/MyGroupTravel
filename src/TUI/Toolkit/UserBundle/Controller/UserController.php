<?php

namespace TUI\Toolkit\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Validator\Constraints\Null;

use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\PermissionBundle\Entity\Permission;
use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\UserBundle\Form\ResettingFormType;
use TUI\Toolkit\UserBundle\Form\UserType;
use TUI\Toolkit\UserBundle\Form\AjaxuserType;
use TUI\Toolkit\UserBundle\Form\UserMediaType;
use TUI\Toolkit\UserBundle\Form\ActivateUserType;
use TUI\Toolkit\UserBundle\Form\SecurityType;
use TUI\Toolkit\MediaBundle\Form\MediaType;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Export\ExcelExport;
use APY\DataGridBundle\Grid\Action\RowAction;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


use Symfony\Component\EventDispatcher\EventDispatcher,
  Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
  Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use TUI\Toolkit\UserBundle\TUIToolkitUserBundle;

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
        $hidden = array('roles');

        // Creates simple grid based on your entity (ORM)
        $source = new Entity('TUIToolkitUserBundle:User');

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        // Attach the source to the grid
        $grid->setSource($source);
        $grid->setId('usergrid');
        // set grid filter persistance so filters are remebered for whole session
        $grid->setPersistence(true);
        $grid->hideColumns($hidden);


        // add roles filter
//        $column = $grid->getColumn('roles');
//        $column->setFilterable(true);
//        $column->setTitle($this->get('translator')->trans('user.grid.filter.title.role'));
//        $column->setFilterType('select');
//        $column->setOperatorsVisible(false);
//        $column->setExport('false');


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
        $editAction->manipulateRender(
            function ($action, $row) { // only show if canEditUser is true
                if ($this->canEditUser($row->getField('id')) == false) {
                    return null;
                }
                return $action;
            }
        );
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
        $grid->addExport(new CSVExport($this->get('translator')->trans('user.grid.export'), "currentUsers", array('delimiter' => ','), "UTF-8", "ROLE_ADMIN"));

        //Testing PHPEXcell2003 export
        //$grid->addExport(new ExcelExport($this->get('translator')->trans('user.grid.export'), "currentUsers", array('delimiter' => ','), "UTF-8", "ROLE_ADMIN"));

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
        $grid->setId('usergriddeleted');
        // set grid filter persistance so filters are remebered for whole session
        $grid->setPersistence(true);
        $grid->hideColumns($hidden);

        // Add action column
        $restoreAction = new RowAction('Restore', 'user_restore');
        $grid->addRowAction($restoreAction);

        //Add hard delete action
        $deleteAction = new RowAction('Delete', 'user_hard_delete');
        $deleteAction->setRole('ROLE_BRAND');
        $deleteAction->setConfirm(true);
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

        //Check softdeleted users to make sure there are no duplicates
        $formEmail = $form->getData()->getEmail();
        $em = $this->getDoctrine()->getManager();
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');
        $existingUser = $em->getRepository('TUIToolkitUserBundle:User')->findOneByEmail($formEmail);
        $filters->enable('softdeleteable');

        if ($existingUser != null) {
            $form['email']->addError(new FormError($this->get('translator')->trans('user.form.error.deleted')));
        }

        if ($form->isValid()) {
            $entity->setUsername($entity->getEmail());
            $entity->setPassword('');
            $entity->setRolesString(implode(', ', $entity->getRoles()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.save') . $entity->getUsername());

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

        //Check softdeleted users to make sure there are no duplicates
        $formEmail = $form->getData()->getEmail();
        $em = $this->getDoctrine()->getManager();
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');
        $existingUser = $em->getRepository('TUIToolkitUserBundle:User')->findOneByEmail($formEmail);
        $filters->enable('softdeleteable');

        if ($existingUser != null) {
            if ($existingUser->getDeleted() != null) {
                $form->addError(new FormError($this->get('translator')->trans('user.form.error.deleted')));

            } else {
                return new Response($existingUser);
            }
        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
                $entity->setPassword('');
                $entity->setUsername($entity->getEmail());
                $entity->setRoles(array('ROLE_CUSTOMER'));
                $entity->setRolesString('ROLE_CUSTOMER');
                $em->persist($entity);
                $em->flush();

                return new Response($entity);

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
            'user' => $this->getUser(),
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
        ));

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

        if ($this->canEditUser($id)) {
            $canEdit = TRUE;
        } else {
            $canEdit = FALSE;
        }

        return $this->render('TUIToolkitUserBundle:User:show.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm,
            'can_edit'  => $canEdit,
        ));
    }

    /**
     * Displays a form to set an newly created User password.
     *
     */
    public function editAction($id)
    {
        if ($this->canEditUser($id) == false) {
            throw new AccessDeniedException('You are not allowed to edit this user.');
        }

        // set a session var for referrer to return user back to it
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
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
            'user' => $this->getUser(),
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

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

        //Check softdeleted users to make sure there are no duplicates
        $formEmail = $editForm->getData()->getEmail();
        if ( $formEmail != $entity->getEmail() ) {
            $filters = $em->getFilters();
            $filters->disable('softdeleteable');
            $existingUser = $em->getRepository('TUIToolkitUserBundle:User')->findOneByEmail($formEmail);
            $filters->enable('softdeleteable');

            if ($existingUser != null) {
                $editForm['email']->addError(new FormError($this->get('translator')->trans('user.form.error.deleted')));
            }
        }

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
            $roles = $entity->getRoles();
            $entity->setRolesString(implode(', ', $roles));
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.save') . $entity->getUsername());

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
            throw new HttpException(400, 'This link has expired or does not exist. Please request another from your administrator.');
        }

        if(true===$entity[0]->isEnabled()){
            throw new HttpException(400, 'This activation link is no longer valid because the account is already activated.');
        }
        $setForm = $this->createActivateUserForm($entity[0]);

        // Check if the User is an Organizer or Assistant to do funcky stuff
        $tourOrganizer = $this->get("permission.set_permission")->getObject('organizer', $entity[0], 'tour');
        $tourAssistant = $this->get("permission.set_permission")->getObject('assistant', $entity[0], 'tour');

        return $this->render('TUIToolkitUserBundle:Registration:activation.html.twig', array(
            'form' => $setForm->createView(),
            'user'  => $entity[0],
            'token' => $token,
            'isOrganizer' => is_array($tourOrganizer) ? array_shift($tourOrganizer): $tourOrganizer,
            'isAssistant' => is_array($tourAssistant) ? array_shift($tourAssistant) : $tourAssistant,
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

        //Encoder factory for hashing security questions
        $factory = $this->get('security.encoder_factory');
        $user_manager = $this->get('fos_user.user_manager');
        $userObject = $user_manager->loadUserByUsername($user->getUsername());
        $encoder = $factory->getEncoder($userObject);

        if ($setForm->isValid()) {

            //Do some manipulation for encoding the security answer
            $answer = $setForm->getData()->getAnswer();
            $answer = trim((strtolower($answer)));
            $answerHash = $encoder->encodePassword($answer, $user->getSalt());

            $user->setPassword($setForm->getData()->getPlainPassword());
            $user->setQuestion($setForm->getData()->getQuestion());
            $user->setAnswer($answerHash);
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

          // check if new user is an assistant or organizer, and allow them to edit their passenger record
            if ($role = $setForm['role']->getData() ) {
                // if an assistant, create a passenger record
                $tour = $em->getRepository('TourBundle:Tour')->find($setForm['tour']->getData());
                $newPassenger = new Passenger();
                $newPassenger->setStatus("waitlist");
                $newPassenger->setFree(false);
                $newPassenger->setFName($user->getFirstName());
                $newPassenger->setLName($user->getLastName());
                $newPassenger->setTourReference($tour);
                $newPassenger->setGender('undefined');
                $newPassenger->setDateOfBirth(new \DateTime("1987-01-01"));
                $newPassenger->setSignUpDate(new \DateTime("now"));
                $newPassenger->setSelf(TRUE);


                $em->persist($newPassenger);
                $em->flush($newPassenger);

                $newPermission = new Permission();
                $newPermission->setUser($user);
                $newPermission->setClass('passenger');
                $newPermission->setGrants('parent');
                $newPermission->setObject($newPassenger->getId());


                $em->persist($newPermission);
                $em->flush($newPermission);
                // if an organizer, a passenger record exists so just add a message
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                $tourUrl = $baseurl . "/tour/dashboard/" . $setForm['tour']->getData()  . "/passengers";
                $tourLink = " <br><a style='color:white;' href='$tourUrl'>$tourUrl</a>";
                $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.register-organizer') . $tourLink);
            }

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

        //Encoder factory for hashing security questions
        $factory = $this->get('security.encoder_factory');
        $user_manager = $this->get('fos_user.user_manager');
        $userObject = $user_manager->loadUserByUsername($user->getUsername());
        $encoder = $factory->getEncoder($userObject);

        if ($setForm->isValid()) {
            $answer = $setForm['answerConfirm']->getData();
            $answer = trim((strtolower($answer)));
            $answerHash = $encoder->encodePassword($answer, $user->getSalt());
            $old_answer = $user->getAnswer();
            if($answerHash == $old_answer) {

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

                $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.password') . ' ' . $user->getEmail());
                return $this->redirect($this->generateUrl('fos_user_profile_show'));
            } else if(trim(strtolower($answer)) == trim(strtolower($user->getAnswer()))) {

                //Set the security question answer to a hashed value if it isn't already
                $user->setAnswer($answerHash);
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

                $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.password') . ' ' . $user->getEmail());
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
              $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.delete') . $id);
            } else {
              $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.cant_delete'));
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
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.restore') . ' ' . $entity->getUsername());

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
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.delete') . $entity->getUsername());
      } else {
          $this->get('ras_flash_alert.alert_reporter')->addError($this->get('translator')->trans('user.flash.cant_delete'));
          return $this->redirect($this->generateUrl('user'));
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * hard Deletes User entity.
     *
     */
    public function harddeleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        // dont forget to disable softdelete filter so doctrine can *find* the deleted entity
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity in order to delete it using ajax.');
        }
        if ($this->canDeleteUser($entity->getId())) {
            $em->remove($entity);
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.delete') . $entity->getUsername());
        } else {
            $this->get('ras_flash_alert.alert_reporter')->addError($this->get('translator')->trans('user.flash.cant_delete'));
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

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

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

        $mailer->send($message);

        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.registration_notification') . ' ' .$user->getEmail());

//        return $this->redirect($this->generateUrl('user'));
      return $this->redirect($_SERVER['HTTP_REFERER']);

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

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('user.email.password_reset.subject'))
            ->setFrom(array($this->container->getParameter('user_system_email') => $this->container->getParameter('user_system_name')))
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

        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.registration_notification') . ' ' . $user->getEmail());

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

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('user.email.password_reset.subject'))
            ->setFrom(array($this->container->getParameter('user_system_email') => $this->container->getParameter('user_system_name')))
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

        //Encoder factory for hashing security questions
        $factory = $this->get('security.encoder_factory');
        $user_manager = $this->get('fos_user.user_manager');
        $user = $user_manager->loadUserByUsername($entity->getUsername());
        $encoder = $factory->getEncoder($user);

        if ($securityForm->isValid()) {
            $answer = $securityForm['originalAnswer']->getData();
            $answer = trim((strtolower($answer)));
            $answerHash = $encoder->encodePassword($answer, $user->getSalt());
            $old_answer = $entity->getAnswer();
            if($answerHash == $old_answer) {
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
                    $newAnswerHash = $encoder->encodePassword($newAnswer, $user->getSalt());
//                    $hash = $this->$factory->getEncoder($user)->encodePassword($newAnswer, null);
                    $entity->setAnswer($newAnswerHash);
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
            } else if(trim(strtolower($answer)) == trim(strtolower($entity->getAnswer()))) {
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
        $em = $this->getDoctrine()->getManager();
        // if user is brand or admin, list quotes where they are salesAgent or SecondaryContact
        // dont show expired or converted quotes for brand/admins either
        // if user is customer, list quotes by Permission Entity
        $quotes = array();
        $securityContext = $this->get('security.context');

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        if ($securityContext->isGranted('ROLE_BRAND')) {
            $today = new \DateTime();
            $limit = $this->container->hasParameter('profile_query_limit') ? $this->container->getParameter('profile_query_limit') : 5;
            $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
            $qb
                ->select('qv', 'q')
                ->from('QuoteBundle:QuoteVersion', 'qv')
                ->leftJoin('qv.quoteReference', 'q', 'WITH', 'q.id = qv.quoteReference')
                ->where('q.salesAgent = ?1 OR q.secondaryContact = ?2')
                ->AndWhere('qv.converted = false')
                ->AndWhere('qv.expiryDate > ?3 OR qv.expiryDate is null')
                ->AndWhere('q.converted = false')
                ->AndWhere('qv.isTemplate = false');
            $qb->setParameter(1, $id);
            $qb->setParameter(2, $id);
            $qb->setParameter(3, $today);
            $qb->orderBy('qv.created', 'DESC');
            $qb->setMaxResults( $limit);
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
        $em = $this->getDoctrine()->getManager();
        // if user is brand or admin, list quotes where they are salesAgent or SecondaryContact
        // if user is customer, list quotes by Permission Entity
        $tours = array();
        $data = array();
        $securityContext = $this->get('security.context');

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        if ($securityContext->isGranted('ROLE_BRAND')) {
            $limit = $this->container->hasParameter('profile_query_limit') ? $this->container->getParameter('profile_query_limit') : 5;
            $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
            $qb
                ->select('t')
                ->from('TourBundle:Tour', 't')
                ->where('t.salesAgent = ?1')
                ->orWhere('t.secondaryContact = ?2');
            $qb->setParameter(1, $id);
            $qb->setParameter(2, $id);
            $qb->orderBy('t.created', 'DESC');
            $qb->setMaxResults( $limit);
            $query = $qb->getQuery();
            $tours = $query->getResult();


        } else {
            $em = $this->getDoctrine()->getManager();
            $permissions = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $id, 'class' => 'tour'));
            // this only returns pointers to tours, so loop through and build tours array
            foreach ($permissions as $permission) {
                if ($object = $em->getRepository('TourBundle:Tour')->find($permission->getObject())) {
                    if ($permission->getGrants() != "parent") {
                        $tours[] = $object;
                    }

                }
            }
        }

        $data['tours'] = $tours;
        $data['locale'] = $locale;
        $data['brand'] = $brand;

        return $this->render('TUIToolkitUserBundle:User:myTours.html.twig', $data);
    }

    /**
     * @param $id tour id
     * @return Response
     */
    public function getToursWithPassengersAction($id){
        $locale = $this->container->getParameter('locale');
        // if user is brand or admin, list quotes where they are salesAgent or SecondaryContact
        // if user is customer, list quotes by Permission Entity
        $parents = array();
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $passengers = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $id, 'class' => 'passenger', 'grants' => 'parent'));
        foreach ($passengers as $passenger) {

            if ($object = $em->getRepository('PassengerBundle:Passenger')->find($passenger->getObject())) {
                if($object->getSelf() == FALSE ) {
                    // dont add passenger objects where they are an organizer or assistant (flagged as self)


                    $completedTasks = array();
                    $tour = $em->getRepository('TourBundle:Tour')
                        ->find($object->getTourReference()->getId());


                    //Find the possible Passenger Tasks for the tour
                    $possibleTasksCount = $this->get("passenger.actions")
                        ->getPossibleTourTasks($tour->getId());

                    //Only add to array if unique
                    if (!in_array($tour, $parents)) {
                        $parents[] = $tour;
                    }

                    //Then find the tasks that the passengers have completed Id= Pa
                    $completedTasksCount = $this->get("passenger.actions")
                        ->getPassengerCompletedTasks($object->getId());

                    //Grab counts of completed tasks for each passenger
                    $object->completedTasksCount = $completedTasksCount;

                    //Grab count of possible tasks
                    $object->possibleTasksCount = $possibleTasksCount;

                    $data['passengerObjects'][] = $object;
                }
            }
        }

        foreach ($parents as $parent) {
            $passengers = array();
            $possible = '';
            $completed = '';
            $completedCount = 0;
            $possibleCount = 0;
            foreach ($data['passengerObjects'] as $passengerObject) {
                if ($passengerObject->getTourReference()->getId() == $parent->getId()) {
                    $passengers[] = $passengerObject;
                    $completed = $passengerObject->completedTasksCount;
                    $completed = count($completed);
                    $completedCount += $completed;
                    $possible = $passengerObject->possibleTasksCount;
                    $possible = count($possible);
                    $possibleCount += $possible;
                }
            }
            $parent->passengers = $passengers;
            $parent->possible = $possibleCount;
            $parent->completed = $completedCount;
        }
        $data['parents'] = $parents;
        $data['locale'] = $locale;

        return $this->render('TUIToolkitUserBundle:User:myToursWithPassengers.html.twig', $data);
    }

    public function getTourPassengersAction($tourId, $parentId){
        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $tour_permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            $permission_pass = FALSE;

            if ($user->getId() == $parentId) {
                $permission_pass = TRUE;
            }

            if ($tour_permission != NULL && (in_array('organizer', $tour_permission) || in_array('assistant', $tour_permission))) {
                $permission_pass = TRUE;
            }

            if (!$permission_pass) {
                throw $this->createAccessDeniedException();
            }
        }

        $locale = $this->container->getParameter('locale');
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $user = $em->getRepository('TUIToolkitUserBundle:User')->find($parentId);

        //Get possible tasks on the tour
        $possibleTasksCount = $this->get("passenger.actions")->getPossibleTourTasks($tour->getId());



        $passengers = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $parentId, 'class' => 'passenger'));

        $passengerObjects = array();
        $creditTotal = 0; $overdueTotal = 0; $priceTotal = 0;
        foreach ($passengers as $passenger) {
            $paxPrice = 0; $paxCredit = 0; $paxOverdue = 0;
            $object = $em->getRepository('PassengerBundle:Passenger')->find($passenger->getObject());
            if (!empty($object) && $object->getTourReference()->getId() == $tourId) {
                $cashBalance = $this->get('payment.GetPayments')->getPassengersPaymentsPaid($object->getId());
                // add payment details to object also
                $payments = $this->get('payment.getPayments')->getPassengersPaymentTasks($object->getId());
                // payment structure is array('credit' => $credit, 'item' =>$tourPaymentTask, 'status' => $status, 'overdueAmt' => $overdueAmt, 'due' => $taskAmt)
                foreach($payments as $payment){
                    $paxPrice += $payment['due'];
                    $paxOverdue += $payment['overdueAmt'];
                    $paxCredit += $payment['credit'];
                }

                $passengerObjects[] = array(
                    'passenger' => $object,
                    'payment' =>$payments,
                    'completedTasksCount' => 0,
                    'paxPrice' => $paxPrice,
                    'paxOverdue' => $paxOverdue,
                    'paxCredit' => $paxCredit,
                );

                $creditTotal+= $paxCredit;
                $priceTotal += $paxPrice;
                $overdueTotal += $paxOverdue;
                if($cashBalance['total'] > 0 && $creditTotal == 0) {
                    // this passenger made payments but no longer owes because of status change
                    $creditTotal = $cashBalance['total'];
                }
            }
        }



        $priceData = array('creditTotal' => $creditTotal, 'priceTotal' => $priceTotal, 'overdueTotal' => $overdueTotal);

        $totalCompletedTasks = 0;


        foreach ($passengerObjects as $key => $passengerObject){

            //Get completed tasks on the tour for each passenger
            $completedTasksCount = $this->get("passenger.actions")->getPassengerCompletedTasks($passengerObject['passenger']->getId());
            $completedTasksCount = count($completedTasksCount);
            $passengerObjects[$key]['completedTasksCount'] = $completedTasksCount;
            $totalCompletedTasks += $completedTasksCount;

        }
        $possibleTasksCount = count($possibleTasksCount);

        //Get total possible tasks
        $totalPossibleTasks = $possibleTasksCount * count($passengerObjects);


        //Get all brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('TUIToolkitUserBundle:User:myPassengers.html.twig', array(
            'entity' => $tour,
            'tour' => $tour,
            'user' => $user,
            'locale' => $locale,
            'brand' => $brand,
            'passengers' => $passengers,
            'passengerObjects' => $passengerObjects,
            'totalCompletedTasks' => $totalCompletedTasks,
            'totalPossibleTasks' => $totalPossibleTasks,
            'possibleTasksCount' => $possibleTasksCount,
            'priceData' => $priceData,
        ));
    }

    /**
     * Test to see if current user has permission to delete another user
     *
     * @param $id
     * @return bool
     */
    public function canDeleteUser($id)
    {

        $em = $this->getDoctrine()->getManager();
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {

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
    }

    /**
     * Test to see if current user has permission to edit another user
     *
     * @param $id
     * @return bool
     */
    public function canEditUser($id)
    {
        $em = $this->getDoctrine()->getManager();
        $targetUser = $em->getRepository('TUIToolkitUserBundle:User')->find($id);
        $roles = $targetUser->getRoles();
        $curUser= $this->get('security.context')->getToken()->getUser();

        if ($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            return TRUE;
        }

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if(in_array('ROLE_SUPER_ADMIN', $roles)) {
                return FALSE;
            } else {
                return TRUE;
            }
        }

        if ($this->get('security.context')->isGranted('ROLE_BRAND')) {
            if(count(array_intersect($roles, array('ROLE_ADMIN', 'ROLE_BRAND', 'ROLE_SUPER_ADMIN'))) > 0) {
                if ($id != $curUser->getId()){
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {
                return TRUE;
            }
        }

        if ($id != $curUser->getId()){
            return FALSE;
        } else {
            return TRUE;
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
              $role = 'parent';
          } else {
             $role = $permission[0]->getGrants();
          }
        if ($role =='organizer'){
          $msg = $this->get('translator')->trans('user.activate.organizer');
        }
          if ($role =='assistant'){
              $msg = $this->get('translator')->trans('user.activate.assistant');
          }

        if ($role == 'parent' ){
          $msg = $this->get('translator')->trans('user.activate.participant');
        }
      }

      return $this->render('TUIToolkitUserBundle:Resetting:welcomeMessage.html.twig', array(
        'message' => $msg,
      ));

    }

}
