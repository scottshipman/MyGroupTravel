<?php

namespace TUI\Toolkit\BrandBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use TUI\Toolkit\BrandBundle\Entity\Brand;
use TUI\Toolkit\BrandBundle\Form\BrandType;
use TUI\Toolkit\BrandBundle\Form\TermsType;
use Application\Sonata\MediaBundle\Entity\Media;


/**
 * Brand controller.
 *
 * @Route("/manage/brand")
 */
class BrandController extends Controller
{

    /**
     * Lists all Brand entities.
     *
     * @Route("/", name="_manage_brand")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BrandBundle:Brand')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Brand entity.
     *
     * @Route("/", name="_manage_brand_create")
     * @Method("POST")
     * @Template("BrandBundle:Brand:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Brand();
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
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('brand.flash.save') . $entity->getName());


            return $this->redirect($this->generateUrl('_manage_brand_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Brand entity.
     *
     * @param Brand $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Brand $entity)
    {
        $form = $this->createForm(new BrandType(), $entity, array(
            'action' => $this->generateUrl('_manage_brand_create'),
            'method' => 'POST',
        ));

        $form
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('brand.actions.create')));

        return $form;
    }

    /**
     * Displays a form to create a new Brand entity.
     *
     * @Route("/new", name="_manage_brand_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Brand();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Brand entity.
     *
     * @Route("/{id}", name="_manage_brand_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BrandBundle:Brand')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Brand entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Brand entity.
     *
     * @Route("/{id}/edit", name="_manage_brand_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BrandBundle:Brand')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Brand entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a Brand entity.
     *
     * @param Brand $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Brand $entity)
    {
        $form = $this->createForm(new BrandType(), $entity, array(
            'action' => $this->generateUrl('_manage_brand_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('brand.actions.update')));

        return $form;
    }

    /**
     * Edits an existing Brand entity.
     *
     * @Route("/{id}", name="_manage_brand_update")
     * @Method("PUT")
     * @Template("BrandBundle:Brand:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BrandBundle:Brand')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Brand entity.');
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

        //handling ajax request for mediaEmail
        if (NULL != $editForm->getData()->getMediaEmail()){
            $fileId = $editForm->getData()->getMediaEmail();
          $entities = $em->getRepository('MediaBundle:Media')
            ->findById($fileId);

          if (NULL !== $entities) {
            $mediaEmail = array_shift($entities);
            $editForm->getData()->setMediaEmail($mediaEmail);
          }
        }



        if ($editForm->isValid()) {
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('brand.flash.save') . $entity->getName());
            return $this->redirect($this->generateUrl('_manage_brand_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Brand entity.
     *
     * @Route("/{id}", name="_manage_brand_delete")
     * @Method("DELETE")
     */

    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BrandBundle:Brand')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Brand entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('brand.flash.delete') . $entity->getName());

        }

        return $this->redirect($this->generateUrl('_manage_brand'));
    }

    /**
     * Creates a form to delete a Brand entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('_manage_brand_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('brand.actions.delete')))
            ->getForm();
    }

  /**
   * Show terms of service.
   *
   */
  public function termsAction()
  {
    $em = $this->getDoctrine()->getManager();
    // Get the default/hardcoded Brand
    $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

    // look for a configured brand
    if($brand_id = $this->container->getParameter('brand_id')){
      $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
    }

    if(!$brand){
      $brand = $default_brand;
    }

    return $this->render('BrandBundle:Brand:terms.html.twig', array('brand' => $brand));
  }

  /**
   * Displays a form to edit an existing Brand terms.
   *
   * @Route("/edit/terms", name="_manage_brand_terms")
   * @Method("GET")
   * @Template("BrandBundle:Brand:editTerms.html.twig")
   */
  public function editTermsAction()
  {
    $em = $this->getDoctrine()->getManager();
    // Get the default/hardcoded Brand
    $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

    // look for a configured brand
    if($brand_id = $this->container->getParameter('brand_id')){
      $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
    }

    if(!$brand){
      $brand = $default_brand;
    }

    $editForm = $this->editTermsForm($brand);
    $cancel = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->generateUrl('_manage_brand_show', array('id' => $brand->getId()));

    return array(
      'brand' => $brand,
      'form' => $editForm->createView(),
      'cancel' => $cancel,
    );
  }

  /**
   * Update a Brand terms of service.
   *
   * @Route("/terms/update", name="_manage_brand_terms_update")
   * @Method("POST")
   */
  public function updateTermsAction(Request $request)
  {

    $em = $this->getDoctrine()->getManager();
    // Get the default/hardcoded Brand
    $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

    // look for a configured brand
    if($brand_id = $this->container->getParameter('brand_id')){
      $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
    }

    if(!$brand){
      $brand = $default_brand;
    }

    $cancel = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->generateUrl('_manage_brand_show', array('id' => $entity->getId()));
    $form = $this->editTermsForm($brand);
    $form->handleRequest($request);


    if ($form->isValid()) {
      $em->persist($brand);
      $em->flush();
      $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('brand.flash.terms') . $brand->getName());


      return $this->redirect($this->generateUrl('_manage_brand_show', array('id' => $brand->getId())));
    }

    return array(
      'brand' => $brand,
      'form' => $form->createView(),
      'cancel' => $cancel,
    );
  }

  /**
   * Creates a form to edit a Brand terms.
   *
   * @param Brand $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function editTermsForm(Brand $entity)
  {
    $form = $this->createForm(new TermsType(), $entity, array(
      'action' => $this->generateUrl('_manage_brand_terms_update', array('id' => $entity->getId())),
      'method' => 'POST',
    ));

    $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('brand.actions.update')));

    return $form;
  }

}