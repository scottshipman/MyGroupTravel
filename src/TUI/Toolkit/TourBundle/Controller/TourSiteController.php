<?php

namespace TUI\Toolkit\TourBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\TourBundle\Form\PromptType;
use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\TourBundle\Form\TourSummaryType;
use TUI\Toolkit\PermissionBundle\Entity\Permission;
use TUI\Toolkit\PermissionBundle\Controller\PermissionService;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Action\RowAction;
use Doctrine\ORM\Query\ResultSetMapping;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * QuoteVersion controller.
 *
 */
class TourSiteController extends Controller
{

  /**
   * Finds and displays a QuoteVersion entity.
   *
   * param $id quoteReference id
   *
   */
  public function siteShowAction($id, $quoteNumber = null)
  {

    $alternate=FALSE;
    $editable = false;
    $permission = array();
    // TODO if user is allowed to edit then set $editable to true
    // if organizer or if brand or higher (check permission table for organizer)

        $em = $this->getDoctrine()->getManager();

        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }

        $collection = $entity->getMedia()->toArray() ? $entity->getMedia()->toArray() : NULL;


      // if no quoteNumber supplied in URL, then prompt for quoteNumber first
    $securityContext = $this->get('security.context');
    $user = $securityContext->getToken()->getUser();
    if($user !='anon.') {
      $permission = $this->get("permission.set_permission")
        ->getPermission($id, 'tour', $user->getId());
    }


    //Get all brand stuff
    $brand = $em->getRepository('BrandBundle:Brand')->findAll();
    $brand = $brand[0];

    if ($securityContext->isGranted('ROLE_BRAND') || in_array('organizer', $permission)){
      $editable = TRUE;
    }
    // get the content blocks to send to twig
    $items=array(); $tabs=array();
    $content = $entity->getContent();
    foreach($content as $tab => $data){
      $tabs[$tab] = $data[0];
      $blocks = isset($data[1]) ? $data[1] : array();
      if(!empty($blocks)) {
        foreach ($blocks as $block) {
          $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find((int) $block);
          if(!$blockObj){
            throw $this->createNotFoundException('Unable to find Content Block entity.');
          }
          $items[$blockObj->getId()] = $blockObj;
        }
      }
    }

    // get the content block that is the header block
    $header = $entity->getHeaderBlock();
    if($header !=NULL){
      $headerBlock=$em->getRepository('ContentBlocksBundle:ContentBlock')->find($header);
    } else {
      $headerBlock = NULL;
    }

    // send warning messages
    $warningMsg = array();
    if(Null!==$entity->getExpiryDate() && $entity->getExpiryDate() < date($date_format)){
      $warningMsg[] = $this->get('translator')->trans('tour.flash.warning.expired') . " " . $entity->getQuoteReference()->getSalesAgent()->getFirstName() ." ".  $entity->getQuoteReference()->getSalesAgent()->getLasttName() . " " .  $this->get('translator')->trans('tour.flash.warning.at') . " " . $entity->getQuoteReference()->getSalesAgent()->getEmail();
    }

    // Record Views
    $this->setTourViews($entity->getId());



    return $this->render('TourBundle:TourSite:siteShow.html.twig', array(
      'entity'      => $entity,
      'locale'      => $locale,
      'items'       => $items,
      'tabs'        => $tabs,
      'warning'     => $warningMsg,
      'header'      => $headerBlock,
      'editable'    => $editable,
      'brand'       => $brand,
      'collection' => $collection,
    ));
  }

  /**
   * Create Tour Prompt Form
   */

  public function createPromptTypeForm($id)
  {
    $form = $this->createForm(new PromptType(), null, array(
      'action' => $this->generateUrl('tour_site_validate', array('id' => $id)),
      'method' => 'POST',
    ));

    $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.go')));

    return $form;
  }

  /**
   * Validate a Quote Number matches the requested tour id
   *
   * redirect to full URL, else redirect to form page again
   */

  public function siteValidateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('TourBundle:Tour')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Tour entity.');
    }
    $promptForm = $this->createPromptTypeForm($id);
    $promptForm->handleRequest($request);

    $quoteNumber = $promptForm->get('quoteNumber')->getData();

    $realQuoteNumber = $entity->getQuoteNumber();

    if($quoteNumber==$realQuoteNumber){
      // send to full page
      return $this->redirect($this->generateUrl('tour_site_show', array('id' => $id, 'quoteNumber' => $realQuoteNumber)));
    } else {
    //send back to form page
      $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.no_match'));
      return $this->redirect($this->generateUrl('tour_site_action_show', array('id' => $id)));
  }


  }

  /**
   * Record Unique Views for a Tour View
   * @param $entity
   *
   * RULES:
   *  1) dont log Brand or Admins
   *  2) ???
   */
  public function setTourViews($id) {
    $cookie = '';
    $securityContext = $this->get('security.context');

    if (FALSE === $securityContext->isGranted('ROLE_BRAND')) {

      if (isset($_COOKIE['toolkit'])) {
        if (strpos($_COOKIE['toolkit'], 'tour-' . $id . '~') !== FALSE) {
          return;
        }
        $cookie = $_COOKIE['toolkit'] . '\n';
      }
      $cookie .= 'tour-' . $id . '~';
      setcookie('toolkit', $cookie, time() + (365 * 24 * 60 * 60), "/"); // 1 year expiration

      // increment the view OR shareView on the record
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('TourBundle:Tour')->find($id);
      if ($entity) {

        $entity->setViews($entity->getViews() + 1);

        $em->persist($entity);
        $em->flush();
        return;
      }
    }
  }


    public function sitePDFAction($id, $quoteNumber = null)
    {
      $alternate=FALSE;
      $editable = false;

      $em = $this->getDoctrine()->getManager();

      $locale = $this->container->getParameter('locale');
      $date_format = $this->container->getParameter('date_format');

      // Get all Quote versions referencing Parent Quote object
      $entity = $em->getRepository('TourBundle:Tour')->find($id);
      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Tour entity for PDF.');
      }


        // get the content blocks to send to twig
        $items=array(); $tabs=array();
        $content = $entity->getContent();
        foreach($content as $tab => $data){
            $tabs[$tab] = $data[0];
            $blocks = isset($data[1]) ? $data[1] : array();
            if(!empty($blocks)) {
                foreach ($blocks as $block) {
                    $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find((int) $block);
                    if(!$blockObj){
                        throw $this->createNotFoundException('Unable to find Content Block entity.');
                    }
                    $items[$blockObj->getId()] = $blockObj;
                }
            }
        }

      // get the content block that is the header block
      $header = $entity->getHeaderBlock();
      if($header !=NULL){
        $headerBlock=$em->getRepository('ContentBlocksBundle:ContentBlock')->find($header);
      } else {
        $headerBlock = NULL;
      }

      // send warning messages
      $warningMsg = array();
      if($entity->getExpiryDate() < date($date_format)){
        $warningMsg[] = $this->get('translator')->trans('tour.flash.warning.expired') . " " . $entity->getQuoteReference()->getSalesAgent()->getFirstName() ." ".  $entity->getQuoteReference()->getSalesAgent()->getLasttName() . " " .  $this->get('translator')->trans('tour.flash.warning.at') . " " . $entity->getQuoteReference()->getSalesAgent()->getEmail();
      }

      $request = $this->getRequest();
      $path = $request->getScheme() . '://' . $request->getHttpHost();

      $data = array(
        'entity' => $entity,
        'locale' => $locale,
        'items' => $items,
        'header' => $headerBlock,
        'editable' =>  $editable,
        'path' => $path,
      );

      $html = $this->renderView( 'TourBundle:TourSite:sitePDF.html.twig', $data );

      $dompdf = new \DOMPDF();
      $dompdf->set_base_path($path . '/');
      $dompdf->load_html($html);
      $dompdf->render();

      return new Response($dompdf->output(), 200, array(
          'Content-Type' => 'application/pdf'
      ));
    }


  /**
   * @param $id
   *
   * Show summary data on Site Show page as embedded twig
   */
  public function showSummaryAction($id)
  {
    $locale = $this->container->getParameter('locale');
    $em = $this->getDoctrine()->getManager();
    $tour = $em->getRepository('TourBundle:Tour')->find($id);
    if(!$tour){
      throw $this->createNotFoundException('Unable to find Tour entity for summary header display.');
    }

    return $this->render('TourBundle:TourSite:tourSummary.html.twig', array(
      'tour' => $tour,
      'locale'  => $locale,
    ));
  }

  /**
   * @param $id
   *
   * Edit summary data on Site Show page using ajaxForm
   */
  public function editSummaryAction($id) {

    $em = $this->getDoctrine()->getManager();

      // Get all Quote versions referencing Parent Quote object
    $entity = $em->getRepository('TourBundle:Tour')->find($id);

    if (!$entity) {
    throw $this->createNotFoundException('Unable to find Tour entity for Summary Edit.' . $id);
    }

    $editForm = $this->createSummaryEditForm($entity);
    $date_format = $this->container->getParameter('date_format');

    return $this->render('TourBundle:TourSite:editSummary.html.twig', array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'date_format' => $date_format,
    ));
  }

  /**
   * Creates a form to edit a Tour Summary.
   *
   * @param Tour $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createSummaryEditForm(Tour $entity)
  {
    $locale = $this->container->getParameter('locale');
    $form = $this->createForm(new TourSummaryType($locale), $entity, array(
      'action' => $this->generateUrl('tour_summary_update', array('id' => $entity->getId())),
      'method' => 'POST',
      'attr'   => array(
        'id'    => 'form-summary-edit-form',
        'class' => 'form-summary-edit-form',
      )
    ));

    $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.update')));

    return $form;
  }

  /**
   * Updates an existing Tour Summary.
   *
   */
  public function updateSummaryAction(Request $request, $id)
  {
    $date_format = $this->container->getParameter('date_format');
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('TourBundle:Tour')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Tour entity.');
    }


    $editForm = $this->createSummaryEditForm($entity);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $em->flush();

      $responseContent =  json_encode((array) $entity);
      return new Response($responseContent,
        Response::HTTP_OK,
        array('content-type' => 'application/json')
      );

    }

    return $this->render('TourBundle:Tour:editSummary.html.twig', array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
      'date_format' => $date_format,
    ));
  }


}


