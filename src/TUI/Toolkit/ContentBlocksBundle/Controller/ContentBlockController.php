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
        $form = $this->createCreateForm($entity, $quoteVersion);
        $form->handleRequest($request);

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

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ContentBlocksBundle:ContentBlock:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
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

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

        $editForm = $this->createEditForm($entity, $quoteVersion, $class);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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
            'method' => 'PUT',
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

        if (!$entity) {
            throw  $this->createNotFoundException('Unable to find ContentBlock entity.');
        }

          $deleteForm = $this->createDeleteForm($id);
          $editForm = $this->createEditForm($entity);
          $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            //return $this->redirect($this->generateUrl('manage_contentblocks_edit', array('id' => $id)));
          $responseContent =  json_encode($entity->getContent());

          return new Response($responseContent,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
          );

        }

        return $this->render('ContentBlocksBundle:ContentBlock:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'quoteVersion' => $quoteVersion,
            'class'       => $class,
        ));
    }
    /**
     * Deletes a ContentBlock entity.
     *
     */
    public function deleteAction(Request $request, $id, $quoteVersion=null, $class = null)
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


        //return $this->redirect($this->generateUrl('manage_contentblocks'));

      $responseContent = $error ? $error : json_encode($entity->getContent());

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
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_contentblocks_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

  /**
   * Locks a ContentBlock entity.
   *
   */
  public function lockAction(Request $request, $id)
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


    //return $this->redirect($this->generateUrl('manage_contentblocks'));

    $responseContent = $error ? $error : json_encode($entity);

    return new Response($responseContent,
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
  }

  /**
   * Locks a ContentBlock entity.
   *
   */
  public function hideAction(Request $request, $id)
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


    //return $this->redirect($this->generateUrl('manage_contentblocks'));

    $responseContent = $error ? $error : json_encode($entity);

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
