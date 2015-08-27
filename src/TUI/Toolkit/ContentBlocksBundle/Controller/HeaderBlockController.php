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
class HeaderBlockController extends Controller
{


    /**
     * Creates a new ContentBlock entity as a Header Block.
     *
     */
    public function createAction(Request $request, $quoteVersion=null, $class=null)
    {
        $entity = new ContentBlock();
        $medias = array();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createCreateForm($entity, $quoteVersion, $class);
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
          $em->flush($entity);

          // add block to quoteVersion headerBlock field
          $quoteVersionEntity = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
          if (!$quoteVersionEntity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
          }
          $quoteVersionEntity->setHeaderBlock($entity);
          $em->persist($quoteVersionEntity);

          $em->flush($quoteVersionEntity);

          $responseContent = json_encode((array) $quoteVersionEntity);

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
        $form = $this->createForm($this->get('form.type.contentblock'), $entity, array(
            'action' => $this->generateUrl('manage_headerblock_create', array('quoteVersion' => $quoteVersion, 'class' => $class)),
          'method' => 'POST',
          'attr'  => array (
            'id' => 'ajax_contentblocks_form'
          ),
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
            'class'   => $class
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

        return $this->render('ContentBlocksBundle:HeaderBlock:show.html.twig', array(
            'entity' => $entity,
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

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'collection_ids' => $collectionIds,
            'edit_form' => $editForm->createView(),
            'quoteVersion' => $quoteVersion,
            'class'       => $class,
        ));
    }

    /**
    * Creates a form to edit a ContentBlock entity for Header Block.
    *
    * @param ContentBlock $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ContentBlock $entity, $quoteVersion=null, $class=null)
    {
        $form = $this->createForm($this->get('form.type.contentblock'), $entity, array(
            'action' => $this->generateUrl('manage_headerblock_update', array('id' => $entity->getId(), 'quoteVersion' => $quoteVersion, 'class' => $class)),
            'method' => 'POST',
            'attr'  => array (
              'id' => 'ajax_contentblocks_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing ContentBlock entity for Header Block.
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
   * Generate a New Contentblock via ajax call.
   *
   */
  public function ajaxNewAction(Request $request)
  {
    $error = false;

    $em = $this->getDoctrine()->getManager();
    $entity = new ContentBlock();
    $entity->setTitle('New Header Block');

      $em->persist($entity);
      $em->flush();

    $responseContent = json_encode(array($entity->getId() => $entity->getTitle()));

    return new Response($responseContent,
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
  }


}