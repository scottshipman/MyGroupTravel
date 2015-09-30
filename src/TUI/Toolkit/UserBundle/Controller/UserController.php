<?php

namespace TUI\Toolkit\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Validator\Constraints\Null;
use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\UserBundle\Form\UserType;
use TUI\Toolkit\UserBundle\Form\AjaxuserType;
use TUI\Toolkit\UserBundle\Form\UserMediaType;
use TUI\Toolkit\UserBundle\Form\PasswordSetType;
use TUI\Toolkit\MediaBundle\Form\MediaType;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Action\RowAction;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

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

        // add email filter
        $column = $grid->getColumn('email');
        $column->setFilterable(true);
        $column->setTitle('Email');
        //$column->setFilterType('select');
        $column->setOperatorsVisible(false);

        // add enabled filter
        $column = $grid->getColumn('enabled');
        $column->setFilterable(true);
        $column->setTitle('Enabled');
        //$column->setFilterType('select');
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
        $resetAction = new RowAction('Reset', 'user_password_reset');
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
    public function ajax_organizer_createAction(Request $request)
    {
        $entity = new User();
        $form = $this->create_ajaxCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($em->getRepository('TUIToolkitUserBundle:User')->findByEmail($entity->getEmail()))
            {
                $existingUser = $em->getRepository('TUIToolkitUserBundle:User')->findByEmail($entity->getEmail());
                $existingUser = $existingUser[0];
                return new Response($existingUser);

            }else {
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
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new AjaxuserType($locale), $entity, array(
            'action' => $this->generateUrl('user_ajax_create'),
            'method' => 'POST',
            'attr' => array(
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
          $deleteForm =Null;
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
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        // only allow brand or higher to edit other users
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $securityContext = $this->get('security.context');
      if ($securityContext->isGranted('ROLE_BRAND') or $user->getID() == $id ) {

        $editForm = $this->createEditForm($entity);
        if ($this->canDeleteUser($id)) {
          $deleteForm = $this->createDeleteForm($id)->createView();
        } else {
          $deleteForm = Null;
        }

        return $this->render('TUIToolkitUserBundle:User:edit.html.twig', array(
          'entity' => $entity,
          'edit_form' => $editForm->createView(),
          'delete_form' => $deleteForm->createView(),
        ));
      } else {
        throw new AccessDeniedException('You do not have the ability to edit this User\'s information.');
      }
    }


    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function passwordSetAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $setForm = $this->createPasswordSetForm($entity);

        return $this->render('TUIToolkitUserBundle:User:password-set.html.twig', array(
            'set_form' => $setForm->createView(),
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

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Creates a form to set a User password.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createPasswordSetForm(User $entity)
    {
        $form = $this->createForm(new PasswordSetType(), $entity, array(
            'action' => $this->generateUrl('user_password_set', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        // get current user's roles and add form elements


        $form->add('submit', 'submit', array('label' => 'Set Password'));

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
            $this->get('session')->getFlashBag()->add('notice', 'User Saved: ' . $entity->getUsername());

          if (true === $this->get('security.context')->isGranted('ROLE_BRAND')) {
            return $this->redirect($this->generateUrl('user'));
          } else {
            return $this->redirect('/profile');
          }
        }

        return $this->render('TUIToolkitUserBundle:User:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing User entity.
     *
     */
    public function setPasswordAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $setForm = $this->createPasswordSetForm($entity);
        $setForm->handleRequest($request);

        if ($setForm->isValid()) {
            $entity->setPassword($setForm->getData()->getPlainPassword());
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'Password Updated for: ' . $entity->getUsername());

            return $this->redirect($this->generateUrl('fos_user_profile_show'));
        }

        return $this->render('TUIToolkitUserBundle:Registration:confirmed.html.twig', array(
            'user' => $entity,
            'set_form' => $setForm->createView(),
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
              $this->get('session')->getFlashBag()->add('notice', 'User Deleted: ' . $id);
            } else {
              $this->get('session')->getFlashBag()->add('notice', 'Unable to delete this user because they are associated qith Quotes or Tours');
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

      if ($this->canDeleteUser($entity->getId())) {
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice', 'User Deleted: ' . $entity->getUsername());
      } else {
          $this->get('session')->getFlashBag()->add('error', 'Unable to delete the User because they are associated with Quotes');
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
            ->setSubject('Toolkit Registration Confirmation')
            ->setFrom('registration@Toolkit.com')
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

        $this->get('session')->getFlashBag()->add('notice', 'A Notification was sent to ' . $user->getEmail());

        return $this->redirect($this->generateUrl('user'));

    }

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
            ->setSubject('Toolkit Password Reset')
            ->setFrom('reset@Toolkit.com')
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

        $this->get('session')->getFlashBag()->add('notice', 'A Notification was sent to ' . $user->getEmail());

        return $this->redirect($this->generateUrl('user'));

    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $em = $this->getDoctrine()->getManager();

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        $userManager->updateUser($user);

        //Get Brand Stuff
        $brand = $em->getRepository('BrandBundle:Brand')->findAll();
        $brand = $brand[0];

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('fos_user_registration_confirmed');
            $response = new RedirectResponse($url);
        }

        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));


        return $this->render('TUIToolkitUserBundle:Registration:activation.html.twig', array(
            'token' => $token,
            'brand' => $brand,
            'user' => $user,
            'form' => $form->createView(),
        ));

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
            ->setSubject('Toolkit Password Reset')
            ->setFrom('reset@Toolkit.com')
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
     * Reset user password
     */
    public function resetAction(Request $request, $token)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        $em = $this->getDoctrine()->getManager();

        //Get Brand Stuff
        $brand = $em->getRepository('BrandBundle:Brand')->findAll();
        $brand = $brand[0];

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

//        if (null !== $event->getResponse()) {
//            return $event->getResponse();
//        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->updateUser($user);

            if ($user->isEnabled() == false) {
                $user->setEnabled(true);
            }

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        return $this->render('TUIToolkitUserBundle:Resetting:reset.html.twig', array(
            'token' => $token,
            'brand' => $brand,
            'user' => $user,
            'form' => $form->createView(),
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
    $quotes=array();
    $securityContext = $this->get('security.context');
    if ($securityContext->isGranted('ROLE_BRAND'))
    {
      $today = new \DateTime();
      $qb= $this->getDoctrine()->getManager()->createQueryBuilder();
      $qb
        ->select('qv', 'q')
        ->from('QuoteBundle:QuoteVersion', 'qv')
        ->leftJoin('qv.quoteReference', 'q', 'WITH', 'q.id = qv.quoteReference')
        ->where('q.salesAgent = ?1')
        ->orWhere('q.secondaryContact = ?2')
        ->AndWhere('qv.converted = false')
        ->AndWhere('qv.expiryDate < ?3')
        ->AndWhere('q.converted = false')
        ->AndWhere('qv.isTemplate = false')
      ;
      $qb->setParameter(1 ,$id);
      $qb->setParameter(2 ,$id);
      $qb->setParameter(3, $today->format($format));
      $query = $qb->getQuery();
      $quotes = $query->getResult();
    } else {
      $em = $this->getDoctrine()->getManager();
      $permissions = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $id, 'class' => 'quote'));
      // this only returns pointers to quotes, so loop through and build quotes array
      foreach( $permissions as $permission )
      {
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
    $tours=array();
    $securityContext = $this->get('security.context');
    if ($securityContext->isGranted('ROLE_BRAND'))
    {
      $qb= $this->getDoctrine()->getManager()->createQueryBuilder();
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
      foreach( $permissions as $permission )
      {
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

    if($primary_organizerq || $primary_adminq || $secondary_adminq || $primary_organizert || $primary_admint || $secondary_admint || $permissions) {
      //$this->get('session')->getFlashBag()->add('error', 'Unable to delete the User because they are associated with Quotes or Tours');
      //return $this->redirect($this->generateUrl('user'));
      return false;
    } else {
      return true;
    }
  }

}
