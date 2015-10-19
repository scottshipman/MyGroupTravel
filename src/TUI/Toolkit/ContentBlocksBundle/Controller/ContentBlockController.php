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
    public function createAction(Request $request, $class = null, $quoteVersion = null, $tabId = null)
    {
        $entity = new ContentBlock();
        $medias = array();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createCreateForm($entity, $class, $quoteVersion, $tabId);
        $form->handleRequest($request);

        if (NULL != $form->getData()->getMediaWrapper()) {
            $fileArrays = json_decode($form->getData()->getMediaWrapper());
            foreach ($fileArrays as $fileArray) {
                $mediaWrappers = $this->forward('MediaBundle:MediaWrapper:create', array(
                    'fileArray' => $fileArray,
                ));
                $content = $mediaWrappers->getContent();
                $wrappers[] = $content;
            }

            $newWrappers = array();
            foreach ($wrappers as $wrap) {
                $newContent = json_decode($wrap, true);
                $newWrappers[] = $newContent;
            }

            if ($newWrappers != NULL) {
                foreach ($newWrappers as $newWrapper) {
                    $wrapper = $em->getRepository('MediaBundle:MediaWrapper')->find($newWrapper['id']);
                    $medias[] = $wrapper;
                }
            }
        }

        if (!empty($medias)) {
            $form->getData()->setMediaWrapper($medias);

        }

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            if ($class == "QuoteVersion") {
                $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
            } elseif ($class == "tour") {
                $parent = $em->getRepository('TourBundle:Tour')->find($quoteVersion);
            }
            if (!$parent) {
                throw  $this->createNotFoundException('Unable to find Quote or Tour in order to update content array.');
            }
            // rebuild content array and remove block
            $content = $parent->getContent();
            foreach ($content as $tab => $data) {
              if ($tab == $tabId) {
                $content[$tab][1][] = $entity->getId();
              }
            }

              $parent->setContent($content);
              $em->flush($parent);

            $responseContent = json_encode($entity->getId());

            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );

//            return $this->redirect($this->generateUrl('manage_contentblocks_show', array('id' => $quoteId)));
        }

        return $this->render('ContentBlocksBundle:ContentBlock:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'quoteVersion' => $quoteVersion,
            '$tabId' => $tabId
        ));
    }

    /**
     * Creates a form to create a ContentBlock entity.
     *
     * @param ContentBlock $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ContentBlock $entity, $class = null, $quoteVersion = null, $tabId = null)
    {
        $form = $this->createForm($this->get('form.type.contentblock'), $entity, array(
            'action' => $this->generateUrl('manage_contentblocks_create', array( 'class' => $class, 'quoteVersion' => $quoteVersion, 'tabId' => $tabId)),
            'method' => 'POST',
            'attr' => array('id' => 'newBlockIdForm')
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('contentBlocks.actions.create')));

        return $form;
    }

    /**
     * Displays a form to create a new ContentBlock entity.
     *
     */
    public function newAction($class=null, $quoteVersion=null, $tabId=null)
    {
        $entity = new ContentBlock();
        $form = $this->createCreateForm($entity, $class, $quoteVersion, $tabId);
        $em = $this->getDoctrine()->getManager();
        $default_layout = $em->getRepository('ContentBlocksBundle:LayoutType')->findAll();
        $default_layout = $default_layout[0];
        $form->get('layoutType')->setData($default_layout);

        return $this->render('ContentBlocksBundle:ContentBlock:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'quoteVersion' => $quoteVersion,
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



        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $collection = $entity->getMediaWrapper()->toArray() ? $entity->getMediaWrapper()->toArray() : NULL;

        return $this->render('ContentBlocksBundle:ContentBlock:show.html.twig', array(
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

        $collection = $entity->getMediaWrapper()->toArray();
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

        $entity->setMediaWrapper($collectionIds);
        $editForm = $this->createEditForm($entity, $quoteVersion, $class);

        // if layout type is Null, set a default
        if(!$entity->getLayoutType()){
          $default_layout = $em->getRepository('ContentBlocksBundle:LayoutType')->findAll();
          $default_layout = $default_layout[0];
          $editForm->get('layoutType')->setData($default_layout);
        }

        $deleteForm = $this->createDeleteForm($id, $quoteVersion, $class);

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'collection_ids' => $collectionIds,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'quoteVersion' => $quoteVersion,
            'class' => $class,
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
        $form = $this->createForm($this->get('form.type.contentblock'), $entity, array(
            'action' => $this->generateUrl('manage_contentblocks_update', array('id' => $entity->getId(), 'quoteVersion' => $quoteVersion, 'class' => $class)),
            'method' => 'POST',
            'attr' => array(
                'id' => 'ajax_contentblocks_form',
                'class' => 'ajax_contentblocks_form',
            ),
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('contentBlocks.actions.update')));

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

        $collection = $entity->getMediaWrapper()->toArray();


        if (!$entity) {
            throw  $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

          $deleteForm = $this->createDeleteForm($id, $quoteVersion, $class);
          $editForm = $this->createEditForm($entity, $quoteVersion, $class);
          $editForm->handleRequest($request);

        $medias = array();

        if (NULL != $editForm->getData()->getMediaWrapper()) {
            $fileArrays = json_decode($editForm->getData()->getMediaWrapper());
            $wrappers = array();
            foreach ($fileArrays as $fileArray) {
                $mediaWrappers = $this->forward('MediaBundle:MediaWrapper:create', array(
                    'fileArray' => $fileArray,
                ));
                $content = $mediaWrappers->getContent();
                $wrappers[] = $content;
            }

            $newWrappers = array();
            if ($wrappers != null) {
                foreach ($wrappers as $wrap) {
                    $newContent = json_decode($wrap, true);
                    $newWrappers[] = $newContent;
                }
            }
            if ($newWrappers != NULL) {
                foreach ($newWrappers as $newWrapper) {
                    $wrapper = $em->getRepository('MediaBundle:MediaWrapper')->find($newWrapper['id']);
                    $medias[] = $wrapper;
                }
            }
        }

        if (!empty($medias)) {
            $editForm->getData()->setMediaWrapper($medias);

        }

        if ($editForm->isValid()) {
            $em->flush();

            //return $this->redirect($this->generateUrl('manage_contentblocks_edit', array('id' => $id)));
          if ($class=='QuoteVersion'){
            $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
          } elseif( $class =='tour'){
            $parent = $em->getRepository('TourBundle:Tour')->find($quoteVersion);
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
            'class' => $class,
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
                $error =  $this->createNotFoundException('Unable to find ContentBlock entity for deletion.');
            } else {

              $em->remove($entity);
              $em->flush($entity);
            }

      if ($class=='QuoteVersion'){
        $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
      } elseif( $class =='tour'){
        $parent = $em->getRepository('TourBundle:Tour')->find($quoteVersion);
      }
      if(!$parent){
        throw  $this->createNotFoundException('Unable to find Quote or Tour in order to update content array.');
      }
      // rebuild content array and remove block
      $content = $parent->getContent();
      foreach($content as $k1=>$q) {
        foreach($q[1] as $k2=>$v) {
          if($v == $id) {
            $foo = '';
            unset($content[$k1][1][$k2]);
          }
        }
      }

      $parent->setContent($content);
      $em->flush($parent);

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
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('contentBlocks.actions.delete')))
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
    } elseif( $class =='tour'){
      $parent = $em->getRepository('TourBundle:Tour')->find($quoteVersion);
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
  public function resizeAction(Request $request, $id, $quoteVersion=null, $class=null)
  {
    $error = false;

    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

    if (!$entity) {
      $error =  $this->createNotFoundException('Unable to find ContentBlock entity.');
    } else {
      if ( $entity->getDoubleWidth() == 1 ) {
        $entity->setDoubleWidth(0);
      } else {
        $entity->setDoubleWidth(1);
      }
      $em->persist($entity);
      $em->flush();
    }

    if ($class=='QuoteVersion'){
      $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
    } elseif( $class =='tour'){
      $parent = $em->getRepository('TourBundle:Tour')->find($quoteVersion);
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
    } elseif( $class =='tour'){
      $parent = $em->getRepository('TourBundle:Tour')->find($quoteVersion);
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
    $entity->setTitle($this->get('translator')->trans('contentBlocks.placeholder.title'));

      $em->persist($entity);
      $em->flush();

    $responseContent = json_encode(array($entity->getId() => $entity->getTitle()));

    return new Response($responseContent,
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
  }
  /**
   * Adds a new tab into the content blocks array field
   *
   * @param mixed $id The entity id
   *
   * @return Symfony\Component\HttpFoundation\Response
   */
  public function newTabAction(Request $request, $id, $class)
  {

    $em = $this->getDoctrine()->getManager();
      if ($class == "QuoteVersion") {
          $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
      }
      elseif ($class == "tour"){
          $entity = $em->getRepository('TourBundle:Tour')->find($id);
      }
    //$content = $entity->getContent();
    $content=array();

    if ($request->getMethod() == 'POST') {
      $newContent = $request->request->all();
    }

    foreach($newContent as $tab => $data){
      $blocks = isset($data[1]) ? $data[1] : array();
      if(!empty($blocks)) {
        $blockArr = array();
        foreach ($blocks as $block) {
          $blockArr[] = (int)substr($block, 15);
        }
        $content[$tab] = array($data[0], $blockArr);
      } else {
        $content[$tab]=array($data[0], array());
      }
    }
    $entity->setContent($content);
    $em->persist($entity);
    $em->flush();

    $responseContent = json_encode($entity->getContent());

    return new Response($responseContent,
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
  }


    /**
     * Adds a new tab into the content blocks array field on the site preview page
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function newSiteTabAction(Request $request, $id, $class)
    {

        $em = $this->getDoctrine()->getManager();
        if ($class == "quoteversion") {
            $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
        }
        elseif($class == "tour"){
            $entity = $em->getRepository('TourBundle:Tour')->find($id);
        }
        if (!$entity) {
            throw  $this->createNotFoundException('Unable to find Quote or Tour Entity While Adding a Tab');
        }
        //$content = $entity->getContent();
        $content=$entity->getContent();

        if ($request->getMethod() == 'POST') {
            $newContent = $request->request->all();
        }

        foreach($newContent as $tab => $data){
            $blocks = isset($data[1]) ? $data[1] : array();
            if(!empty($blocks)) {
                $blockArr = array();
                foreach ($blocks as $block) {
                    $blockArr[] = (int)substr($block, 15);
                }
                $content[$tab] = array($data[0], $blockArr);
            } else {
                $content[$tab]=array($data[0], array());
            }
        }
        $entity->setContent($content);
        $em->persist($entity);
        $em->flush();

        $responseContent = json_encode($entity->getContent());

        return new Response($responseContent,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }

}
