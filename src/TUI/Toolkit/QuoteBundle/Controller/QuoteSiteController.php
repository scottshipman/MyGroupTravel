<?php

namespace TUI\Toolkit\QuoteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use TUI\Toolkit\QuoteBundle\Form\PromptType;
use TUI\Toolkit\QuoteBundle\Entity\QuoteVersion;
use TUI\Toolkit\QuoteBundle\Form\QuoteChangeRequestType;
use TUI\Toolkit\QuoteBundle\Form\QuoteAcceptType;
use TUI\Toolkit\QuoteBundle\Form\QuoteSummaryType;
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
class QuoteSiteController extends Controller
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
    $related=FALSE;
    $editable = false;
    $share = strpos($_SERVER['REQUEST_URI'], 'share') !== FALSE ? TRUE : FALSE ;

    $permission = array();
    // TODO if user is allowed to edit then set $editable to true
    // if organizer or if brand or higher (check permission table for organizer)

        $em = $this->getDoctrine()->getManager();

        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        // Get all Quote versions referencing Parent Quote object
        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find QuoteVersion entity in order to show the quote site.');
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

    // if no quoteNumber supplied in URL, then prompt for quoteNumber first
    $securityContext = $this->get('security.context');
    $user = $securityContext->getToken()->getUser();

    // if user is anonymous and this is a template, redirect to access denied.

      if ($user =='anon.' and $entity->getIsTemplate() == TRUE) {
          throw new AccessDeniedException('You are not authorized to view this URL or it does not exist.');
      }

    if($user !='anon.') {
      $permission = $this->get("permission.set_permission")
        ->getPermission($id, 'quote', $user->getId());
    }

    if(($quoteNumber===NULL || $quoteNumber != $entity->getQuoteNumber()) && FALSE === $securityContext->isGranted('ROLE_BRAND')){

      $promptForm = $this->createPromptTypeForm($id);
      return $this->render('QuoteBundle:QuoteSite:sitePrompt.html.twig', array(
        'entity'      => $entity,
        'form'        => $promptForm->createView(),
      ));
    }

    if ($securityContext->isGranted('ROLE_BRAND') ){
      if ($entity->getConverted() == false ) {
        $editable = TRUE;
      }
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
            throw $this->createNotFoundException('Unable to find Content Block entity while compiling the quote site.');
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
      $warningMsg[] = $this->get('translator')->trans('quote.exception.expired') . " $entity>getQuoteReference()->getSalesAgent()->getFirstName()   $entity>getQuoteReference()->getSalesAgent()->getLasttName()  at $entity>getQuoteReference()->getSalesAgent()->getEmail()";
    }

    // Record Views
    $this->setQuoteViews($entity->getId());

    // prepare Alternate Quotes flag.
    $qr = $entity->getQuotereference()->getId();
    $versions = $em->getRepository('QuoteBundle:QuoteVersion')->findBy(array('quoteReference' => $qr));
    if(count($versions) > 1){
      $alternate=TRUE;
    }

    // check for related quotes too


    $quote = $em->getRepository('QuoteBundle:Quote')->find($qr);

      $relOrganizer = $quote->getOrganizer() ? $quote->getOrganizer()->getId() : NULL ;
      $relInstitution = $quote->getInstitution() ? $quote->getInstitution()->getId() : NULL ;
    $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
    $qb
      ->select('q')
      ->from('QuoteBundle:Quote', 'q')
      ->where('q.organizer = ?1')
      ->andWhere('q.institution = ?2')
      ->andWhere('q.converted = FALSE');
    $qb->setParameter(1, $relOrganizer);
    $qb->setParameter(2, $relInstitution);
    $query = $qb->getQuery();
    $relatedQuotes = $query->getResult();
    if(count($relatedQuotes) > 1){
      $related=TRUE;
    }


    return $this->render('QuoteBundle:QuoteSite:siteShow.html.twig', array(
      'entity'      => $entity,
      'locale'      => $locale,
      'items'       => $items,
      'tabs'        => $tabs,
      'warning'     => $warningMsg,
      'header'      => $headerBlock,
      'editable'  =>  $editable,
      'alternate' => $alternate,
      'related'  => $related,
      'brand' => $brand,
      'share' => $share,
    ));
  }

  /**
   * Create Quote Prompt Form
   */

  public function createPromptTypeForm($id)
  {
    $form = $this->createForm(new PromptType(), null, array(
      'action' => $this->generateUrl('quote_site_validate', array('id' => $id)),
      'method' => 'POST',
    ));

    $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('quote.actions.prompt')));

    return $form;
  }

  /**
   * Validate a Quote Number matches the requested quote id
   *
   * redirect to full URL, else redirect to form page again
   */

  public function siteValidateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find QuoteVersion entity while validating Quote Number form.');
    }
    $promptForm = $this->createPromptTypeForm($id);
    $promptForm->handleRequest($request);

    $quoteNumber = $promptForm->get('quoteNumber')->getData();

    $realQuoteNumber = $entity->getQuoteNumber();

    if($quoteNumber==$realQuoteNumber){
      // send to full page
      return $this->redirect($this->generateUrl('quote_site_show', array('id' => $id, 'quoteNumber' => $realQuoteNumber)));
    } else {
    //send back to form page
      $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('quote.exception.prompt_error'));
      return $this->redirect($this->generateUrl('quote_site_action_show', array('id' => $id)));
  }


  }

  /**
   * Record Unique Views for a Quote View
   * @param $entity
   *
   * RULES:
   *  1) dont log Brand or Admins
   *  2) ???
   */
  public function setQuoteViews($id) {
    $cookie = '';
    $securityContext = $this->get('security.context');

    if (FALSE === $securityContext->isGranted('ROLE_BRAND')) {

      if (isset($_COOKIE['toolkit'])) {
        if (strpos($_COOKIE['toolkit'], 'quote-' . $id . '~') !== FALSE) {
          return;
        }
        $cookie = $_COOKIE['toolkit'] . '\n';
      }
      $cookie .= 'quote-' . $id . '~';
      setcookie('toolkit', $cookie, time() + (365 * 24 * 60 * 60), "/"); // 1 year expiration

      // increment the view OR shareView on the record
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
      if ($entity) {
        if(strpos($_SERVER['REQUEST_URI'], 'share')===false) {
          $entity->setViews($entity->getViews() + 1);
        } else {
          $entity->setShareViews($entity->getShareViews() + 1);
        }
        $em->persist($entity);
        $em->flush();
        return;
      }
    }
  }

    /**
     * Creates a form to make a change request to a QuoteVersion entity.
     *
     * @param QuoteVersion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */

    public function createChangeRequestFormAction($id)
    {
        $changeForm = $this->createForm(new QuoteChangeRequestType(), array(), array(
            'action' => $this->generateUrl('quote_site_change_request', array('id' => $id)),
            'method' => 'POST',
        ));

        $changeForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('quote.actions.change_request')));

        return $changeForm;

    }

    public function newRequestAction($id)
    {
        $changeForm = $this->createChangeRequestFormAction($id);
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');


        return $this->render('QuoteBundle:QuoteSite:changeRequest.html.twig', array(
            'change_request_form' => $changeForm->createView(),
            'entity' => $entity,
            'locale' => $locale,
            'date_format' => $date_format,
        ));
    }

    public function requestChangeAction(Request $request, $id)
    {
        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        $changeForm = $this->createChangeRequestFormAction($id);
        $changeForm->handleRequest($request);
        $changes = $changeForm->get('changes')->getData();
        $additional = $changeForm->get('additional')->getData();

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->findById($id);
        $entity = $entity[0];
        $departure = $entity->getDepartureDate();
        $tourName = $entity->getName();
        $salesAgent = $entity->getQuoteReference()->getSalesAgent();
        $agentEmail = $salesAgent->getEmail();

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('quote.email.change_request.subject') .' '. $tourName)
            ->setFrom($this->container->getParameter('user_system_email'))
            ->setTo($agentEmail)
            ->setBody(
                $this->renderView(
                    'QuoteBundle:Emails:changerequest.html.twig',
                    array(
                        'brand' => $brand,
                        'entity' => $entity,
                        'changes' => $changes,
                        'additional' => $additional,
                        'departure' => $departure,
                        'tour_name' => $tourName,
                        'locale' => $locale,
                        'date_format' => $date_format,
                    )
                ), 'text/html');

        $this->get('mailer')->send($message);

        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('quote.flash.change_request') . ' '. $tourName);

        return $this->redirect($this->generateUrl('quote_site_show', array('id' => $id, "quoteNumber" => $entity->getQuoteNumber())));

    }

    public function createAcceptFormAction($id)
    {
        $acceptForm = $this->createForm(new QuoteAcceptType(), array(), array(
            'action' => $this->generateUrl('quote_site_quote_accepted', array('id' => $id)),
            'method' => 'POST',
        ));

        $acceptForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('quote.actions.accept_form')));

        return $acceptForm;

    }

    public function newAcceptAction($id)
    {
        $acceptForm = $this->createAcceptFormAction($id);
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        return $this->render('QuoteBundle:QuoteSite:acceptQuote.html.twig', array(
            'accept_form' => $acceptForm->createView(),
            'entity' => $entity,
            'locale' => $locale,
            'date_format' => $date_format,
        ));
    }


    /**
     * Creates the action for when a user accepts a quote
     *
     * @param Request $request object and the quote $id
     *
     * @return \Symfony\Component\Form\Form The form
     */

    public function quoteAcceptedAction(Request $request, $id)
    {
        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        $acceptForm = $this->createAcceptFormAction($id);
        $acceptForm->handleRequest($request);
        $additional = $acceptForm->get('additional')->getData();

        $secondaryAgent = "";
        $toArray = array();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->findById($id);
        $entity = $entity[0];

        $departure = $entity->getDepartureDate();
        $tourName = $entity->getName();
        if ($entity->getQuoteReference()->getSalesAgent()) {
            $agentEmail = $entity->getQuoteReference()->getSalesAgent()->getEmail();
            $toArray[] = $agentEmail;
        }
        if ($entity->getQuoteReference()->getSecondaryContact()) {
            $secondaryAgent = $entity->getQuoteReference()->getSecondaryContact()->getEmail();
            $toArray[] = $secondaryAgent;
        }

        $quoteNumber = $entity->getQuoteNumber();

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('quote.email.liked.subject') . ' ' . $quoteNumber)
            ->setFrom($this->container->getParameter('user_system_email'))
            ->setBody(
                $this->renderView(
                    'QuoteBundle:Emails:acceptQuote.html.twig',
                    array(
                        'brand' => $brand,
                        'entity' => $entity,
                        'departure' => $departure,
                        'tour_name' => $tourName,
                        'additional' => $additional,
                        'locale' => $locale,
                        'date_format' => $date_format,
                    )
                ), 'text/html');

        $em->persist($entity);
        $em->flush();

        foreach($toArray as $user) {
            $message->setTo($user);
            $this->get('mailer')->send($message);
        }

        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('quote.flash.liked') . ' ' . $tourName );

        return $this->redirect($this->generateUrl('quote_site_show', array('id' => $id, "quoteNumber" => $entity->getQuoteNumber())));

    }

    public function sitePDFAction($id, $quoteNumber = null)
    {
      $alternate=FALSE;
      $editable = false;

      $em = $this->getDoctrine()->getManager();

      $locale = $this->container->getParameter('locale');
      $date_format = $this->container->getParameter('date_format');

      // Get all Quote versions referencing Parent Quote object
      $entity = $em->getRepository('QuoteBundle:QuoteVersion')->findById($id);
      if (!$entity) {
        throw $this->createNotFoundException('Unable to find QuoteVersion entity in order to display as PDF.');
      }

      // get the content blocks to send to twig
      $items=array();
      $content = $entity[0]->getContent();
      foreach($content as $tab){
        foreach($tab[1] as $key=>$block){
          $object=$em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);
          if($object != null){
            $items[$block] = $object;
          }
        }
      }

      // get the content block that is the header block
      $header = $entity[0]->getHeaderBlock();
      if($header !=NULL){
        $headerBlock=$em->getRepository('ContentBlocksBundle:ContentBlock')->find($header);
      } else {
        $headerBlock = NULL;
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

      // send warning messages
      $warningMsg = array();
        if ($entity[0]->getExpiryDate() != null) {
            if ($entity[0]->getExpiryDate() < date($date_format)) {
                $warningMsg[] = $this->get('translator')->trans('quote.exception.expired') . " $entity>getQuoteReference()->getSalesAgent()->getFirstName()   $entity>getQuoteReference()->getSalesAgent()->getLasttName()  at $entity>getQuoteReference()->getSalesAgent()->getEmail()";
            }
        }

      $request = $this->getRequest();
      $path = $request->getScheme() . '://' . $request->getHttpHost();

      // prepare Alternate Quotes flag.
      $versions = $em->getRepository('QuoteBundle:QuoteVersion')->findByQuoteReference($entity[0]->getQuoteReference());
      if(count($versions>1)){
        $alternate=TRUE;
      }

      $data = array(
        'entity' => $entity[0],
        'locale' => $locale,
        'items' => $items,
        'header' => $headerBlock,
        'editable' =>  $editable,
        'path' => $path,
        'alternate' => $alternate,
        'brand' => $brand,
        'warning' => NULL,
        'related' => NULL,
      );

//        $html = $this->renderView('MyBundle:Foo:bar.html.twig', array(
//            'some'  => $vars
//        ));
        $quoteNumber = $entity[0]->getQuoteNumber();
      $html = $this->renderView( 'QuoteBundle:QuoteSite:quotePDF.html.twig', $data );

//      $dompdf = new \DOMPDF();
//      $dompdf->set_base_path($path . '/');
//      $dompdf->load_html($html);
//      $dompdf->render();

//        return $this->get('knp_snappy.pdf')->generate('http://toolkit.travelbound.co.uk/quote/view/21/a', '/srv/www/Toolkit/web/static/exports/file.pdf');
      return new Response($this->get('knp_snappy.pdf')->getOutputFromHtml($html),
          200,
          array(
          'Content-Type' => 'application/pdf',
          'Content-Disposition'   => 'attachment; filename="' . $quoteNumber . '.pdf"'
      ));
    }

  /**
   * @param $id
   *
   * Get all Quote Versions whose quoteReference is ID and return a stand alone Twig of tabular data
   */
  public function getQuoteSiblingsAction($id)
  {
    $locale = $this->container->getParameter('locale');
    $em = $this->getDoctrine()->getManager();
    $tour = $em->getRepository('QuoteBundle:Quote')->find($id);
    if(!$tour){
      throw $this->createNotFoundException('Unable to find Quote entity for alternate quotes tab.');
    }
    $tourName = $tour->getName();
    $entities = $em->getRepository('QuoteBundle:QuoteVersion')->findByQuoteReference($id);

    if(!$entities){
      throw $this->createNotFoundException('Unable to find QuoteVersion entities for alternate quotes tab.');
    }

    return $this->render('QuoteBundle:QuoteSite:quoteSiblings.html.twig', array(
      'entities' => $entities,
      'locale'  => $locale,
      'tourName'  => $tourName,
    ));
  }

  /**
   * @param $id
   *
   * Get all Quote Versions whose organizer and institution match and return a stand alone Twig of tabular data
   *
   *  not converted
   *  same organizer
   *  same institution
   *
   */
  public function getRelatedQuotesAction($id)
  {
    $entities = array();
    $locale = $this->container->getParameter('locale');
    $em = $this->getDoctrine()->getManager();
    $tour = $em->getRepository('QuoteBundle:Quote')->find($id);
    if(!$tour){
      throw $this->createNotFoundException('Unable to find Quote entity for related quotes tab.');
    }
    $tourOrganizer = $tour->getOrganizer();
    $tourInstitution = $tour->getInstitution();

    $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
    $qb
      ->select('q')
      ->from('QuoteBundle:Quote', 'q')
      ->where('q.organizer = ?1')
      ->andWhere('q.institution = ?2')
      ->andWhere('q.converted = FALSE')
      ->andWhere('q.id != ?3');
    $qb->setParameter(1, $tourOrganizer->getId());
    $qb->setParameter(2, $tourInstitution->getId());
    $qb->setParameter(3, $id);
    $query = $qb->getQuery();
    $quotes = $query->getResult();

    if(!$quotes){
      throw $this->createNotFoundException('Unable to find Quote entities for related quotes tab.');
    }

    foreach($quotes as $relQuote){
      $relQuoteID = $relQuote->getId();
      $relatedversions =  $em->getRepository('QuoteBundle:QuoteVersion')->findBy(array('quoteReference' => $relQuoteID));
      foreach($relatedversions as $version) {
        $entities[] = $version;
      }
    }

    return $this->render('QuoteBundle:QuoteSite:relatedQuotes.html.twig', array(
      'entities' => $entities,
      'locale'  => $locale,
      'tourOrganizer'  => $tourOrganizer,
      'tourInstitution'  => $tourInstitution,
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
    $quote = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
    $share = strpos($_SERVER['REQUEST_URI'], 'share') !== FALSE ? TRUE : FALSE ;
    if(!$quote){
      throw $this->createNotFoundException('Unable to find Quote entity for summary header display.');
    }

    return $this->render('QuoteBundle:QuoteSite:quoteSummary.html.twig', array(
      'quote' => $quote,
      'locale'  => $locale,
      'share'   => $share,
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
    $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

    if (!$entity) {
    throw $this->createNotFoundException('Unable to find QuoteVersion entity for Summary Edit.' . $id);
    }

    $editForm = $this->createSummaryEditForm($entity);
    $date_format = $this->container->getParameter('date_format');

    return $this->render('QuoteBundle:QuoteSite:editSummary.html.twig', array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'date_format' => $date_format,
    ));
  }

  /**
   * Creates a form to edit a QuoteVersion Summary.
   *
   * @param QuoteVersion $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createSummaryEditForm(QuoteVersion $entity)
  {
    $locale = $this->container->getParameter('locale');
    $form = $this->createForm(new QuoteSummaryType($locale), $entity, array(
      'action' => $this->generateUrl('quote_summary_update', array('id' => $entity->getId())),
      'method' => 'POST',
      'attr'   => array(
        'id'    => 'form-summary-edit-form',
        'class' => 'form-summary-edit-form',
      )
    ));

    $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('quote.actions.update')));

    return $form;
  }

  /**
   * Updates an existing QuoteVersion Summary.
   *
   */
  public function updateSummaryAction(Request $request, $id)
  {
    $date_format = $this->container->getParameter('date_format');
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find QuoteVersion entity while updating quote summary data.');
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

    return $this->render('QuoteBundle:QuoteVersion:editSummary.html.twig', array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
      'template' => $template,
      'date_format' => $date_format,
    ));
  }


}


