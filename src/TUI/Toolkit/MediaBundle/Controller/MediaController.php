<?php
/**
 * Created scottshipman
 * Date: 7/28/15
 */
namespace TUI\Toolkit\MediaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use TUI\Toolkit\MediaBundle\Entity\Media;
use TUI\Toolkit\MediaBundle\Form\MediaType;


/**
 * Media controller.
 *
 *
 */class MediaController extends Controller
{


  /**
   * Lists all Media entities.
   *
   * @Method("GET")
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('MediaBundle:Media')->findAll();

    return $this->render('MediaBundle:Media:index.html.twig', array(
      'entities' => $entities,
    ));
  }

  /**
   * Creates a new Media entity.
   *
   * @Route("/", name="media_create")
   * @Method("POST")
   * @Template("MediaBundle:Media:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new Media();
    $form = $this->createCreateForm($entity);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();
      $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('media.flash.save') . $entity->getFilename());


      return $this->redirect($this->generateUrl('media', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }

  /**
   * Creates a form to create a Media entity.
   *
   * @param Media $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createCreateForm(Media $entity)
  {
    $helper = $this->container->get('oneup_uploader.templating.uploader_helper');
    $endpoint = $helper->endpoint('user');

    $form = $this->createForm(new MediaType(), $entity, array(
      'action' => $endpoint,
      'method' => 'POST',
      'attr' => array(
        'class' => 'dropzone',
        )
      ));

    return $form;
  }

  /**
   * Creates a form to create a Media entity.
   *
   * @param Media $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  public function createDropzoneFormAction(Request $request, $context)
  {
    $form = $this->createFormBuilder()->getForm();

    $form->handleRequest($request);
    return $this->render('MediaBundle:Media:dropzone.html.twig', array(
      'context'=>$context,
      'form' => $form->createView(),
    ));
  }

  /**
   * Creates a form (light) to create a Media entity.
   *
   * @param Media $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  public function createDropzoneLightFormAction(Request $request, $context)
  {
    $form = $this->createFormBuilder()->getForm();

    $form->handleRequest($request);
    return $this->render('MediaBundle:Media:dropzone_light.html.twig', array(
      'context'=>$context,
      'form' => $form->createView(),
    ));
  }

  /**
   * Creates a form (brand) to create a Media entity.
   *
   * @param Media $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  public function createDropzoneBrandFormAction(Request $request, $context, $identifier = 'primary')
  {
    $form = $this->createFormBuilder()->getForm();

    $form->handleRequest($request);
    return $this->render('MediaBundle:Media:dropzone_brand.html.twig', array(
      'context' => $context,
      'identifier' => $identifier,
      'form' => $form->createView(),
    ));
  }

  /**
   * Displays a form to create a new Media entity.
   *
   * @Route("/new", name="media_new")
   * @Method("GET")
   * @Template()
   */
  public function newAction()
  {
    $entity = new Media();
    $form = $this->createCreateForm($entity);

    return array(
      'entity' => $entity,
      'form' => $form->createView(),
    );
  }
  /**
   * Displays a form to edit an existing Media entity.
   *
   * @Route("/{id}/edit", name="media_edit")
   * @Method("GET")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('MediaBundle:Media')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Media entity.');
    }

    $editForm = $this->createEditForm($entity);

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
    );
  }

  /**
   * Creates a form to edit a Media entity.
   *
   * @param Media $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createEditForm(Media $entity)
  {
    $form = $this->createForm(new MediaType(), $entity, array(
      'action' => $this->generateUrl('media_update', array('id' => $entity->getId())),
      'method' => 'PUT',
    ));

    $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('media.actions.update')));

    return $form;
  }

  /**
   * Edits an existing Media entity.
   *
   * @Route("/{id}", name="media_update")
   * @Method("PUT")
   * @Template("MediaBundle:Media:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('MediaBundle:Media')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Media entity.');
    }

    $editForm = $this->createEditForm($entity);
    $editForm->handleRequest($request);


    if ($editForm->isValid()) {
      $em->flush();
      $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('media.flash.save') . $entity->getFilename());
      return $this->redirect($this->generateUrl('media_edit', array('id' => $id)));
    }

    return array(
      'entity' => $entity,
    );
  }

}