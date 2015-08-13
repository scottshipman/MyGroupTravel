<?php

namespace TUI\Toolkit\ContentBlocksBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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
    public function createAction(Request $request)
    {
        $entity = new ContentBlock();
        $medias = array();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createCreateForm($entity);
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

            return $this->redirect($this->generateUrl('manage_contentblocks_show', array('id' => $entity->getId())));
        }

        return $this->render('ContentBlocksBundle:ContentBlock:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a ContentBlock entity.
     *
     * @param ContentBlock $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ContentBlock $entity)
    {
        $form = $this->createForm(new ContentBlockType(), $entity, array(
            'action' => $this->generateUrl('manage_contentblocks_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ContentBlock entity.
     *
     */
    public function newAction()
    {
        $entity = new ContentBlock();
        $form = $this->createCreateForm($entity);

        return $this->render('ContentBlocksBundle:ContentBlock:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
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

        $deleteForm = $this->createDeleteForm($id);

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
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        $collection = $entity->getMedia()->toArray();
        foreach ($collection as $image) {
            $imageIds[] = $image->getId();

        }
        $collectionIds = implode(',', $imageIds);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $entity->setMedia($collectionIds);
        $editForm = $this->createEditForm($entity);
//        $editForm->getData()->setMedia($collectionIds);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'collection_ids' => $collectionIds,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a ContentBlock entity.
     *
     * @param ContentBlock $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(ContentBlock $entity)
    {
        $form = $this->createForm(new ContentBlockType(), $entity, array(
            'action' => $this->generateUrl('manage_contentblocks_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing ContentBlock entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

        $collection = $entity->getMedia()->toArray();


        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
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

            return $this->redirect($this->generateUrl('manage_contentblocks_edit', array('id' => $id)));
        }

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'collection' => $collection,
        ));
    }

    /**
     * Deletes a ContentBlock entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ContentBlock entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('manage_contentblocks'));
    }

    /**
     * Creates a form to delete a ContentBlock entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_contentblocks_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }
}
