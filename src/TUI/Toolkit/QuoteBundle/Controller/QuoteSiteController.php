<?php

namespace TUI\Toolkit\QuoteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\QuoteBundle\Entity\QuoteVersion;
use TUI\Toolkit\QuoteBundle\Form\QuoteVersionType;
use TUI\Toolkit\PermissionBundle\Entity\Permission;
use TUI\Toolkit\PermissionBundle\Controller\PermissionService;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Action\RowAction;
use Doctrine\ORM\Query\ResultSetMapping;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use DeepCopy\Filter\SetNullFilter;
use DeepCopy\Matcher\PropertyNameMatcher;
use DeepCopy\Filter\ReplaceFilter;
use DeepCopy\Filter\KeepFilter;
use DeepCopy\Matcher\PropertyMatcher;
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
    $editable = false;
    // TODO if user is allowed to edit then set $editable to true
    // if organizer or if brand or higher (check permission table for organizer)

    $em = $this->getDoctrine()->getManager();

    $locale = $this->container->getParameter('locale');
    $date_format = $this->container->getParameter('date_format');

    // Get all Quote versions referencing Parent Quote object
    $entity = $em->getRepository('QuoteBundle:QuoteVersion')->findById($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find QuoteVersion entity.');
    }


    // get the content blocks to send to twig
    $items=array();
    $content = $entity[0]->getContent();
    foreach($content as $tab){
      foreach($tab as $key=>$block){
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

    // send warning messages
    $warningMsg = array();
    if($entity[0]->getExpiryDate() < date($date_format)){
      $warningMsg[] = "This quote has expired. Please contact $entity>getQuoteReference()->getSalesAgent()->getFirstName()   $entity>getQuoteReference()->getSalesAgent()->getLasttName()  at $entity>getQuoteReference()->getSalesAgent()->getEmail()";
    }



    return $this->render('QuoteBundle:QuoteSite:siteShow.html.twig', array(
      'entity'      => $entity[0],
      'locale'      => $locale,
      'items'       => $items,
      'warning'     => $warningMsg,
      'header'      => $headerBlock,
      'editable'  =>  $editable,
    ));
  }





  public function sitePDFAction($id, $quoteNumber = null)
  {
    $editable = false;
    // TODO if user is allowed to edit then set $editable to true
    // if organizer or if brand or higher (check permission table for organizer)

    $em = $this->getDoctrine()->getManager();

    $locale = $this->container->getParameter('locale');
    $date_format = $this->container->getParameter('date_format');

    // Get all Quote versions referencing Parent Quote object
    $entity = $em->getRepository('QuoteBundle:QuoteVersion')->findById($id);
    if (!$entity) {
      throw $this->createNotFoundException('Unable to find QuoteVersion entity.');
    }


    // get the content blocks to send to twig
    $items=array();
    $content = $entity[0]->getContent();
    foreach($content as $tab){
      foreach($tab as $key=>$block){
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

    // send warning messages
    $warningMsg = array();
    if($entity[0]->getExpiryDate() < date($date_format)){
      $warningMsg[] = "This quote has expired. Please contact $entity>getQuoteReference()->getSalesAgent()->getFirstName()   $entity>getQuoteReference()->getSalesAgent()->getLasttName()  at $entity>getQuoteReference()->getSalesAgent()->getEmail()";
    }

    $request = $this->getRequest();
    $path = $request->getScheme() . '://' . $request->getHttpHost();

    $data = array(
      'entity' => $entity[0],
      'locale' => $locale,
      'items' => $items,
      'header' => $headerBlock,
      'editable' =>  $editable,
      'path' => $path
    );

    $html = $this->renderView( 'QuoteBundle:QuoteSite:sitePDF.html.twig', $data );

    $dompdf = new \DOMPDF();
    $dompdf->set_base_path($path . '/');
    $dompdf->load_html($html);
    $dompdf->render();
    
    return new Response($dompdf->output(), 200, array(
        'Content-Type' => 'application/pdf'
    ));

    /*
    return $this->render('QuoteBundle:QuoteSite:sitePDF.html.twig', array(
      'entity'      => $entity[0],
      'locale'      => $locale,
      'items'       => $items,
      'warning'     => $warningMsg,
      'header'      => $headerBlock,
      'editable'    => $editable,
      'path'        => $path,
    ));
    */
  }

}
