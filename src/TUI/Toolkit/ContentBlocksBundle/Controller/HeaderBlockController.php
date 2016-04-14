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
    public function createAction(Request $request, $quoteVersion = null, $class = null)
    {
        // Check context permissions.
        $permission_class = strtolower($class);
        if ($permission_class == 'quoteversion') {
            $permission_class = 'quote';
        }

        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($quoteVersion, $permission_class, $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $entity = new ContentBlock();
        $medias = array();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createCreateForm($entity, $quoteVersion, $class);
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
            $em->flush($entity);

            // add block to quoteVersion headerBlock field
            if ($class == "tour") {
                $quoteVersionEntity = $em->getRepository('TourBundle:Tour')->find($quoteVersion);
            } elseif ($class == "QuoteVersion") {
                $quoteVersionEntity = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
            }
            if (!$quoteVersionEntity) {
                throw $this->createNotFoundException('Unable to find ContentBlock entity.');
            }
            $quoteVersionEntity->setHeaderBlock($entity);
            $em->persist($quoteVersionEntity);

            $em->flush($quoteVersionEntity);

            //generate js friendly array
            foreach ($entity as $key => $value) {
                $blockArr[$key] = $value;
            }
            $responseContent = json_encode($blockArr);

            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );

            //return $this->redirect($this->generateUrl('manage_contentblocks_show', array('id' => $entity->getId())));
        }

        return $this->render('ContentBlocksBundle:ContentBlock:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'quoteVersion' => $quoteVersion,
        ));
    }

    /**
     * Creates a form to create a ContentBlock entity.
     *
     * @param ContentBlock $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ContentBlock $entity, $quoteVersion = null, $class = null)
    {
        $form = $this->createForm($this->get('form.type.contentblock'), $entity, array(
            'action' => $this->generateUrl('manage_headerblock_create', array('quoteVersion' => $quoteVersion, 'class' => $class)),
            'method' => 'POST',
            'attr' => array(
                'id' => 'ajax_contentblocks_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('contentBlocks.actions.create')));

        return $form;
    }

    /**
     * Displays a form to create a new ContentBlock entity.
     *
     */
    public function newAction($quoteVersion, $class)
    {
        // Check context permissions.
        $permission_class = strtolower($class);
        if ($permission_class == 'quoteversion') {
            $permission_class = 'quote';
        }

        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($quoteVersion, $permission_class, $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $entity = new ContentBlock();
        $form = $this->createCreateForm($entity, $quoteVersion, $class);

        return $this->render('ContentBlocksBundle:ContentBlock:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'quoteVersion' => $quoteVersion,
            'class' => $class
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

        $collection = $entity->getMediaWrapper()->toArray();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Header Block entity.');
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
    public function editAction($id, $quoteVersion = null, $class = null)
    {
        // Check context permissions.
        $permission_class = strtolower($class);
        if ($permission_class == 'quoteversion') {
            $permission_class = 'quote';
        }

        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($quoteVersion, $permission_class, $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        $collection = $entity->getMediaWrapper()->toArray();
        foreach ($collection as $image) {
            $imageIds[] = $image->getId();
        }

        if (isset($imageIds)) {
            $collectionIds = implode(',', $imageIds);
        } else {
            $collectionIds = '';
        }

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $entity->setMediaWrapper($collectionIds);
        $editForm = $this->createEditForm($entity, $quoteVersion, $class);

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'collection_ids' => $collectionIds,
            'edit_form' => $editForm->createView(),
            'quoteVersion' => $quoteVersion,
            'class' => $class,
        ));
    }

    /**
     * Displays a form to edit an existing ContentBlock entity in Layout editor mode.
     *
     */
    public function editLayoutAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        $collection = $entity->getMediaWrapper()->toArray();
        foreach ($collection as $image) {
            $imageIds[] = $image->getId();
        }

        if (isset($imageIds)) {
            $collectionIds = implode(',', $imageIds);
        } else {
            $collectionIds = '';
        }

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $entity->setMediaWrapper($collectionIds);
        $editForm = $this->createEditLayoutForm($entity);

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'collection_ids' => $collectionIds,
            'edit_form' => $editForm->createView(),
            'quoteVersion' => null,
            'class' => null,
        ));
    }


    /**
     * Creates a form to edit a ContentBlock entity for Header Block.
     *
     * @param ContentBlock $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(ContentBlock $entity, $quoteVersion = null, $class = null)
    {
        $form = $this->createForm($this->get('form.type.contentblock'), $entity, array(
            'action' => $this->generateUrl('manage_headerblock_update', array('id' => $entity->getId(), 'quoteVersion' => $quoteVersion, 'class' => $class)),
            'method' => 'POST',
            'attr' => array(
                'id' => 'ajax_contentblocks_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('contentBlocks.actions.update')));

        return $form;
    }

    /**
     * Creates a form to edit a ContentBlock entity for Header Block in Layout editor mode.
     *
     * @param ContentBlock $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditLayoutForm(ContentBlock $entity)
    {
        $form = $this->createForm($this->get('form.type.contentblock'), $entity, array(
            'action' => $this->generateUrl('manage_headerblock_layout_update', array('id' => $entity->getId())),
            'method' => 'POST',
            'attr' => array(
                'id' => 'ajax_headerblock_layout_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('contentBlocks.actions.update')));

        return $form;
    }


    /**
     * Edits an existing ContentBlock entity for Header Block.
     *
     */
    public function updateAction(Request $request, $id, $quoteVersion = null, $class = null)
    {
        // Check context permissions.
        $permission_class = strtolower($class);
        if ($permission_class == 'quoteversion') {
            $permission_class = 'quote';
        }

        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $permission = $this->get("permission.set_permission")->getPermission($quoteVersion, $permission_class, $user->getId());
            if ($permission == NULL || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
                throw $this->createAccessDeniedException();
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        $collection = $entity->getMediaWrapper()->toArray();


        if (!$entity) {
            throw  $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $editForm = $this->createEditForm($entity, $quoteVersion, $class);
        $editForm->handleRequest($request);

        $medias = array();

        if (NULL != $editForm->getData()->getMediaWrapper()) {
            $fileArrays = json_decode($editForm->getData()->getMediaWrapper());
            $wrappers = array();
            if (!empty($fileArrays)) {
                foreach ($fileArrays as $fileArray) {
                    $mediaWrappers = $this->forward('MediaBundle:MediaWrapper:create', array(
                        'fileArray' => $fileArray,
                    ));
                    $content = $mediaWrappers->getContent();
                    $wrappers[] = $content;
                }
            }

            $newWrappers = array();
            if ($wrappers != null || !empty($wrappers)) {
                foreach ($wrappers as $wrap) {
                    $newContent = json_decode($wrap, true);
                    $newWrappers[] = $newContent;
                }
            }
            if ($newWrappers != NULL || !empty($newWrappers)) {
                foreach ($newWrappers as $newWrapper) {
                    $wrapper = $em->getRepository('MediaBundle:MediaWrapper')->find($newWrapper['id']);
                    $medias[] = $wrapper;
                }
            }
        }

        if (!empty($medias)) {
            $editForm->getData()->setMediaWrapper($medias);
        }
        else if (empty($medias)) {
            $editForm->getData()->setMediaWrapper(NULL);
        }

        if ($editForm->isValid()) {
            $em->flush();

            //return $this->redirect($this->generateUrl('manage_contentblocks_edit', array('id' => $id)));
            if ($class == 'QuoteVersion') {
                $parent = $em->getRepository('QuoteBundle:QuoteVersion')->find($quoteVersion);
            } elseif ($class == 'tour') {
                $parent = $em->getRepository('TourBundle:Tour')->find($quoteVersion);
            }
            $responseContent = json_encode($parent->getContent());
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
     * Edits an existing ContentBlock entity for Header Block in Layout Editor
     * Always returns response object not twigs.
     *
     */
    public function updateLayoutAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        if (!$entity) {
            throw  $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $editForm = $this->createEditLayoutForm($entity);
        $editForm->handleRequest($request);

        $medias = array();

        if (NULL != $editForm->getData()->getMediaWrapper()) {
            $fileArrays = json_decode($editForm->getData()->getMediaWrapper());
            $wrappers = array();
            if (!empty($fileArrays)) {
                foreach ($fileArrays as $fileArray) {
                    $mediaWrappers = $this->forward('MediaBundle:MediaWrapper:create', array(
                        'fileArray' => $fileArray,
                    ));
                    $content = $mediaWrappers->getContent();
                    $wrappers[] = $content;
                }
            }

            $newWrappers = array();
            if ($wrappers != null || !empty($wrappers)) {
                foreach ($wrappers as $wrap) {
                    $newContent = json_decode($wrap, true);
                    $newWrappers[] = $newContent;
                }
            }
            if ($newWrappers != NULL || !empty($newWrappers)) {
                foreach ($newWrappers as $newWrapper) {
                    $wrapper = $em->getRepository('MediaBundle:MediaWrapper')->find($newWrapper['id']);
                    $medias[] = $wrapper;
                }
            }
        }

        if (!empty($medias)) {
            $editForm->getData()->setMediaWrapper($medias);
        }
        else if (empty($medias)) {
            $editForm->getData()->setMediaWrapper(NULL);
        }

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $responseContent = json_encode((array)$entity);
            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );

        }
        $responseContent = json_encode((array)$editForm);
        return new Response($responseContent,
            419,
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
        $entity->setTitle($this->get('translator')->trans('contentBlocks.placeholder.header'));

        $em->persist($entity);
        $em->flush();

        $responseContent = json_encode(array($entity->getId() => $entity->getTitle()));

        return new Response($responseContent,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }


}
