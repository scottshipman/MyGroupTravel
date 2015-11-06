<?php

namespace TUI\Toolkit\TourBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
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
            'version',
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
            'passengerDate',
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
          'version',
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
          'passengerDate',
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
        $grid->setId('tourgrid');
        $grid->hideColumns($hidden);

        // Add action column
        $restoreAction = new RowAction('Restore', 'manage_tour_restore');
        $grid->addRowAction($restoreAction);

        // add business admin last name filter
        $column = $grid->getColumn('salesAgent.lastName');
        $column->setFilterable(true);
        $column->setTitle($this->get('translator')->trans('tour.grid.filter.title.ba_lname'));
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
     *
     */
    public function createAction(Request $request)
    {
//        //Handling the autocomplete field for organizer.  We need to convert the string from organizer into the object.
        $entity = new Tour();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        //handling ajax request for organizer
        $o_data = $form->getData()->getOrganizer();
        if (preg_match('/<+(.*?)>/', $o_data, $o_matches)) {
            $email = $o_matches[1];
            $entities = $em->getRepository('TUIToolkitUserBundle:User')
                ->findByEmail($email);
            if (NULL !== $entities) {
                $organizer = array_shift($entities);
                $form->getData()->setOrganizer($organizer);
            }
        }
        //handling ajax request for SalesAgent same as we did with organizer
        $a_data = $form->getData()->getSalesAgent();
        if (preg_match('/<+(.*?)>/', $a_data, $a_matches)) {
            $agentEmail = $a_matches[1];

            $agentEntities = $em->getRepository('TUIToolkitUserBundle:User')
                ->findByEmail($agentEmail);
            if (NULL !== $agentEntities) {
                $salesAgent = array_shift($agentEntities);
                $form->getData()->setSalesAgent($salesAgent);
            }
        }

        //handling ajax request for SecondaryContact same as we did with organizer
        $s_data = $form->getData()->getSecondaryContact();
        if (preg_match('/<+(.*?)>/', $s_data, $s_matches)) {
            $secondEmail = $s_matches[1];
            $secondEntities = $em->getRepository('TUIToolkitUserBundle:User')
                ->findByEmail($secondEmail);
            if (NULL !== $secondEntities) {
                $secondAgent = array_shift($secondEntities);
                $form->getData()
                    ->setSecondaryContact($secondAgent);
            }
        }

        //Handling the request for institution a little different than we did for the other 2.
        $institutionParts = explode(' - ', $form->getData()->getQuoteReference()->getInstitution());
        $institutionEntities = $em->getRepository('InstitutionBundle:Institution')->findBy(
          array('name' => $institutionParts[0], 'city' => $institutionParts[1])
        );
        if (null !== $institutionEntities) {
            $institution = array_shift($institutionEntities);
            $form->getData()->setInstitution($institution);
        }

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();
            // Create organizer permission
            $permission = $this->get("permission.set_permission")->setPermission($entity->getId(), 'tour', $entity->getOrganizer(), 'organizer');
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.save') . $entity->getName());

            return $this->redirect($this->generateUrl('manage_tour'));
        }
        $date_format = $this->container->getParameter('date_format');
        return $this->render('TourBundle:Tour:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'date_format' => $date_format,
        ));
    }

    /**
     *
    /**
     * Creates a form to create a Tour entity.
     *
     * @param Tour $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Tour $entity)
    {
        $locale = $this->container->getParameter('locale');
        $currency_code = $this->container->getParameter('currency');
        $em = $this->getDoctrine()->getManager();
        $currency = $em->getRepository('CurrencyBundle:Currency')->findByCode($currency_code);
        $currency = array_shift($currency);
        $form = $this->createForm(new TourType($locale), $entity, array(
            'action' => $this->generateUrl('manage_tour_create'),
            'method' => 'POST',
        ));
        $form->get('quoteReference')->get('salesAgent')->setData($this->get('security.token_storage')->getToken()->getUser());
        $form->get('currency')->setdata($currency);
       // $form->get('expiryDate')->setdata(new \DateTime('now + 30 days'));
        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.create')));

        return $form;
    }

    /**
     * Displays a form to create a new Tour entity.
     *
     */
    public function newAction()
    {
        $date_format = $this->container->getParameter('date_format');
        $entity = new Tour();
        $form = $this->createCreateForm($entity);

        return $this->render('TourBundle:Tour:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'date_format' => $date_format,
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

        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        if ($user != 'anon.') {
            $permission = $this->get("permission.set_permission")->getPermission($id, 'tour', $user->getId());
        }

        if ($securityContext->isGranted('ROLE_BRAND') || in_array('organizer', $permission)) {
          $editable = TRUE;
        }

        $em = $this->getDoctrine()->getManager();

        $locale = $this->container->getParameter('locale');

        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }

        $collection = $entity->getMedia()->toArray() ? $entity->getMedia()->toArray() : NULL;

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
                    $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($blocks[0]);
                    if (!$blockObj) {
                        throw $this->createNotFoundException('Unable to find Content Block entity.');
                    }
                    $items[$blockObj->getId()] = $blockObj;
                } else {
                    foreach ($blocks as $block) {
                        $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find((int)$block);
                        if (!$blockObj) {
                            throw $this->createNotFoundException('Unable to find Content Block entity.');
                        }
                        $items[$blockObj->getId()] = $blockObj;
                    }
                }
            }
        }


        $deleteForm = $this->createDeleteForm($id);

        return $this->render('TourBundle:Tour:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
            'locale' => $locale,
            'collection' => $collection,
            'items' => $items,
            'tabs' => $tabs,
            'editable' => $editable,
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
                    $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($blocks[0]);
                    if (!$blockObj) {
                        throw $this->createNotFoundException('Unable to find Content Block entity.');
                    }
                    $items[$blockObj->getId()] = $blockObj;
                } else {
                    foreach ($blocks as $block) {
                        $blockObj = $em->getRepository('ContentBlocksBundle:ContentBlock')->find((int)$block);
                        if (!$blockObj) {
                            throw $this->createNotFoundException('Unable to find Content Block entity.');
                        }
                        $items[$blockObj->getId()] = $blockObj;
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
        $form = $this->createForm(new TourType($locale), $entity, array(
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
        $date_format = $this->container->getParameter('date_format');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }
        $collection = $entity->getMedia()->toArray();

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        //handling ajax request for organizer
        $o_data = $editForm->getData()->getOrganizer();
        if (preg_match('/<+(.*?)>/', $o_data, $o_matches)) {
            $email = $o_matches[1];
            $entities = $em->getRepository('TUIToolkitUserBundle:User')
                ->findByEmail($email);
            if (NULL !== $entities) {
                $organizer = array_shift($entities);
                $editForm->getData()->setOrganizer($organizer);
            }
        }
        //handling ajax request for SalesAgent same as we did with organizer
        $a_data = $editForm->getData()->getSalesAgent();
        if (preg_match('/<+(.*?)>/', $a_data, $a_matches)) {
            $agentEmail = $a_matches[1];
            $agentEntities = $em->getRepository('TUIToolkitUserBundle:User')
                ->findByEmail($agentEmail);
            if (NULL !== $agentEntities) {
                $salesAgent = array_shift($agentEntities);
                $editForm->getData()->setSalesAgent($salesAgent);
            }
        }

        //handling ajax request for SecondaryContact same as we did with organizer
        $s_data = $editForm->getData()->getSecondaryContact();
        if (preg_match('/<+(.*?)>/', $s_data, $s_matches)) {
            $secondEmail = $s_matches[1];
            $secondEntities = $em->getRepository('TUIToolkitUserBundle:User')
                ->findByEmail($secondEmail);
            if (NULL !== $secondEntities) {
                $secondAgent = array_shift($secondEntities);
                $editForm->getData()->setSecondaryContact($secondAgent);
            }
        }

        //Handling the request for institution a little different than we did for the other 2.
        $institutionParts = explode(' - ', $editForm->getData()->getQuoteReference()->getInstitution());
        $institutionEntities = $em->getRepository('InstitutionBundle:Institution')->findBy(
          array('name' => $institutionParts[0], 'city' => $institutionParts[1])
        );
        if (null !== $institutionEntities) {
            $institution = array_shift($institutionEntities);
            $editForm->getData()->setInstitution($institution);
        }

        $medias = array();

        if (NULL != $editForm->getData()->getMedia()) {
            $fileIdString = $editForm->getData()->getMedia();
            $fileIds = explode(',', $fileIdString);

            foreach ($fileIds as $fileId) {
                $image = $em->getRepository('MediaBundle:Media')
                    ->findById($fileId);
                $medias[] = array_shift($image);
            }
        }
        if (!empty($medias)) {
            $editForm->getData()->setMedia($medias);

        }


        if ($editForm->isValid()) {

            if (NULL != $editForm->getData()->getMedia()) {
                $web_dir = $_SERVER['DOCUMENT_ROOT'];
                $exportsDir = $web_dir . "/static/exports/";

                if (!file_exists($exportsDir) && !is_dir($exportsDir)) {
                    mkdir($exportsDir, 0755);
                }

                $collection = $editForm->getData()->getMedia();

                $zip = new \ZipArchive();
                $fileName = $entity->getquoteNumber() . ".zip";
                $zip->open("static/exports/" . $fileName, \ZipArchive::OVERWRITE);

                foreach ($collection as $c) {
                    $zip->addFromString($c->gethashedFilename(), file_get_contents($c->getfilepath() . "/" . $c->gethashedFilename()));
                }


                $zip->close();
                $em->flush();
            }
          // loop through payment tasks and set type to institution
            foreach($editForm->getData()->getPaymentTasks() as $paymentTask){
              $paymentTask->setType('institution');
            }

            $em->flush();
            $permission = $this->get("permission.set_permission")->setPermission($entity->getId(), 'tour', $entity->getOrganizer(), 'organizer');
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.save') . $entity->getName());
            return $this->redirect($this->generateUrl('manage_tour'));
        }

        return $this->render('TourBundle:Tour:edit.html.twig', array(
            'entity' => $entity,
            'collection' => $collection,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'date_format' => $date_format,
        ));
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
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.delete') . $entity->getName());

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
            ->add('submit', 'button', array(
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
        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.delete') . $tour->getName());

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
        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.restore') . $tour->getName());

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
        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.lock'));

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
        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.lock'));

        return $this->redirect($this->generateUrl('manage_tour'));

    }

    /**
     * Clone Content Blocks for a Tour
     *
     */

    public function cloneContentBlocks($content = array())
    {
        $newContentArray = array();
        if (!empty($content) && $content != NULL) {
            foreach ($content as $tab => $blocks) {
                foreach ($blocks[1] as $block) { // block should be an ID number
                    $em = $this->getDoctrine()->getManager();

                    $originalBlock = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);

                    if (!$originalBlock) {
                        throw $this->createNotFoundException('Unable to find Content entity while cloning from quote to tour.');
                    }

                    $newBlock = clone $originalBlock;
                    $newBlock->setId(null);
                    $em->persist($newBlock);
                    $em->flush($newBlock);

                    $newContentArray[$tab][0] = $blocks[0];
                    $newContentArray[$tab][1][] = $newBlock->getID();
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

    /**
     * @param $id
     * @return Response
     */

    public function getTourCompletedAndSetupAction($id)
    {
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

        //get number of wait listed
        $passengers = $em->getRepository('PassengerBundle:Passenger')->findByTourReference($entity);
        $waitlist = 0;
        $accepted = 0;
        foreach ($passengers as $passenger) {
            if ($passenger->getStatus() == "waitlist") {
                $waitlist++;
            }
            else if ($passenger->getStatus() == "accepted"){
                $accepted++;
            }
        }

        return $this->render('TourBundle:Tour:completedandsetup.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'date_format' => $date_format,
            'locale' => $locale,
            'brand' => $brand,
            'waitlist' => $waitlist,
            'accepted' => $accepted,
        ));

    }

    /**
     * @param $id
     * @return Response
     */

    public function getTourNotCompletedAndSetupAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TourBundle:Tour')->find($id);

        $date_format = $this->container->getParameter('date_format');

        $locale = $this->container->getParameter('locale');

        return $this->render('TourBundle:Tour:notCompletedAndSetup.html.twig', array(
            'entity' => $entity,
            'date_format' => $date_format,
            'locale' => $locale
        ));

    }

    /**
     * @param $id
     * @return Response
     */

    public function getTourNotSetupAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');
        $setupForm = $this->createTourSetupForm($entity);

        return $this->render('TourBundle:Tour:notSetup.html.twig', array(
            'entity' => $entity,
            'setup_form' => $setupForm->createView(),
            'date_format' => $date_format,
            'locale' => $locale,
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
        $setupForm = $this->createForm(new TourSetupType($locale), $entity, array(
            'action' => $this->generateUrl('manage_tour_setup', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $setupForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('tour.actions.save')));

        return $setupForm;
    }


    public function TourSetupAction(Request $request, $id)
    {
        $date_format = $this->container->getParameter('date_format');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TourBundle:Tour')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tour entity.');
        }

        $setupForm = $this->createTourSetupForm($entity);
        $setupForm->handleRequest($request);

        $entity->setSetupComplete(true);

        $payments = $setupForm->getData()->getPaymentTasksPassenger();

        foreach ($payments as $payment) {
            $payment->setType("passenger");
        }

        if ($setupForm->isValid()) {
            $em->flush();
            $permission = $this->get("permission.set_permission")->setPermission($entity->getId(), 'tour', $entity->getOrganizer(), 'organizer');
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.save') . $entity->getName());
            return $this->redirect($this->generateUrl('manage_tour_show', array('id' => $id)));
        }

        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.not_saved') . $entity->getName());
        return $this->redirect($this->generateUrl('manage_tour_show', array('id' => $id)));


    }

    public function setupCompleteAction($id, $quoteNumber)
    {
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
        $locale = $this->container->getParameter('locale');
        $notifyForm = $this->createForm(new ContactOrganizerType($locale), array(), array(
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


        return $this->render('TourBundle:Tour:contactorganizer.html.twig', array(
            'notify_form' => $notifyForm->createView(),
            'entity' => $entity,
            'locale' => $locale,
            'date_format' => $date_format,
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

        if ($entity->getOrganizer()->isEnabled() == true) {

            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('tour.email.setup.subject'))
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
                ->setSubject($this->get('translator')->trans('tour.email.setup.subject'))
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
        $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('tour.flash.registration_notification') . " " . $organizerEmail);


    return $this->redirect($this->generateUrl('manage_tour'));

    }
}