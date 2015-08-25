<?php

namespace TUI\Toolkit\QuoteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\QuoteBundle\Entity\QuoteVersion;
use TUI\Toolkit\QuoteBundle\Form\QuoteChangeRequestType;
use TUI\Toolkit\PermissionBundle\Entity\Permission;
use TUI\Toolkit\PermissionBundle\Controller\PermissionService;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Action\RowAction;
use Doctrine\ORM\Query\ResultSetMapping;


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
        $items = array();
        $content = $entity[0]->getContent();
        foreach ($content as $tab) {
            foreach ($tab as $key => $block) {
                $object = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);
                if ($object != null) {
                    $items[$block] = $object;
                }
            }
        }

        // get the content block that is the header block
        $header = $entity[0]->getHeaderBlock();
        if ($header != NULL) {
            $headerBlock = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($header);
        } else {
            $headerBlock = NULL;
        }

        // send warning messages
        $warningMsg = array();
        if ($entity[0]->getExpiryDate() < date($date_format)) {
            $warningMsg[] = "This quote has expired. Please contact $entity>getQuoteReference()->getSalesAgent()->getFirstName()   $entity>getQuoteReference()->getSalesAgent()->getLasttName()  at $entity>getQuoteReference()->getSalesAgent()->getEmail()";
        }


        return $this->render('QuoteBundle:QuoteSite:siteShow.html.twig', array(
            'entity' => $entity[0],
            'locale' => $locale,
            'items' => $items,
            'warning' => $warningMsg,
            'header' => $headerBlock,
            'editable' => $editable,
        ));
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

        $changeForm->add('submit', 'submit', array('label' => 'Submit Change Request'));

        return $changeForm;

    }

    public function newRequestAction($id)
    {
        $changeForm = $this->createChangeRequestFormAction($id);
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

        return $this->render('QuoteBundle:QuoteSite:changeRequest.html.twig', array(
            'change_request_form' => $changeForm->createView(),
            'entity' => $entity,
        ));
    }

    public function requestChangeAction(Request $request, $id)
    {

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
        $brand = $em->getRepository('BrandBundle:Brand')->findAll();
        $brand = $brand[0];

        $message = \Swift_Message::newInstance()
            ->setSubject('Quote Change Request For ' . $tourName)
            ->setFrom('ChangeRequest@toolkit.com')
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
                    )
                ), 'text/html');

        $this->get('mailer')->send($message);

        $this->get('session')->getFlashBag()->add('notice', 'You have requested a change to' . $tourName);

        return $this->redirect($this->generateUrl('quote_site_action_show', array('id' => $id)));


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
        $secondaryAgent = "";
        $toArray = array();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->findById($id);
        $entity = $entity[0];
        if ($entity->getConverted() == false) {
            $entity->setConverted(true);
        }
        $quoteConverted = $entity->getQuoteReference()->getConverted();
        if ($quoteConverted == false) {
            $entity->getQuoteReference()->setConverted(true);
        }
        $departure = $entity->getDepartureDate();
        $tourName = $entity->getName();
        $salesAgent = $entity->getQuoteReference()->getSalesAgent();
        $agentEmail = $salesAgent->getEmail();
        $toArray[] = $agentEmail;
        if ($entity->getQuoteReference()->getSecondaryContact()) {
            $secondaryAgent = $entity->getQuoteReference()->getSecondaryContact()->getEmail();
            $toArray[] = $secondaryAgent;
        }

        $brand = $em->getRepository('BrandBundle:Brand')->findAll();
        $brand = $brand[0];
        $brandName = $brand->getName();


        $message = \Swift_Message::newInstance()
            ->setSubject('Quote' . $tourName . 'has been accepted!')
            ->setFrom($brandName . '@Toolkit.com')
            ->setTo($toArray)
            ->setBody(
                $this->renderView(
                    'QuoteBundle:Emails:acceptQuote.html.twig',
                    array(
                        'brand' => $brand,
                        'entity' => $entity,
                        'departure' => $departure,
                        'tour_name' => $tourName,
                    )
                ), 'text/html');

        $em->persist($entity);
        $em->flush();
        $this->get('mailer')->send($message);

        $this->get('session')->getFlashBag()->add('notice', 'Quote ' . $tourName . 'has been accepted.');

        return $this->redirect($this->generateUrl('quote_site_action_show', array('id' => $id)));

    }

}
