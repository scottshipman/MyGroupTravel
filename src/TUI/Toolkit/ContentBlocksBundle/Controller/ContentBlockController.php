<?php

namespace TUI\Toolkit\ContentBlocksBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\ContentBlocksBundle\Entity\ContentBlock;
use TUI\Toolkit\ContentBlocksBundle\Form\ContentBlockType;

/**
 * ContentBlock controller.
 *
 */
class ContentBlockController extends Controller
{

    /**
     * Lists all ContentBlock entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ContentBlocksBundle:ContentBlock')->findAll();

        return $this->render('ContentBlocksBundle:ContentBlock:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new ContentBlock entity.
     *
     */
    public function createAction(Request $request, $quoteVersion=null)
    {
        $entity = new ContentBlock();
        $medias = array();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createCreateForm($entity, $quoteVersion);
        $form->handleRequest($request);

        if (NULL != $form->getData()->getMedia()) {
            $fileIdString = $form->getData()->getMedia();
            $fileIds = explode(',', $fileIdString);

            foreach ($fileIds as $fileId) {
                $image = $em->getRepository('MediaBundle:Media')
                    ->findById($fileId);
                $medias[] = array_shift($image);
            }
        }
        if (!empty($medias)) {
            $form->getData()->setMedia($medias);

        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

          $responseContent = json_encode($entity->getContent());

          return new Response($responseContent,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
          );

            //return $this->redirect($this->generateUrl('manage_contentblocks_show', array('id' => $entity->getId())));
        }

        return $this->render('ContentBlocksBundle:ContentBlock:new.html.twig', array(
          'entity' => $entity,
          'form'   => $form->createView(),
          'quoteVersion'  => $quoteVersion,
        ));
    }

    /**
     * Creates a form to create a ContentBlock entity.
     *
     * @param ContentBlock $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ContentBlock $entity, $quoteVersion=null, $class=null)
    {
        $form = $this->createForm(new ContentBlockType(), $entity, array(
            'action' => $this->generateUrl('manage_contentblocks_create', array('quoteVersion' => $quoteVersion, 'class' => $class)),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ContentBlock entity.
     *
     */
    public function newAction($quoteVersion, $class)
    {
        $entity = new ContentBlock();
        $form   = $this->createCreateForm($entity, $quoteVersion, $class);

        return $this->render('ContentBlocksBundle:ContentBlock:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'quoteVersion'  => $quoteVersion,
        ));
    }

    /**
     * Finds and displays a ContentBlock entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        $collection = $entity->getMedia()->toArray();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $deleteForm = $this->createDeleteForm($id, $quoteVersion, $class);

        return $this->render('ContentBlocksBundle:ContentBlock:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
            'collection' => $collection,
        ));
    }

    /**
     * Displays a form to edit an existing ContentBlock entity.
     *
     */
    public function editAction($id, $quoteVersion=null, $class=null)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        $collection = $entity->getMedia()->toArray();
        foreach ($collection as $image) {
            $imageIds[] = $image->getId();
        }

        if ( isset($imageIds) ) {
            $collectionIds = implode(',', $imageIds);
        } else {
            $collectionIds = '';
        }

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }
      
        $entity->setMedia($collectionIds);
        $editForm = $this->createEditForm($entity, $quoteVersion, $class);
        $deleteForm = $this->createDeleteForm($id, $quoteVersion, $class);

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'collection_ids' => $collectionIds,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'quoteVersion' => $quoteVersion,
            'class'       => $class,
        ));
    }

    /**
    * Creates a form to edit a ContentBlock entity.
    *
    * @param ContentBlock $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ContentBlock $entity, $quoteVersion=null, $class=null)
    {
        $form = $this->createForm(new ContentBlockType(), $entity, array(
            'action' => $this->generateUrl('manage_contentblocks_update', array('id' => $entity->getId(), 'quoteVersion' => $quoteVersion, 'class' => $class)),
            'method' => 'POST',
            'attr'  => array (
              'id' => 'ajax_contentblocks_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing ContentBlock entity.
     *
     */
    public function updateAction(Request $request, $id, $quoteVersion=null, $class=null)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        $collection = $entity->getMedia()->toArray();


        if (!$entity) {
            throw  $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

          $deleteForm = $this->createDeleteForm($id, $quoteVersion, $class);
          $editForm = $this->createEditForm($entity, $quoteVersion, $class);
          $editForm->handleRequest($request);

        $medias = array();

        if (NULL != $editForm->getData()->getMedia()) {
            $fileIdString = $editForm->getData()->getMedia();
            $fileIds = explode(',', $fileIdString);

            foreach ($fileIds as $fileId) {
                $image = $em->getRepository('MediaBundle:Media')
                    ->findById($fileId);
                $medias[] = array_shift($image);
            }
        }
        if (!empty($medias)) {
            $editForm->getData()->setMedia($medias);

        }

        if ($editForm->isValid()) {
            $em->flush();

            //return $this->redirect($this->generateUrl('manage_contentblocks_edit', array('id' => $id)));
          if ($class=='QuoteVersion'){
            $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
          } elseif( $class =='TourVersion'){
            //$parent = $em->getRepository('TourBundle:TourVersion')->find($quoteVersion);
          }
          $responseContent =  json_encode($parent->getContent());
          return new Response($responseContent,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
          );

        }

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'collection' => $collection,
            'quoteVersion' => $quoteVersion,
            'class'       => $class,
        ));
    }

    /**
     * Deletes a ContentBlock entity.
     *
     */
    public function deleteAction(Request $request, $id, $quoteVersion, $class)
    {
        $error = false;

            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

            if (!$entity) {
                $error =  $this->createNotFoundException('Unable to find ContentBlock entity.');
            } else {

              $em->remove($entity);
              $em->flush();
            }

      if ($class=='QuoteVersion'){
        $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
      } elseif( $class =='TourVersion'){
        //$parent = $em->getRepository('TourBundle:TourVersion')->find($quoteVersion);
      }
      $responseContent =  json_encode($parent->getContent());
      return new Response($responseContent,
        Response::HTTP_OK,
        array('content-type' => 'application/json')
      );
    }

    /**
     * Creates a form to delete a ContentBlock entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id, $quoteVersion=null, $class=null)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_contentblocks_delete', array('id' => $id, 'quoteVersion' => $quoteVersion, 'class' => $class)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }

  /**
   * Locks a ContentBlock entity.
   *
   */
  public function lockAction(Request $request, $id, $quoteVersion=null, $class=null)
  {
    $error = false;

    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

    if (!$entity) {
      $error =  $this->createNotFoundException('Unable to find ContentBlock entity.');
    } else {
      $value = $entity->getLocked()==true ? false :true;
      $entity->setLocked($value);
      $em->persist($entity);
      $em->flush();
    }

    if ($class=='QuoteVersion'){
      $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
    } elseif( $class =='TourVersion'){
      //$parent = $em->getRepository('TourBundle:TourVersion')->find($quoteVersion);
    }
    $responseContent =  json_encode($parent->getContent());
    return new Response($responseContent,
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
  }

  /**
   * Locks a ContentBlock entity.
   *
   */
  public function hideAction(Request $request, $id, $quoteVersion=null, $class=null)
  {
    $error = false;

    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

    if (!$entity) {
      $error =  $this->createNotFoundException('Unable to find ContentBlock entity.');
    } else {
      $value = $entity->getHidden()==true ? false :true;
      $entity->setHidden($value);
      $em->persist($entity);
      $em->flush();
    }
    if ($class=='QuoteVersion'){
      $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
    } elseif( $class =='TourVersion'){
      //$parent = $em->getRepository('TourBundle:TourVersion')->find($quoteVersion);
    }
    $responseContent =  json_encode($parent->getContent());
    return new Response($responseContent,
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
  }

  /**
   * Generate a New Contentblock via ajax call.
   *
   */
  public function ajaxNewAction(Request $request)
  {
    $error = false;

    $em = $this->getDoctrine()->getManager();
    $entity = new ContentBlock();
    $entity->setTitle('New Content Block');

      $em->persist($entity);
      $em->flush();

    $responseContent = json_encode(array($entity->getId() => $entity->getTitle()));

    return new Response($responseContent,
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
  }


}
