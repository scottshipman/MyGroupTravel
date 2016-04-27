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

    if($quoteNumber===NULL) {
      throw $this->createNotFoundException('Make sure there is a correct Quote number.');
    }
    else {
      $quoteVersion = $em->getRepository('QuoteBundle:QuoteVersion')->findOneBy(array('quoteNumber' => $quoteNumber));

      if (!$quoteVersion) {
        throw $this->createNotFoundException('Unable to find Quote number provided.');
      }
      else {
        if ($entity->getQuoteNumber() != $quoteVersion->getQuoteNumber()) {
          throw $this->createNotFoundException('Quote number doesn\'t match tour quote number.');
        }
      }
    }

    //Get all brand stuff
    $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

    // look for a configured brand
    if($brand_id = $this->container->getParameter('brand_id')){
      $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
    }

    if(!$brand) {
      $brand = $default_brand;
    }

    if ($securityContext->isGranted('ROLE_BRAND')){
      $editable = TRUE;
    };

    if (isset($permission)){
      if (in_array('organizer', $permission) || in_array('assistant', $permission)) {
      $editable = TRUE;
      }
    };

    //Get logger service for errors
    $logger = $this->get('logger');

    // get the content blocks to send to twig
    $items = array();
    $tabs = array();
    $content = $entity->getContent();
    foreach ($content as $tab => $data) {
      $tabs[$tab] = $data[0];
      $blocks = isset($data[1]) ? $data[1] : array();
      $blockCount = count($blocks);
      if (!empty($blocks)) {
        if ($blockCount <= 1) {
          $blocks = array_shift($blocks);
          $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($blocks);
          if (!$blockObj) {
            $items[$blocks] = null;
            $logger->error('Content Block '.$blocks. ' cannot be found');
          } else {
            $items[$blockObj->getId()] = $blockObj;
          }
        } else {
          foreach ($blocks as $block) {
            $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find((int)$block);
            if (!$blockObj) {
              $items[$block] = null;
              $logger->error('Content Block '.$block. ' cannot be found');
            } else {
              $items[$blockObj->getId()] = $blockObj;
            }
          }
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
      $warningMsg[] = $this->get('translator')->trans('tour.flash.warning.expired') . " " . $entity->getQuoteReference()->getSalesAgent()->getFirstName() ." ".  $entity->getQuoteReference()->getSalesAgent()->getLastName() . " " .  $this->get('translator')->trans('tour.flash.warning.at') . " " . $entity->getQuoteReference()->getSalesAgent()->getEmail();
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
      $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.no_match'));
      return $this->redirect($this->generateUrl('tour_site_show', array('id' => $id, 'quoteNumber' => $quoteNumber)));
    }
  }

  /**
   * Record Unique Views for a Tour View
   * @param $entity
   *
   * RULES:
   *  1) dont log Brand or Admins
   *  2) only log anonymous viewers
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

      // increment the view OR shareView on the record for anonymous viewers only
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('TourBundle:Tour')->find($id);
      if (FALSE === $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        if ($entity) {

          $entity->setViews($entity->getViews() + 1);

          $em->persist($entity);
          $em->flush();
          return;
        }
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

      // if no quoteNumber supplied in URL, then prompt for quoteNumber first
      $securityContext = $this->get('security.context');
      $user = $securityContext->getToken()->getUser();
      if($user !='anon.') {
        $permission = $this->get("permission.set_permission")
          ->getPermission($id, 'tour', $user->getId());
      }

      if ($securityContext->isGranted('ROLE_BRAND') || in_array('organizer', $permission) || in_array('assistant', $permission)){
        $editable = TRUE;
      }


      //Get logger service for errors
      $logger = $this->get('logger');

      // get the content blocks to send to twig
      $items = array();
      $tabs = array();
      $content = $entity->getContent();
      foreach ($content as $tab => $data) {
        $tabs[$tab] = $data[0];
        $blocks = isset($data[1]) ? $data[1] : array();
        $blockCount = count($blocks);
        if (!empty($blocks)) {
          if ($blockCount <= 1) {
            $blocks = array_shift($blocks);
            $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($blocks);
            if (!$blockObj) {
              $items[$blocks] = null;
              $logger->error('Content Block '.$blocks. ' cannot be found');
            } else {
              $items[$blockObj->getId()] = $blockObj;
            }
          } else {
            foreach ($blocks as $block) {
              $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find((int)$block);
              if (!$blockObj) {
                $items[$block] = null;
                $logger->error('Content Block '.$block. ' cannot be found');
              } else {
                $items[$blockObj->getId()] = $blockObj;
              }
            }
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
        $warningMsg[] = $this->get('translator')->trans('tour.flash.warning.expired') . " " . $entity->getQuoteReference()->getSalesAgent()->getFirstName() ." ".  $entity->getQuoteReference()->getSalesAgent()->getLastName() . " " .  $this->get('translator')->trans('tour.flash.warning.at') . " " . $entity->getQuoteReference()->getSalesAgent()->getEmail();
      }

      //brand stuff
      $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

      // look for a configured brand
      if($brand_id = $this->container->getParameter('brand_id')){
        $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
      }

      if(!$brand) {
        $brand = $default_brand;
      }

      $request = $this->getRequest();
      $path = $request->getScheme() . '://' . $request->getHttpHost();

      $data = array(
        'entity' => $entity,
        'locale' => $locale,
        'items' => $items,
        'header' => $headerBlock,
        'path' => $path,
        'editable' => $editable,
        'brand' => $brand,

      );

      $fileNameFinal = $entity->getQuoteNumber()? $entity->getQuoteNumber() : 'template-' . $id;
      $html = $this->renderView( 'TourBundle:TourSite:tourPDF.html.twig', $data );


      return new Response($this->get('knp_snappy.pdf')->getOutputFromHtml($html),
          200,
          array(
              'Content-Type' => 'application/pdf',
              'Content-Disposition'   => 'attachment; filename="' . $fileNameFinal . '.pdf"',
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
    // Check context permissions.
    $securityContext = $this->container->get('security.authorization_checker');
    if (!$securityContext->isGranted('ROLE_BRAND')) {
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $permission = $this->get("permission.set_permission")->getPermission($id, 'tour', $user->getId());
      if ($permission == null || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
        throw $this->createAccessDeniedException();
      }
    }

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
    // Check context permissions.
    $securityContext = $this->container->get('security.authorization_checker');
    if (!$securityContext->isGranted('ROLE_BRAND')) {
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $permission = $this->get("permission.set_permission")->getPermission($id, 'tour', $user->getId());
      if ($permission == null || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
        throw $this->createAccessDeniedException();
      }
    }

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

  /**
   * Edit the Display Name on a Tour from jEditable.
   *
   */
  public function updateTourDisplayNameAction($id) {
    // Check context permissions.
    $securityContext = $this->container->get('security.authorization_checker');
    if (!$securityContext->isGranted('ROLE_BRAND')) {
      $user = $this->get('security.token_storage')->getToken()->getUser();
      $permission = $this->get("permission.set_permission")->getPermission($id, 'tour', $user->getId());
      if ($permission == null || (!in_array('organizer', $permission) && !in_array('assistant', $permission))) {
        throw $this->createAccessDeniedException();
      }
    }

    $value=htmlspecialchars($_POST['value']);
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('TourBundle:Tour')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find Tour entity while jediting Tour Display Name.');
    }

    $entity->setDisplayName($value);
    $em->persist($entity);
    $em->flush();

    $responseContent =  json_encode(array('success'=> $value));
    return new Response($responseContent,
        Response::HTTP_OK,
        array('content-type' => 'application/json')
    );

  }
}


