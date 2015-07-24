<?php

namespace TUI\Toolkit\QuoteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\QuoteBundle\Entity\QuoteVersion;
use TUI\Toolkit\QuoteBundle\Form\QuoteVersionType;

/**
 * QuoteVersion controller.
 *
 */
class QuoteVersionController extends Controller
{

    /**
     * Lists all QuoteVersion entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('QuoteBundle:QuoteVersion')->findAll();

        return $this->render('QuoteBundle:QuoteVersion:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new QuoteVersion entity.
     *
     */
    public function createAction(Request $request)
    {
        //Handling the autocomplete field for organizer.  We need to convert the string from organizer into the object.
        $email = $_REQUEST['tui_toolkit_quotebundle_quoteversion']['quoteReference']['organizer'];
        $email = explode('[', $email);
        $email = rtrim($email[1], ']');
        $entity = new QuoteVersion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('TUIToolkitUserBundle:User')->findByEmail($email);
        $organizer =  $entities[0];
        $form->getData()->getQuoteReference()->setOrganizer($organizer);

        //handling request for SalesAgent same as we did with organizer
        $agentemail = $_REQUEST['tui_toolkit_quotebundle_quoteversion']['quoteReference']['salesAgent'];
        $agentemail = explode('[', $agentemail);
        $agentemail = rtrim($agentemail[1], ']');
        $agententities = $em->getRepository('TUIToolkitUserBundle:User')->findByEmail($agentemail);
        $salesagent =  $agententities[0];
        $form->getData()->getQuoteReference()->setSalesAgent($salesagent);

        //Handling the request for institution a little different than we did for the other 2.
        $institutionname = $_REQUEST['tui_toolkit_quotebundle_quoteversion']['quoteReference']['institution'];
        $institutionentities = $em->getRepository('InstitutionBundle:Institution')->findByName($institutionname);
        $institution =  $institutionentities[0];
        $form->getData()->getQuoteReference()->setInstitution($institution);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'Quote Saved: '. $entity->getQuoteReference()->getName());

            return $this->redirect($this->generateUrl('manage_quote'));
        }

        return $this->render('QuoteBundle:QuoteVersion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

  /**
   * Creates a new Quote Template .
   *
   */
  public function createTemplateAction(Request $request)
  {
    $entity = new QuoteVersion();
    $form = $this->createTemplateCreateForm($entity);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();
      $this->get('session')->getFlashBag()->add('notice', 'Quote Template Saved: '. $entity->getQuoteReference()->getName());

      return $this->redirect($this->generateUrl('manage_quote_templates'));
    }

    return $this->render('QuoteBundle:QuoteVersion:new.html.twig', array(
      'entity' => $entity,
      'form'   => $form->createView(),
      'template' => 'Template'
    ));
  }



  /**
     * Creates a form to create a QuoteVersion entity.
     *
     * @param QuoteVersion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(QuoteVersion $entity)
    {
        $form = $this->createForm(new QuoteVersionType(), $entity, array(
            'action' => $this->generateUrl('manage_quoteversion_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

  /**
   * Creates a form to create a Quote Template entity.
   *
   * @param QuoteVersion $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createTemplateCreateForm(QuoteVersion $entity)
  {
    $form = $this->createForm(new QuoteVersionType(), $entity, array(
      'action' => $this->generateUrl('manage_quoteversion_createtemplate'),
      'method' => 'POST',
    ));

    $form->add('submit', 'submit', array('label' => 'Create Template'));

    return $form;
  }

    /**
     * Displays a form to create a new QuoteVersion entity.
     *
     */
    public function newAction()
    {
        $entity = new QuoteVersion();
        $form   = $this->createCreateForm($entity);

        return $this->render('QuoteBundle:QuoteVersion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }


    /**
     * Finds and displays a QuoteVersion entity.
     *
     * param $id quoteReference id
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

      // Get all Quote versions referencing Parent Quote object
      $entity = $em->getRepository('QuoteBundle:QuoteVersion')->findByQuoteReference($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find QuoteVersion entity.');
        }

      // Get the quote with highest Version number and order array DESC
      usort($entity, function ($a, $b) {
        if ($a->getVersion() == $b->getVersion()) return 0;
        return $a->getVersion() > $b->getVersion() ? -1 : 1;
      });
      $quote = $entity[0];
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('QuoteBundle:QuoteVersion:show.html.twig', array(
            'entity'      => $quote,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing QuoteVersion entity.
     * @param $id id of the parent Quote object
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

      // Get all Quote versions referencing Parent Quote object
      $entity = $em->getRepository('QuoteBundle:QuoteVersion')->findByQuoteReference($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find QuoteVersion entity.' . $id);
        }

        // order array DESC by Version # & Get the quote version with highest Version number
        usort($entity, function ($a, $b) {
          if ($a->getVersion() == $b->getVersion()) return 0;
          return $a->getVersion() > $b->getVersion() ? -1 : 1;
        });
        $quote = $entity[0];
         if($quote->getQuoteReference()->getIsTemplate()){
           $template='Template';
         } else {$template='';}
        $editForm = $this->createEditForm($quote);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('QuoteBundle:QuoteVersion:edit.html.twig', array(
            'entity'      => $quote,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'template'    => $template,
        ));
    }

    /**
    * Creates a form to edit a QuoteVersion entity.
    *
    * @param QuoteVersion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(QuoteVersion $entity)
    {
        $form = $this->createForm(new QuoteVersionType(), $entity, array(
            'action' => $this->generateUrl('manage_quoteversion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing QuoteVersion entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find QuoteVersion entity.');
        }

        if($entity->getQuoteReference()->getIsTemplate()){
          $template='Template'; $route = '_templates';
        } else {$template=''; $route = '';}

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
          $this->get('session')->getFlashBag()->add('notice', 'Quote Saved: '. $entity->getQuoteReference()->getName());


          return $this->redirect($this->generateUrl('manage_quote' . $route));
        }

        return $this->render('QuoteBundle:QuoteVersion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'template'    => $template,
        ));
    }
    /**
     * Deletes a QuoteVersion entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find QuoteVersion entity.');
            }

            $em->remove($entity);
            $em->flush();
          $this->get('session')->getFlashBag()->add('notice', 'Quote Deleted: '. $entity->getName());

        }

        return $this->redirect($this->generateUrl('manage_quoteversion'));
    }

    /**
     * Creates a form to delete a Quote entity by id. (we dont delete quoteversions)
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_quote_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
