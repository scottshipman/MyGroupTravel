<?php

namespace TUI\Toolkit\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\UserBundle\Form\UserType;
use Application\Sonata\MediaBundle\Entity\Media;
use Application\Sonata\MediaBundle\ApplicationSonataMediaBundle;

/**
 * User controller.
 *
 *
 */
class UserController extends Controller
{

    /**
     * Lists all User entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TUIToolkitUserBundle:User')->findAll();

        return $this->render('TUIToolkitUserBundle:User:index.html.twig', array(
            'entities' => $entities,
        ));
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
          $this->get('session')->getFlashBag()->add('notice', 'User Saved: '. $entity->getUsername());

            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
        }

        return $this->render('TUIToolkitUserBundle:User:new.html.twig', array(
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

      if($this->get('security.context')->isGranted('ROLE_ADMIN')){
        $form->add('enabled')
              ->add('roles', 'choice', array(
                'choices'  => array('ROLE_USER' => 'User', 'ROLE_CUSTOMER' => 'CUSTOMER', 'ROLE_BRAND'=> 'BRAND', 'ROLE_ADMIN'=>'ADMIN',),
                'multiple' => true,
                'expanded' => TRUE,
              ));
      }

      if($this->get('security.context')->isGranted('ROLE_BRAND')){
        //what does Brand add?
      }

      //    if(!$this->get('security.context')->isGranted('ROLE_BRAND')){
      //is a CUSTOMER role so they must be creating a chiuld so set parent to them
      $curr_user = $this->get('security.context')->getToken()->getUser();
      $form->add('userParent', 'hidden', array(
        'data' => $curr_user->getID(),
      ));
      //   }



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
        $form   = $this->createCreateForm($entity);

        return $this->render('TUIToolkitUserBundle:User:new.html.twig', array(
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

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TUIToolkitUserBundle:User:show.html.twig', array(
            'entity'      => $entity,
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
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TUIToolkitUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
          $this->get('session')->getFlashBag()->add('notice', 'User Saved: '. $entity->getUsername());

            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return $this->render('TUIToolkitUserBundle:User:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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
          $this->get('session')->getFlashBag()->add('notice', 'User Deleted: '. $entity->getUsername());
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
            ->getForm()
        ;
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

        if ($form->isValid()) {

            // Getting sonata media manager service
            $mediaManager = $this->container->get('sonata.media.manager.media');

            // Getting sonata media object and saving media
            $media = new Media;
            $media->setBinaryContent($request->files->get('file'));
            $media->setContext('user');
            $media->setProviderName('sonata.media.provider.image');
            $mediaManager->save($media);


        }

        return $this->render('TUIToolkitUserBundle:User:dropzone.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
