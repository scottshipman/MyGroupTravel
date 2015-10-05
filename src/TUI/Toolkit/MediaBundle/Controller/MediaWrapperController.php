<?php

namespace TUI\Toolkit\MediaBundle\Controller;

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
    public function createAction(Request $request, $fileArrays)
    {
        $entity = new MediaWrapper();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $wrapper = array();
        $images = array();

        if($fileArrays != NULL) {
            foreach ($fileArrays as $properties) {
                    $image = $em->getRepository('MediaBundle:Media')
                        ->find($properties[0]);
                    $images[] = $image;
                    $form->getData()->setMedia($images);
                    $form->getData()->setTitle($properties[1]);
                    $form->getData()->setWeight($properties[2]);
                    $em->persist($entity);
                    $em->flush();
                    $wrapper[] = $entity;
                }


//            if ($form->isValid()) {
//                $em = $this->getDoctrine()->getManager();
//                $em->persist($wrapper);

                $responseContent = $wrapper;

            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );

//                return $this->redirect($this->generateUrl('manage_mediawrapper_show', array('id' => $entity->getId())));
//            }
        }

//        return $this->render('MediaBundle:MediaWrapper:new.html.twig', array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
//        ));
    }

    /**
     * Creates a form to create a MediaWrapper entity.
     *
     * @param MediaWrapper $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(MediaWrapper $entity)
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
    public function newAction()
    {
        $entity = new MediaWrapper();
        $form   = $this->createCreateForm($entity);

        return $this->render('MediaBundle:MediaWrapper:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a MediaWrapper entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MediaBundle:MediaWrapper')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MediaWrapper entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('MediaBundle:MediaWrapper:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing MediaWrapper entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MediaBundle:MediaWrapper')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MediaWrapper entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('MediaBundle:MediaWrapper:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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
    private function createEditForm(MediaWrapper $entity)
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
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MediaBundle:MediaWrapper')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MediaWrapper entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('manage_mediawrapper_edit', array('id' => $id)));
        }

        return $this->render('MediaBundle:MediaWrapper:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a MediaWrapper entity.
     *
     */
    public function deleteAction(Request $request, $id)
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
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_mediawrapper_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
