<?php

namespace TUI\Toolkit\TourBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use TUI\Toolkit\TourBundle\Entity\PaymentTask;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\QuoteBundle\Entity\QuoteVersion;
use TUI\Toolkit\TourBundle\Form\ContactOrganizerType;
use TUI\Toolkit\TourBundle\Form\TourPassengerType;
use TUI\Toolkit\TourBundle\Form\TourSetupType;
use TUI\Toolkit\UserBundle\Controller\UserController;
use TUI\Toolkit\TourBundle\Form\TourType;
use TUI\Toolkit\PermissionBundle\Entity\Permission;
use TUI\Toolkit\PermissionBundle\Controller\PermissionService;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;
use APY\DataGridBundle\Grid\Action\RowAction;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Form\FormError;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use DeepCopy\Filter\SetNullFilter;
use DeepCopy\Matcher\PropertyNameMatcher;
use DeepCopy\Filter\ReplaceFilter;
use DeepCopy\Filter\KeepFilter;
use DeepCopy\Matcher\PropertyMatcher;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Tour controller.
 *
 */
class TourController extends Controller
{

    /**
     * Lists all Tour entities.
     *
     */
    public function indexAction(Request $request)
    {
        // hide columns from the screen display
        $hidden = array(
            'secondaryContact.firstName',
            'secondaryContact.lastName',
            'secondaryContact.email',
            'quoteReference.id',
            'institution.city',
            'institution.name',
            'deleted',
            'locked',
            'organizer.firstName',
            'organizer.lastName',
            'organizer.email',
            'salesAgent_full',
            'salesAgent.firstName',
            'salesAgent.lastName',
            'salesAgent.email',
            'destination',
            'created',
            'tourReference',
            'id',
            'duration',
            'displayName',
            'tripStatus.name',
            'expiryDate',
            'transportType.name',
            'boardBasis.name',
            'freePlaces',
            'payingPlaces',
            'departureDate',
            'returnDate',
            'pricePerson',
            'pricePersonPublic',
            'currency.name',
            'emergencyDate',
            'passportDate',
            'medicalDate',
            'dietaryDate',
            'cashPayment',
            'cashPaymentDescription',
            'bankTransferPayment',
            'bankTransferPaymentDescription',
            'onlinePayment',
            'onlinePaymentDescription',
            'otherPayment',
            'otherPaymentDescription',
        );

        // Creates simple grid based on your entity (ORM)
        $source = new Entity('TourBundle:Tour');

        //add WHERE clause
        $tableAlias = $source->getTableAlias();

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        // Attach the source to the grid
        $grid->setSource($source);
        $grid->setId('tourgrid');
        // set grid filter persistance so filters are remebered for whole session
        $grid->setPersistence(true);
        $grid->hideColumns($hidden);

        // Add action column
        $editAction = new RowAction('Edit', 'manage_tour_edit');
        $editAction->manipulateRender(
            function ($action, $row) { // business rule is only admins can edit locked tours
                if ($row->getField('locked') == true) {
                    if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                        return null;
                    }
                }
                return $action;
            }
        );
        $grid->addRowAction($editAction);
        $showAction = new RowAction('Dashboard', 'manage_tour_show');
        $grid->addRowAction($showAction);
        $previewAction = new RowAction('Preview', 'tour_site_action_show');
        $grid->addRowAction($previewAction);
        $deleteAction = new RowAction('Delete', 'manage_tour_quick_delete');
        $deleteAction->setRole('ROLE_ADMIN');
        $deleteAction->setConfirm(true);
        $grid->addRowAction($deleteAction);
//        $lockAction = new RowAction('Lock', 'manage_tour_lock_nonajax');
//        $lockAction->manipulateRender(
//            function ($action, $row) {
//                if ($row->getField('locked') == true) {
//                    $action->setTitle('Unlock');
//                }
//                return $action;
//            }
//        );
//        // Lock actions are only available to admins
//        $lockAction->setRole('ROLE_ADMIN');
//        $grid->addRowAction($lockAction);

        // Change Row Color if locked
        $source->manipulateRow(
            function($row){
                if ($row->getField('locked') ==true){
                    $row->setColor('#ddd');
                    $row->setClass('locked');
                }
                return $row;
            }
        );
        $emailAction = new RowAction('Email', 'manage_tour_notify_organizers_form');
        $emailAction->setRole('ROLE_BRAND');
        $grid->addRowAction($emailAction);

        // add business admin last name filter
        $column = $grid->getColumn('salesAgent.lastName');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('tour.grid.filter.title.ba_lname'));
        $column->setOperatorsVisible(false);

        // add other business admin last name filter
        $column = $grid->getColumn('secondaryContact.lastName');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('tour.grid.filter.title.sc_lname'));
        $column->setOperatorsVisible(false);

        // add organizer last name filter
        $column = $grid->getColumn('organizer.lastName');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('tour.grid.filter.title.o_lname'));
        $column->setOperatorsVisible(false);

        // Set the default order of the grid
        $grid->setDefaultOrder('created', 'DESC');

        // Set the selector of the number of items per page
        $grid->setLimits(array(10, 25, 50, 100));

        //set no data message
        $grid->setNoDataMessage($this->get('translator')->trans('tour.grid.no_result'));

        //set default filter value
        $match_route = $this->generateUrl('manage_tour');
        $referer = $request->headers->get('referer');
        if (strpos($referer, $match_route) === false ) { // only set default filter if referer is not itself, ie reset button
          $usr = $this->get('security.context')->getToken()->getUser();
          $lastName = $usr->getLastName();
          $filters = array(
            'salesAgent.lastName' => array(
              'operator' => 'like',
              'from' => $lastName
            )
          );
          $grid->setDefaultFilters($filters);
        }

        // Export of the grid
        $grid->addExport(new CSVExport($this->get('translator')->trans('tour.grid.export'), "activeTours", array('delimiter' => ','), "UTF-8", "ROLE_BRAND"));


        // Manage the grid redirection, exports and the response of the controller
        return $grid->getGridResponse('TourBundle:Tour:index.html.twig');
    }



    /**
     * Lists all Deleted Tours
     *
     */
    public function deletedAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');

        // hide columns from the screen display
        $hidden = array(
          'secondaryContact.firstName',
          'secondaryContact.lastName',
          'secondaryContact.email',
          'quoteReference.id',
          'institution.name',
          'deleted',
          'locked',
          'organizer.firstName',
          'organizer.lastName',
          'organizer.email',
          'salesAgent_full',
          'salesAgent.firstName',
          'salesAgent.lastName',
          'salesAgent.email',
          'destination',
          'created',
          'tourReference',
          'id',
          'duration',
          'displayName',
          'tripStatus.name',
          'expiryDate',
          'transportType.name',
          'boardBasis.name',
          'freePlaces',
          'payingPlaces',
          'departureDate',
          'returnDate',
          'pricePerson',
          'pricePersonPublic',
          'currency.name',
          'emergencyDate',
          'passportDate',
          'medicalDate',
          'dietaryDate',
          'cashPayment',
          'cashPaymentDescription',
          'bankTransferPayment',
          'bankTransferPaymentDescription',
          'onlinePayment',
          'onlinePaymentDescription',
          'otherPayment',
          'otherPaymentDescription',
        );

        // Creates simple grid based on your entity (ORM)
        $source = new Entity('TourBundle:Tour');

        //add WHERE clause
        $tableAlias = $source->getTableAlias();

        $source->manipulateQuery(
            function ($query) use ($tableAlias) {
                $query
                    //->andWhere($tableAlias . '.ts IS NULL')
                    ->andWhere($tableAlias . ".deleted IS NOT NULL");
                $dql = $query->getDql();
                $foo = '';
            }
        );

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        // Attach the source to the grid
        $grid->setSource($source);
        $grid->setId('tourgriddeleted');
        // set grid filter persistance so filters are remebered for whole session
        $grid->setPersistence(true);
        $grid->hideColumns($hidden);

        // Add action column
        $restoreAction = new RowAction('Restore', 'manage_tour_restore');
        $grid->addRowAction($restoreAction);

        // add business admin last name filter
        $column = $grid->getColumn('salesAgent.lastName');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('tour.grid.filter.title.ba_lname'));
        $column->setOperatorsVisible(false);

        // add other business admin last name filter
        $column = $grid->getColumn('secondaryContact.lastName');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('tour.grid.filter.title.sc_lname'));
        $column->setOperatorsVisible(false);

        // add organizer last name filter
        $column = $grid->getColumn('organizer.lastName');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('tour.grid.filter.title.o_lname'));
        $column->setOperatorsVisible(false);

        // Set the default order of the grid
        $grid->setDefaultOrder('created', 'DESC');

        // Set the selector of the number of items per page
        $grid->setLimits(array(10, 25, 50, 100));

        //set no data message
        $grid->setNoDataMessage($this->get('translator')->trans('tour.grid.no_result-deleted'));

        //set default filter value
        $match_route = $this->generateUrl('manage_tour_deleted');
        $referer = $request->headers->get('referer');
        if (strpos($referer, $match_route) === false ) { // only set default filter if referer is not itself, ie reset button

          $usr = $this->get('security.context')->getToken()->getUser();
          $lastName = $usr->getLastName();
          $filters = array(
            'salesAgent.lastName' => array(
              'operator' => 'like',
              'from' => $lastName
            )
          );
          $grid->setDefaultFilters($filters);
        }

        // Export of the grid
        $grid->addExport(new CSVExport($this->get('translator')->trans('tour.grid.export_deleted'), "deletedTours", array('delimiter' => ','), "UTF-8", "ROLE_BRAND"));


        // Manage the grid redirection, exports and the response of the controller
        return $grid->getGridResponse('TourBundle:Tour:deleted.html.twig');
    }


    /**
     * Creates a new Tour entity.
     */
    public function createAction(Request $request, $id)
    {
        $entity = new Tour();

        $date_format = $this->container->getParameter('date_format');

        $form = $this->createCreateForm($entity, $id);
        $form->handleRequest($request);

        $form = $this->processTour($form, $entity, null, true);

        if ($form->isValid()) {
            return $this->redirect($this->generateUrl('tour_site_show', array(
                    'id' => $entity->getId(),
                    'quoteNumber' => $entity->getQuoteNumber()
                    )
                )
            );
        }

        $errors = $this->get("app.form.validation")->getNestedErrorMessages($form);

        return $this->render('TourBundle:Tour:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'date_format' => $date_format,
            'errors' => $errors,
        ));
    }

    /**
     * Creates a form to create a Tour entity.
     *
     * @param Tour $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Tour $entity, $id = null)
    {
        $locale = $this->container->getParameter('locale');
        $em = $this->getDoctrine()->getManager();
        $currency_code = $this->container->getParameter('currency');
        $currency = $em->getRepository('CurrencyBundle:Currency')->findByCode($currency_code);
        $currency = array_shift($currency);

        if ($id) {
            $quoteVersion = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

            // Check if the quoteVersion still exists.
            if (!$quoteVersion) {
                throw $this->createNotFoundException('Unable to find Quote Version while converting to tour.');
            }

            $quoteReference = $quoteVersion->getQuoteReference();
            $quote = $em->getRepository('QuoteBundle:Quote')->find($quoteReference);

            // Check if the quote still exists.
            if (!$quote) {
                throw $this->createNotFoundException('Unable to find Quote while converting to tour.');
            }

            // Check if quote is already converted.
            if($quote->getConverted() == TRUE ){
                throw $this->createNotFoundException($this->get('translator')->trans('quote.exception.convert'));
            }

            $siblings = $em->getRepository('QuoteBundle:QuoteVersion')->findBy(array('quoteReference' => $quoteReference));
            foreach($siblings as $sibling){
                if($sibling->getConverted() == TRUE){
                    throw $this->createNotFoundException($this->get('translator')->trans('quote.exception.convert_sibling'));
                }
            }

            // Get first trip status from doctrine.
            $tripStatus = $em->createQueryBuilder()
                ->select('e')
                ->from('TripStatusBundle:TripStatus', 'e')
                ->orderBy('e.id', 'ASC')
                ->where('e.visible = TRUE')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $entity->setTripStatus($tripStatus);
            $entity->setQuoteNumber($quoteVersion->getQuoteNumber());
            $entity->setQuoteReference($quote);
            $entity->setQuoteVersionReference($quoteVersion);
            $entity->setBoardBasis($quoteVersion->getBoardBasis());
            $entity->getCreated(new \DateTime());

            $quote_currency = $quoteVersion->getCurrency();
            if (!empty($quote_currency)) {
                $entity->setCurrency($quote_currency);
            }
            else {
                $entity->setCurrency($currency);
            }

            $entity->setDepartureDate($quoteVersion->getDepartureDate());
            $entity->setDestination($quote->getDestination());
            $entity->setDuration($quoteVersion->getDuration());
            $entity->setDisplayName($quoteVersion->getDisplayName());
            $entity->setExpiryDate($quoteVersion->getExpiryDate());
            $entity->setFreePlaces($quoteVersion->getFreePlaces());
            $entity->setInstitution($quote->getInstitution());
            $entity->setLocked(FALSE);
            $entity->setName($quote->getName() . ' - ' . $quoteVersion->getName());
            $entity->setOrganizer($quote->getOrganizer());
            $entity->setPayingPlaces($quoteVersion->getPayingPlaces());
            $entity->setPricePerson($quoteVersion->getPricePerson());
            $entity->setPricePersonPublic($quoteVersion->getPricePerson());
            $entity->setReturnDate($quoteVersion->getReturnDate());
            $entity->setSalesAgent($quote->getSalesAgent());
            $entity->setSecondaryContact($quote->getSecondaryContact());
            $entity->setTotalPrice(0);
            $entity->setTransportType($quoteVersion->getTransportType());
            $entity->setWelcomeMsg($quoteVersion->getWelcomeMsg());

            $headerBlock = $quoteVersion->getHeaderBlock();
            if($headerBlock !== NULL){
                $blockId = $headerBlock->getId();
            }
            if(isset($blockId)) {
                $headerBlock = $this->cloneHeaderBlock($blockId);
                $entity->setHeaderBlock($headerBlock);
            }
            $content = $this->cloneContentBlocks($quoteVersion->getContent());
            $entity->setContent($content);
        }
        else {
            $entity->setSalesAgent($this->get('security.token_storage')->getToken()->getUser());
            $entity->setCurrency($currency);
        }
        
        $entity->setRegistrations(0);
        $entity->setCashPayment(false);
        $entity->setBankTransferPayment(false);
        $entity->setOnlinePayment(false);
        $entity->setOtherPayment(false);

        $form = $this->createForm(new TourType($entity, $locale), $entity, array(
            'action' => $this->generateUrl('manage_tour_create', array('id' => $id)),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.create')));

        return $form;
    }

    /*
    * Helper function to convert quote to a tour.
    */
    public function convertQuoteAction(Request $request, $id)
    {
        $date_format = $this->container->getParameter('date_format');
        $entity = new Tour();
        $form = $this->createCreateForm($entity, $id);

        $errors = $this->get("app.form.validation")->getNestedErrorMessages($form);

        return $this->render('TourBundle:Tour:new.html.twig', array(
          'entity' => $entity,
          'form' => $form->createView(),
          'date_format' => $date_format,
          'errors' => $errors,
        ));
    }
  
  /**
     * Finds and displays a Tour entity.
     *
     * param $id tour id
     *
     */
    public function showAction($id)
    {
        $editable = FALSE;
        $permission = array();

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

        $locale = $this->container->getParameter('locale');

        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }

        $collection = $entity->getMedia()->toArray() ? $entity->getMedia()->toArray() : NULL;

        $payment_tasks = $entity->getPaymentTasks();
        $paymentSchedule = $this->getPaymentSchedule($id);

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

        //Get all brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }


        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TourBundle:Tour:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
            'locale' => $locale,
            'collection' => $collection,
            'payment_tasks' => $payment_tasks,
            'payment_schedule' => $paymentSchedule,
            'items' => $items,
            'tabs' => $tabs,
            'editable' => $editable,
            'brand' => $brand,
        ));
    }

    /**
     * Finds and displays a Tour entity's tabs.
     *
     * param $id Tour id
     *
     */
    public function showTabsAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $locale = $this->container->getParameter('locale');

        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
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


        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TourBundle:TourSite:siteShowTabOrder.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
            'locale' => $locale,
            'items' => $items,
            'tabs' => $tabs,
        ));
    }

    /**
     * Displays a form to edit an existing Tour entity.
     * @param $id id of the Tour object
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.' . $id);
        }

        if ($entity->getMedia() != null) {
            $collection = $entity->getMedia()->toArray();
            foreach ($collection as $image) {
                $imageIds[] = $image->getId();
            }

            if (isset($imageIds)) {
                $collectionIds = implode(',', $imageIds);
            } else {
                $collectionIds = '';
            }

            $entity->setMedia($collectionIds);
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($entity->getId());
        $date_format = $this->container->getParameter('date_format');
        $locale =  $this->container->getParameter('locale');

        return $this->render('TourBundle:Tour:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'collection_ids' => $collectionIds,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'date_format' => $date_format,
            'locale' => $locale,
        ));
    }


    /**
     * Creates a form to edit a Tour entity.
     *
     * @param Tour $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Tour $entity)
    {
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new TourType($entity, $locale), $entity, array(
            'action' => $this->generateUrl('manage_tour_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.update')));

        return $form;
    }

    /**
     * Edits an existing Tour entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        $oldOrganizerID = $entity->getOrganizer()->getID();
        $date_format = $this->container->getParameter('date_format');
        $collection = $entity->getMedia()->toArray();

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $editForm = $this->processTour($editForm, $entity, $oldOrganizerID);

        if ($editForm->isValid()) {
            if ($entity->getSetupComplete() == false and $entity->getIsComplete() == false) {

                return $this->redirect($this->generateUrl('tour_site_show', array(
                            'id' => $entity->getId(),
                            'quoteNumber' => $entity->getQuoteNumber()
                        )
                    )
                );

            } else {
                return $this->redirect($this->generateUrl('manage_tour'));
            }
        }

        $errors = $this->get("app.form.validation")->getNestedErrorMessages($editForm);

        return $this->render('TourBundle:Tour:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'date_format' => $date_format,
            'errors' => $errors,
        ));
    }

    /**
     * Helper function to validate and set fields on tour form.
     */
    protected function processTour(&$form, $entity, $oldOrganizerID = null, $converted = false) {
        $em = $this->getDoctrine()->getManager();

        if (!empty($oldOrganizerID)) {
            $oldOrganizer = $em->getRepository('TUIToolkitUserBundle:User')->find($oldOrganizerID);
        }
        else {
            $oldOrganizer = null;
        }

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }

        // Handling ajax request for organizer.
        $o_data = $form->getData()->getOrganizer();
        if (preg_match('/<+(.*?)>/', $o_data, $o_matches)) {
            $email = $o_matches[1];
            $entities = $em->getRepository('TUIToolkitUserBundle:User')
                ->findByEmail($email);
            if (NULL !== $entities) {
                $organizer = array_shift($entities);
                $form->getData()->setOrganizer($organizer);
            } else {
                $form['organizer']->addError(new FormError($this->get('translator')
                    ->trans('quote.exception.organizer')));
            }
        } else {
            $form['organizer']->addError(new FormError($this->get('translator')->trans('quote.exception.organizer')));
        }

        // Handling ajax request for SalesAgent same as we did with organizer.
        $a_data = $form->getData()->getSalesAgent();
        if (preg_match('/<+(.*?)>/', $a_data, $a_matches)) {
            $agentEmail = $a_matches[1];
            $agentEntities = $em->getRepository('TUIToolkitUserBundle:User')
                ->findByEmail($agentEmail);
            if (NULL !== $agentEntities and !empty($agentEntities)) {
                $salesAgent = array_shift($agentEntities);
                $form->getData()->setSalesAgent($salesAgent);
            } else {
                if (!empty($oldOrganizer)) {
                    $form->getData()->setSalesAgent($oldOrganizer);
                }
                $form['salesAgent']->addError(new FormError($this->get('translator')->trans('quote.exception.salesagent')));
            }
        } else {

            $form['salesAgent']->addError(new FormError($this->get('translator')->trans('quote.exception.salesagent')));
        }

        // Handling ajax request for SecondaryContact same as we did with organizer.
        $s_data = $form->getData()->getSecondaryContact();
        if ( $s_data != null) {
            if (preg_match('/<+(.*?)>/', $s_data, $s_matches)) {
                $secondEmail = $s_matches[1];
                $secondEntities = $em->getRepository('TUIToolkitUserBundle:User')
                    ->findByEmail($secondEmail);
                if (NULL !== $secondEntities) {
                    $secondAgent = array_shift($secondEntities);
                    $form->getData()
                        ->setSecondaryContact($secondAgent);
                }
            }else {
                $form['secondaryContact']->addError(new FormError($this->get('translator')->trans('quote.exception.secondaryagent')));
            }
        }

        // Handling the request for institution a little different than we did for the other 2.
        $institutionParts = explode(' - ', $form->getData()->getInstitution());
        if (count($institutionParts) == 2 ) {
            $institutionEntities = $em->getRepository('InstitutionBundle:Institution')->findBy(
                array('name' => $institutionParts[0], 'city' => $institutionParts[1])
            );
            if (null !== $institutionEntities) {
                $institution = array_shift($institutionEntities);
                $form->getData()->setInstitution($institution);
            }
        } else {
            $form['institution']->addError(new FormError($this->get('translator')->trans('quote.exception.institution')));
        }

        // Handling media.
        $medias = array();
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

        // Handling pricePersonPublic.
        if ($converted) {
            $form_price_person = $form->getData()->getPricePerson();
            $entity->setPricePersonPublic($form_price_person);
        }

        if ($form->isValid()) {

            if (NULL != $form->getData()->getMedia()) {
                $web_dir = $_SERVER['DOCUMENT_ROOT'];
                $exportsDir = $web_dir . "/static/exports/";

                if (!file_exists($exportsDir) && !is_dir($exportsDir)) {
                    mkdir($exportsDir, 0755);
                }

                $collection = $form->getData()->getMedia();

                $zip = new \ZipArchive();
                $fileName = $entity->getquoteNumber() . ".zip";
                $destination = "static/exports/" . $fileName;
                $zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                foreach ($collection as $c) {
                    $zip->addFromString($c->gethashedFilename(), file_get_contents($c->getfilepath() . "/" . $c->gethashedFilename()));
                    $zip->renameName($c->gethashedFilename(),$c->getFilename());
                }
                
                $zip->close();
                $em->flush();
            }

            // Sync payment tasks for instution and passengers if still Not Setup complete.
            if ($entity->getSetupComplete() == FALSE && $entity->getIsComplete() == FALSE) {
                // Step 1 purge existing passenger payment schedules
                $this->purgePassengerPaymentSchedule($entity->getPaymentTasksPassenger(), $entity->getId());
                $entity->setPaymentTasksPassenger(null);
            }

            $passengerPaymentTasksStorage = array();

            // Loop through payment tasks and set type to institution.
            foreach($form->getData()->getPaymentTasks() as $paymentTask){
                $paymentTask->setType('institution');
                if ($entity->getSetupComplete() == FALSE && $entity->getIsComplete() == FALSE) {
                    // Tour has not been setup by Organizer so sync Passenger payments with Institution Payments as Default values
                    // Step 2 copy values over for each Institution payment task.
                    $newPaymentTaskPassenger = $this->syncPassengerPaymentDefaults($paymentTask);
                    $passengerPaymentTasksStorage[] = $newPaymentTaskPassenger;
                }
            }

            // Sync payment tasks for instution and passengers if still Not Setup complete.
            if ($entity->getSetupComplete() == FALSE && $entity->getIsComplete() == FALSE) {
                // Step 1 purge existing passenger payment schedules
                if(!empty($passengerPaymentTasksStorage)){$entity->setPaymentTasksPassenger($passengerPaymentTasksStorage);}
            }

            $em->persist($entity);
            $em->flush();

            if ($converted) {
                // Handling quote and quote version.
                $quoteNumber = $entity->getQuoteNumber();
                if($quoteNumber) {
                    $quoteVersion = $em->getRepository('QuoteBundle:QuoteVersion')->findOneBy(array('quoteNumber' => $quoteNumber));

                    if (!$quoteVersion) {
                        $form['quoteNumber']->addError(new FormError($this->get('translator')->trans('quote.exception.prompt_error')));
                    }
                    $quoteReference = $quoteVersion->getQuoteReference();
                    $quote = $em->getRepository('QuoteBundle:Quote')->find($quoteReference);

                    if (!$quote) {
                        $form['quoteNumber']->addError(new FormError($this->get('translator')->trans('quote.exception.prompt_error')));
                    }

                    // Update quote and quoteVersion.
                    $quote->setConverted(TRUE);
                    $quoteVersion->setConverted(TRUE);

                    $em->persist($quote);
                    $em->persist($quoteVersion);
                }
            }

            // if new organizer, then check for / add passenger record and permissions
            // stub out passenger record and parent permission for passenger for organizer
            $organizer = $entity->getOrganizer();
            if(empty($oldOrganizer) || $organizer->getEmail() != $oldOrganizer->getEmail()) {
                $this->changeOrganizer($organizer, $entity, $oldOrganizer);
            }

            $this->get("permission.set_permission")->setPermission($entity->getId(), 'tour', $entity->getOrganizer(), 'organizer');
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.save') . $entity->getName());
        }

        return $form;
    }

    /**
     * If a new Organizer if set to Tour, add Passenger and Permission Records
     * Called from Tour Edit Forms create action
     */

    public function changeOrganizer($organizer, $tour, $oldOrganizer){
        $em = $this->getDoctrine()->getManager();
        $exists = FALSE;
        $existingPermissions = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $organizer, 'class' => 'passenger'));
                foreach($existingPermissions as $existingPermission){
                    $existingPassengers = $em->getRepository('PermissionBundle:Permission')->find($existingPermission->getObject());
                    foreach($existingPassengers as $existingPassenger){
                        if (($existingPassenger->getLName() == $organizer->getLastName())
                            && $existingPassenger->getFName() == $organizer->getFirstName()
                            && ($existingPassenger->getTour() == $tour->getId())) {
                            // a passenger exists for this tour already for this user as best we can tell
                            $exists = TRUE;
                            $existingPassenger->setSelf(TRUE);
                            $em->persist($existingPassenger);
                            $em->flush();
                        }
                    }
                }

                if ($exists == FALSE) {
                    // no pax or perm record for this user and this tour so create both

                    $newPassenger = new Passenger();
                    //$newPassenger->setDateOfBirth(); // we dont know what this is here
                    $newPassenger->setFName($organizer->getFirstName());
                    //$newPassenger->setGender(); // we dont know what this is here
                    $newPassenger->setLName($organizer->getLastName());
                    $newPassenger->setStatus("waitlist");
                    $newPassenger->setSignUpDate(new \DateTime());
                    $newPassenger->setTourReference($tour);
                    $newPassenger->setFree(FALSE);
                    $newPassenger->setSelf(TRUE);
                    $em->persist($newPassenger);
                    $em->flush($newPassenger);

                    $paxpermission = new Permission();
                    $paxpermission->setClass('passenger');
                    $paxpermission->setObject($newPassenger->getId());
                    $paxpermission->setGrants('parent');
                    $paxpermission->setUser($organizer);
                    $em->persist($paxpermission);
                    $em->flush($paxpermission);
                }
//        // always create an Organizer permission for the tour
//        $opermission = new Permission();
//        $opermission->setClass('tour');
//        $opermission->setObject($tour->getId());
//        $opermission->setGrants('organizer');
//        $opermission->setUser($organizer);
//        $em->persist($opermission);
//        $em->flush($opermission);
//
//        // remove the old organizer permission
//        $oldPermission = $em->getRepository('PermissionBundle:Permission')->findBy(array('user' => $oldOrganizer, 'class' => 'tour', 'grants' => 'organizer', 'object' => $tour->getId()));
//        if(!empty($oldPermission[0])) {
//            $em->remove($oldPermission[0]);
//            $em->flush($oldPermission[0]);
//        }


    }

    /**
     * Purge a Tour's passenger payment schedule for institutions to a Passenger
     *
     * @param paymentTasksPassenger - the Tours existing passenger payment task array collection
     */

    public function purgePassengerPaymentSchedule($paymentTasksPassenger, $entity) {
        if (!empty($paymentTasksPassenger) ){
            $em = $this->getDoctrine()->getManager();
            $tasks=$paymentTasksPassenger->toArray();
            foreach($tasks as $task) {
                $em->remove($task);
            }
            $em->flush();
        }


    }


    /**
     * Syncronize a Tour's payment schedule for institutions to a Passenger
     *
     * @param paymentTask - the payment object to look for and create/update
     * return new paymentTaskPassenger object
     */

    public function syncPassengerPaymentDefaults($paymentTask) {
        $em = $this->getDoctrine()->getManager();
        $newPassengerPaymentTask = clone $paymentTask;
        $newPassengerPaymentTask->setType('passenger');
        $newPassengerPaymentTask->setId(null);
        $em->persist($newPassengerPaymentTask);
        $em->flush($newPassengerPaymentTask);
        return $newPassengerPaymentTask;

    }


    /**
     * Deletes a Tour entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TourBundle:Tour')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tour entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.delete') . $entity->getName());

        }

        return $this->redirect($this->generateUrl('manage_tour'));
    }

    /**
     * Creates a form to delete a Tour entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_tour_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array(
                'label' => $this->get('translator')->trans('tour.actions.delete'),
                'attr' => array(
                    'class' => 'delete-btn'
                )
            ))
            ->getForm();
    }

    /**
     * quickly Deletes Tour entity.
     *
     */
    public function quickdeleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        // dont forget to disable softdelete filter so doctrine can *find* the deleted entity

        $tour = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$tour) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }
        $em->remove($tour);
        $em->flush();
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.delete') . $tour->getName());

        return $this->redirect($this->generateUrl('manage_tour'));
    }

    /**
     * hard Deletes Tour entity.
     *
     */
    public function harddeleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        // dont forget to disable softdelete filter so doctrine can *find* the deleted entity
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');

        $tour = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$tour) {
            throw $this->createNotFoundException('Unable to find Tour entity in order to delete it using ajax.');
        }
        $em->remove($tour);
        $em->flush();
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('user.flash.delete'). ' ' . $tour->getName());

        return $this->redirect($this->generateUrl('manage_tour'));
    }


    /**
     * Creates a form to Restore a deleted Tour entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createRestoreForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_tour_restore', array('id' => $id)))
            ->setMethod('POST')
            ->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.restore')))
            ->getForm();
    }


    /**
     * Restores a Deleted Tour entity.
     *
     */
    public function restoreAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        // dont forget to disable softdelete filter so doctrine can *find* the deleted entity
        $filters = $em->getFilters();
        $filters->disable('softdeleteable');

        $tour = $em->getRepository('TourBundle:Tour')->find($id);

        if (!$tour) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }
        $tour->setDeleted(NULL);
        $em->persist($tour);
        $em->flush();
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.restore') . $tour->getName());

        return $this->redirect($this->generateUrl('manage_tour'));
    }


    /**
     * Toggles lock status on Tour entity.
     *
     */
    public function lockAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $tour = $em->getRepository('TourBundle:Tour')->find($id);

        if (!$tour) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }

        if ($tour->getLocked() == false) {
            $status = true;
        } else {
            $status = false;
        }
        $tour->setLocked($status);
        $em->persist($tour);
        $em->flush();
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.lock'));

        return new Response(json_encode((array)$tour));

    }

    /**
     * Toggles lock status Tour entity without ajax.
     *
     */
    public function lockNonajaxAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $tour = $em->getRepository('TourBundle:Tour')->find($id);

        if (!$tour) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }

        if ($tour->getLocked() == false) {
            $status = true;
        } else {
            $status = false;
        }
        $tour->setLocked($status);
        $em->persist($tour);
        $em->flush();
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.lock'));

        return $this->redirect($this->generateUrl('manage_tour'));

    }

    /**
     * Clone Content Blocks for a Tour
     *
     */

    public function cloneContentBlocks($content = array())
    {
        //Get logger service for errors
        $logger = $this->get('logger');

        $newContentArray = array();
        if (!empty($content) && $content != NULL) {
            foreach ($content as $tab => $blocks) {
                foreach ($blocks[1] as $block) { // block should be an ID number
                    $em = $this->getDoctrine()->getManager();

                    $originalBlock = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);

                    if (!$originalBlock) {
//                        $items[$blocks] = null;
                        $logger->error('Content Block '.$originalBlock. ' cannot be found');
//                        throw $this->createNotFoundException('Unable to find Content entity while cloning quote content.');
                    } else {

                        $newBlock = clone $originalBlock;
                        $newBlock->setId(null);
                        $em->persist($newBlock);
                        $em->flush($newBlock);

                        $newContentArray[$tab][0] = $blocks[0];
                        $newContentArray[$tab][1][] = $newBlock->getID();
                    }
                }
            }
        }

        return $newContentArray;
    }

    /**
     * Clone Header Block for a Tour
     *
     */

    public function cloneHeaderBlock($block)
    {
        $result = NULL;
        if (!empty($block) && $block != NULL) {
            $em = $this->getDoctrine()->getManager();

            $originalBlock = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);

            if (!$originalBlock) {
                throw $this->createNotFoundException('Unable to find Header Content entitywhile cloning from quote to tour.');
            }

            $newBlock = clone $originalBlock;
            $newBlock->setId(null);
            $em->persist($newBlock);
            $em->flush($newBlock);
            $result = $newBlock;
        }

        return $result;
    }


    /**
     * Export Tour Promotional Assets
     *
     */

    public function exportTourAssetsAction(Request $request, $id, $fileName)
    {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:Tour')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }
        $web_dir = $_SERVER['DOCUMENT_ROOT'];

        $response = new Response();
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $response->headers->set('Content-Length', filesize("static/exports/" . $fileName));
        $response->setContent(file_get_contents($web_dir . '/static/exports/' . $fileName));

        return $response;


    }

    public function getTourTasksDashboardAction($id)
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

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }
        $editForm = $this->createEditForm($entity);
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $passengerData = $this->get("passenger.actions")->getTourPassengersData($id);

        $payment_tasks = $entity->getPaymentTasks();

        return $this->render('TourBundle:Tour:tasks-dashboard.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'date_format' => $date_format,
            'locale' => $locale,
            'brand' => $brand,
            'passengerData' =>$passengerData,
            'payment_tasks' => $payment_tasks
        ));

    }

    public function getPassengerTasksMiniCardAction($passenger) {

        $em = $this->getDoctrine()->getManager();

        $locale = $this->container->getParameter('locale');

        $completedTasks = $this->get("passenger.actions")->getPassengerCompletedTasks($passenger->getId());

        $passenger->completedTasks = $completedTasks;

        $possibleTasks = $this->get("passenger.actions")->getPossibleTourTasks($passenger->getTourReference()->getId());

        $passenger->possibleTasks = $possibleTasks;

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('TourBundle:Tour:tasks-dashboard-minicards.html.twig', array(
            'locale' => $locale,
            'pax' => $passenger,
            'brand' => $brand
        ));
    }

    /**
     * @param $id
     * @return Response
     */

    public function getTourCompletedAndSetupAction($id)
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

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        $editForm = $this->createEditForm($entity);
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
          $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
          $brand = $default_brand;
        }

        $passengerData = $this->get("passenger.actions")->getTourPassengersData($id);

        return $this->render('TourBundle:Tour:completedandsetup.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'date_format' => $date_format,
            'locale' => $locale,
            'brand' => $brand,
            'passengerData' =>$passengerData
        ));

    }

    /**
     * @param $id
     * @return Response
     */

    public function getTourNotCompletedAndSetupAction($id)
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

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:Tour')->find($id);

        $date_format = $this->container->getParameter('date_format');

        $locale = $this->container->getParameter('locale');

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('TourBundle:Tour:notCompletedAndSetup.html.twig', array(
            'entity' => $entity,
            'date_format' => $date_format,
            'locale' => $locale,
            'brand' => $brand,
        ));

    }

    /**
     * @param $id
     * @return Response
     */

    public function getTourNotSetupAction($id)
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

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');
        $setupForm = $this->createTourSetupForm($entity);
        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('TourBundle:Tour:notSetup.html.twig', array(
            'entity' => $entity,
            'setup_form' => $setupForm->createView(),
            'date_format' => $date_format,
            'locale' => $locale,
            'brand' => $brand,
        ));

    }


    public function getEditPaymentsAction($id)
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

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');
        $setupForm = $this->createTourSetupForm($entity);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $passenger_payment_tasks = $entity->getPaymentTasksPassenger();


        return $this->render('TourBundle:Tour:editPayments.html.twig', array(
            'entity' => $entity,
            'setup_form' => $setupForm->createView(),
            'date_format' => $date_format,
            'locale' => $locale,
            'brand' => $brand,
            'passenger_payment_tasks' => $passenger_payment_tasks,
        ));

    }

    /**
     * Creates a form to edit a Tour entity on first setup.
     *
     * @param Tour $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */

    public
    function createTourSetupForm(Tour $entity)
    {
        $locale = $this->container->getParameter('locale');
        $setupForm = $this->createForm(new TourSetupType($entity, $locale), $entity, array(
            'action' => $this->generateUrl('manage_tour_setup', array('id' => $entity->getId())),
            'method' => 'POST',
            'attr'  => array(
                'id' => 'ajax_tour_setup_form'
            )
        ));

        $setupForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.save')));

        return $setupForm;
    }


    public function TourSetupAction(Request $request, $id)
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

        $setupForm = $this->createTourSetupForm($entity);
        $setupForm->handleRequest($request);

        $payments = $setupForm->getData()->getPaymentTasksPassenger();

        foreach ($payments as $payment) {
            $payment->setType("passenger");
        }

        if ($setupForm->isValid()) {
            $entity->setSetupComplete(true);
            $em->flush();
            $permission = $this->get("permission.set_permission")->setPermission($entity->getId(), 'tour', $entity->getOrganizer(), 'organizer');
            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.save') . $entity->getName());

            $serializer = $this->container->get('jms_serializer');
            $serialized = $serializer->serialize($entity, 'json');
            $response = new Response($serialized);
            $response->headers->set('Content-Type', 'application/json');
            $response->setStatusCode('200');
            return $response;

//            // smarter redirect
//            if(isset($_SESSION["tour_settings_referer"]) && $_SESSION["tour_settings_referer"] != ''){
//                $referer = $_SESSION["tour_settings_referer"];
//                unset($_SESSION["tour_settings_referer"]);
//                return $this->redirect($referer);
//            } else {
//                return $this->redirect($referer);
//            }
        }

        $errors = $this->get("app.form.validation")->getNestedErrorMessages($setupForm);

        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');
        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;


    }

    public function setupCompleteAction($id, $quoteNumber)
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

        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }
        $entity->setIsComplete(true);

        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('tour_site_show', array('id' => $id, "quoteNumber" => $quoteNumber)));

    }


    /**
     * Creates a form to make a change request to a QuoteVersion entity.
     *
     * @param QuoteVersion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */

    public function createNotifyOrganizerFormAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        $active_organizer = $entity->getOrganizer()->isEnabled();
        $locale = $this->container->getParameter('locale');
        $notifyForm = $this->createForm(new ContactOrganizerType($locale, $active_organizer), array(), array(
            'action' => $this->generateUrl('manage_tour_notify_organizers', array('id' => $id)),
            'method' => 'POST',
        ));

        $notifyForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.send')));

        return $notifyForm;

    }

    public function newNotifyOrganizerAction($id)
    {
        $notifyForm = $this->createNotifyOrganizerFormAction($id);
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        // Get some user info
        if ($entity->getOrganizer()) {
            $organizer = $entity->getOrganizer();

            if ($organizer->isEnabled() == false) {
                // Create token
                $tokenGenerator = $this->container->get('fos_user.util.token_generator');
                // Get some user info
                $organizer->setConfirmationToken($tokenGenerator->generateToken());
            }
        };

        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // Look for a configured brand.
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        return $this->render('TourBundle:Tour:contactorganizer.html.twig', array(
            'notify_form' => $notifyForm->createView(),
            'entity' => $entity,
            'locale' => $locale,
            'date_format' => $date_format,
            'organizer' => $organizer,
            'brand' => $brand,
        ));
    }

    public function organizerNotifyAction(Request $request, $id)
    {

        $locale = $this->container->getParameter('locale');
        $date_format = $this->container->getParameter('date_format');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);

        $notifyForm = $this->createNotifyOrganizerFormAction($id);
        $notifyForm->handleRequest($request);
        $additional = $notifyForm->get('message')->getData();

        $organizerEmail = $entity->getOrganizer()->getEmail();
        $BusinessPersonId = $entity->getSalesAgent()->getId();

        $agent = $em->getRepository('TUIToolkitUserBundle:User')->find($BusinessPersonId);

        //brand stuff
        $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

        // look for a configured brand
        if($brand_id = $this->container->getParameter('brand_id')){
            $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
        }

        if(!$brand) {
            $brand = $default_brand;
        }

        $departure = $entity->getDepartureDate();
        $tourName = $entity->getName();
        $subject = $this->get('translator')->trans('tour.email.setup.subject') . ' ' . $brand->getName() . ' tour';

        if ($entity->getOrganizer()->isEnabled() == true) {

            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->container->getParameter('user_system_email'))
                ->setTo($organizerEmail)
                ->setBody(
                    $this->renderView(
                        'TourBundle:Emails:organizermessage.html.twig',
                        array(
                            'brand' => $brand,
                            'entity' => $entity,
                            'departure' => $departure,
                            'tour_name' => $tourName,
                            'additional' => $additional,
                            'locale' => $locale,
                            'date_format' => $date_format,
                            'agent' => $agent,
                        )
                    ), 'text/html');
        } elseif ($entity->getOrganizer()->isEnabled() == false) {
            $user = $entity->getOrganizer();
            // Create token
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');

            //Get some user info
            $user->setConfirmationToken($tokenGenerator->generateToken());

            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->container->getParameter('user_system_email'))
                ->setTo($organizerEmail)
                ->setBody(
                    $this->renderView(
                        'TourBundle:Emails:organizersetupmessage.html.twig',
                        array(
                            'brand' => $brand,
                            'entity' => $entity,
                            'user' => $user,
                            'additional' => $additional,
                            'agent' => $agent,
                            'locale' => $locale,
                            'date_format' => $date_format,

                        )
                    ), 'text/html');
        }

        $em->persist($entity);
        $em->flush();
        $this->get('mailer')->send($message);
        $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('tour.flash.registration_notification') . " " . $organizerEmail);


    return $this->redirect($this->generateUrl('manage_tour'));

    }

    /*
     * getPaymentSchedule
     *
     * parameter int tourId
     * returns array of payment tasks and payments made plus status of each task and balances due.
     */

    public function getPaymentSchedule($tourId) {
        $totalPaid = 0;
        $items=array();
        $now = new \DateTime('now');
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);
        $paymentTasks = $tour->getPaymentTasks();
        $payments = $em->getRepository('PaymentBundle:BrandPayment')->findBy(array('tour' => $tourId));

        foreach($payments as $payment){
            $totalPaid = $totalPaid + $payment->getValue();
        }

        $balance = $totalPaid;
        foreach($paymentTasks as $paymentTask) {
            $taskDue = $paymentTask->getValue() * $tour->getPayingPlaces();
            $taskStatus = 'pending';
            $taskOverdueAmt = 0;
            $taskPaid = 0;
            if($balance >= $taskDue){
                $taskStatus = 'paid';
                $taskPaid = $taskDue;
                $balance = $balance - $taskDue;
                if($paymentTask->getPaidDate() == NULL){
                    //make sure to flag this as paid.
                    $paymentTask->setPaidDate($now);
                    $em->persist($paymentTask);
                    $em->flush($paymentTask);
                }
            }

            elseif($balance < $taskDue){
                $taskPaid = $balance;
                $balance = 0;
                if($paymentTask->getDueDate() < $now){
                    $taskStatus = "overdue";
                    $taskOverdueAmt = $taskDue - $taskPaid;
                }
            }
            $items[] = array('taskPaid' => $taskPaid, 'item' =>$paymentTask, 'taskStatus' => $taskStatus, 'taskOverdueAmt' => $taskOverdueAmt);

        }
        return $items;

    }
}