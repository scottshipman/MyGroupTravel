<?php

namespace TUI\Toolkit\MediaBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use TUI\Toolkit\MediaBundle\Entity\MediaWrapper;
use TUI\Toolkit\MediaBundle\Form\MediaWrapperType;

/**
 * MediaWrapper controller.
 *
 */
class MediaWrapperController extends Controller
{

    /**
     * Lists all MediaWrapper entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MediaBundle:MediaWrapper')->findAll();

        return $this->render('MediaBundle:MediaWrapper:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new MediaWrapper entity.
     *
     */
    public function createAction(Request $request, $fileArray)
    {

            $entity = new MediaWrapper();
            $form = $this->createCreateForm($entity);
            $form->handleRequest($request);
            $em = $this->getDoctrine()->getManager();
            $images = array();

            $image = $em->getRepository('MediaBundle:Media')
                ->find($fileArray[0]);
            $images[] = $image;
            $form->getData()->setMedia($images);

            $form->getData()->setTitle(strip_tags($fileArray[1]));
            $form->getData()->setWeight(strip_tags($fileArray[2]));
            $em->persist($entity);
            $em->flush($entity);

        $serializer = $this->container->get('serializer');
        $imageWrapper = $serializer->serialize($entity, 'json');

        return new Response($imageWrapper);

    }



    /**
     * Creates a form to create a MediaWrapper entity.
     *
     * @param MediaWrapper $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private
    function createCreateForm(MediaWrapper $entity)
    {
        $form = $this->createForm(new MediaWrapperType(), $entity, array(
            'action' => $this->generateUrl('manage_mediawrapper_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new MediaWrapper entity.
     *
     */
    public
    function newAction()
    {
        $entity = new MediaWrapper();
        $form = $this->createCreateForm($entity);

        return $this->render('MediaBundle:MediaWrapper:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a MediaWrapper entity.
     *
     */
    public
    function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MediaBundle:MediaWrapper')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MediaWrapper entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('MediaBundle:MediaWrapper:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing MediaWrapper entity.
     *
     */
    public
    function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MediaBundle:MediaWrapper')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MediaWrapper entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('MediaBundle:MediaWrapper:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a MediaWrapper entity.
     *
     * @param MediaWrapper $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private
    function createEditForm(MediaWrapper $entity)
    {
        $form = $this->createForm(new MediaWrapperType(), $entity, array(
            'action' => $this->generateUrl('manage_mediawrapper_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing MediaWrapper entity.
     *
     */
    public
    function updateAction(Request $request, $id, $fileArray)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MediaBundle:MediaWrapper')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MediaWrapper entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $media = $entity->getMedia()->toArray();

        $editForm->getData()->setMedia($media);
        $editForm->getData()->setTitle(strip_tags($fileArray[1]));
        $editForm->getData()->setWeight(strip_tags($fileArray[2]));

        $em->persist($entity);
        $em->flush($entity);

        $serializer = $this->container->get('serializer');
        $imageWrapper = $serializer->serialize($entity, 'json');

        return new Response($imageWrapper);

    }

    /**
     * Deletes a MediaWrapper entity.
     *
     */
    public
    function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MediaBundle:MediaWrapper')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find MediaWrapper entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('manage_mediawrapper'));
    }

    /**
     * Creates a form to delete a MediaWrapper entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private
    function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_mediawrapper_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }
}
