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
class QuoteVersionController extends Controller
{

    /**
     * Lists all QuoteVersion ( IE Quotes) entities.
     *
     */
    public function indexAction()
    {
      // hide columns from the screen display
      $hidden = array(
        'quoteReference.id',
        'quoteReference.institution.name',
        'quoteReference.institution.city',
        'converted',
        'deleted',
        'locked',
        'quoteReference.converted',
        'quoteReference.setupComplete',
        'quoteReference.organizer.firstName',
        'quoteReference.organizer.lastName',
        'quoteReference.organizer.email',
        'quoteReference.salesAgent.firstName',
        'quoteReference.salesAgent.lastName',
        'quoteReference.salesAgent.email',
        'quoteReference.destination',
        'created',
        'version',
        'id',
        'duration',
        'tripStatus.name',
        'expiryDate',
        'transportType.name',
        'boardBasis.name',
        'freePlaces',
        'payingPlaces',
        'departureDate',
        'returnDate',
        'pricePerson',
        'currency.name'
      );

      // Creates simple grid based on your entity (ORM)
      $source = new Entity('QuoteBundle:QuoteVersion');

      //add WHERE clause
      $tableAlias=$source->getTableAlias();

      $source->manipulateQuery(
        function ($query) use ($tableAlias)
        {      $quoteAlias = '_quoteReference';
          $query
         // ->andWhere($tableAlias . '.ts IS NULL')
          ->andWhere($tableAlias . '.converted = false')
          ->andWhere($tableAlias . '.isTemplate = false');
        }
      );

      /* @var $grid \APY\DataGridBundle\Grid\Grid */
      $grid = $this->get('grid');

      // Attach the source to the grid
      $grid->setSource($source);
      $grid->setId('quoteversiongrid');
      $grid->hideColumns($hidden);

      // Add action column
      $editAction = new RowAction('Edit', 'manage_quote_edit');
      $editAction->manipulateRender(
        function ($action, $row)
        { // business rule is only admins can edit locked quotes
          if ($row->getField('locked') == true) {
            if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
              return null;
            }
          }
          return $action;
        }
      );
      $grid->addRowAction($editAction);
      $showAction = new RowAction('View', 'manage_quote_show');
      $grid->addRowAction($showAction);
      $previewAction = new RowAction('Preview', 'quote_site_action_show');
      $grid->addRowAction($previewAction);
      $cloneAction = new RowAction('Clone', 'manage_quote_clone');
      $grid->addRowAction($cloneAction);
      $deleteAction = new RowAction('Delete', 'manage_quote_quick_delete');
      $deleteAction->setRole('ROLE_ADMIN');
      $deleteAction->setConfirm(true);
      $grid->addRowAction($deleteAction);
      $lockAction = new RowAction('Lock', 'manage_quoteversion_lock_nonajax');
      $lockAction->manipulateRender(
        function ($action, $row)
        {
          if ($row->getField('locked') == true) {
            $action->setTitle('Unlock');
          }
          return $action;
        }
      );
      $grid->addRowAction($lockAction);

      // Set the default order of the grid
      $grid->setDefaultOrder('created', 'DESC');

      // Set the selector of the number of items per page
      $grid->setLimits(array(10, 25, 50, 100));

      //set no data message
      $grid->setNoDataMessage("There are no quotes to show. Please check your filter settings and try again.");


      // Export of the grid
      $grid->addExport(new CSVExport("Quotes as CSV", "activeQuotes", array('delimiter'=>','), "UTF-8", "ROLE_BRAND"));


      // Manage the grid redirection, exports and the response of the controller
      return $grid->getGridResponse('QuoteBundle:QuoteVersion:index.html.twig');
    }

  /**
   * Lists all Converted QuoteVersion ( IE Quotes) entities.
   *
   */
  public function convertedAction()
  {
    // hide columns from the screen display
    $hidden = array(
      'quoteReference.id',
      'quoteReference.institution.name',
      'quoteReference.institution.city',
      'converted',
      'deleted',
      'locked',
      'quoteReference.converted',
      'quoteReference.setupComplete',
      'quoteReference.organizer.firstName',
      'quoteReference.organizer.lastName',
      'quoteReference.organizer.email',
      'quoteReference.salesAgent.firstName',
      'quoteReference.salesAgent.lastName',
      'quoteReference.salesAgent.email',
      'quoteReference.destination',
      'created',
      'version',
      'id',
      'duration',
      'tripStatus.name',
      'expiryDate',
      'transportType.name',
      'boardBasis.name',
      'freePlaces',
      'payingPlaces',
      'departureDate',
      'returnDate',
      'pricePerson',
      'currency.name'
    );

    // Creates simple grid based on your entity (ORM)
    $source = new Entity('QuoteBundle:QuoteVersion');
    $quotetable = new Entity('QuoteBundle:Quote');
    //add WHERE clause
    $tableAlias=$source->getTableAlias();

    $source->manipulateQuery(
      function ($query) use ($tableAlias)
      {
        $query
         // ->andWhere($tableAlias . '.ts IS NULL')
          ->andWhere($tableAlias . '.converted = true')
          ->andWhere($tableAlias . '.isTemplate = false');
      }
    );

    /* @var $grid \APY\DataGridBundle\Grid\Grid */
    $grid = $this->get('grid');

    // Attach the source to the grid
    $grid->setSource($source);
    $grid->setId('quoteversiongrid');
    $grid->hideColumns($hidden);

    // Add action column
    $showAction = new RowAction('View', 'manage_quote_show');
    $grid->addRowAction($showAction);
    $cloneAction = new RowAction('Clone', 'manage_quote_clone');
    $grid->addRowAction($cloneAction);
    $deleteAction = new RowAction('Delete', 'manage_quote_quick_delete');
    $deleteAction->setRole('ROLE_ADMIN');
    $deleteAction->setConfirm(true);
    $grid->addRowAction($deleteAction);

    // Set the default order of the grid
    $grid->setDefaultOrder('created', 'DESC');

    // Set the selector of the number of items per page
    $grid->setLimits(array(10, 25, 50, 100));

    //set no data message
    $grid->setNoDataMessage("There are no converted quotes to show. Please check your filter settings and try again.");


    // Export of the grid
    $grid->addExport(new CSVExport("Converted Quotes as CSV", "convertedQuotes", array('delimiter'=>','), "UTF-8", "ROLE_BRAND"));


    // Manage the grid redirection, exports and the response of the controller
    return $grid->getGridResponse('QuoteBundle:QuoteVersion:converted.html.twig');
  }
  /**
   * Lists all Deleted QuoteVersion ( IE Quotes) entities.
   *
   */
  public function deletedAction()
  {
    $em = $this->getDoctrine()->getManager();
    $filters = $em->getFilters();
    $filters->disable('softdeleteable');

    // hide columns from the screen display
    $hidden = array(
      'quoteReference.id',
      'quoteReference.institution.name',
      'quoteReference.institution.city',
      'converted',
      'deleted',
      'locked',
      'quoteReference.converted',
      'quoteReference.setupComplete',
      'quoteReference.organizer.firstName',
      'quoteReference.organizer.lastName',
      'quoteReference.organizer.email',
      'quoteReference.salesAgent.firstName',
      'quoteReference.salesAgent.lastName',
      'quoteReference.salesAgent.email',
      'quoteReference.destination',
      'created',
      'version',
      'id',
      'duration',
      'tripStatus.name',
      'expiryDate',
      'transportType.name',
      'boardBasis.name',
      'freePlaces',
      'payingPlaces',
      'departureDate',
      'returnDate',
      'pricePerson',
      'currency.name'
    );

    // Creates simple grid based on your entity (ORM)
    $source = new Entity('QuoteBundle:QuoteVersion');

    //add WHERE clause
    $tableAlias=$source->getTableAlias();

    $source->manipulateQuery(
      function ($query) use ($tableAlias)
      {
        $query
          //->andWhere($tableAlias . '.ts IS NULL')
          ->andWhere($tableAlias . ".deleted IS NOT NULL")
        ;
        $dql = $query->getDql();
        $foo='';
      }
    );

    /* @var $grid \APY\DataGridBundle\Grid\Grid */
    $grid = $this->get('grid');

    // Attach the source to the grid
    $grid->setSource($source);
    $grid->setId('quoteversiongrid');
    $grid->hideColumns($hidden);

    // Add action column
    $restoreAction = new RowAction('Restore', 'manage_quote_restore');
    $grid->addRowAction($restoreAction);


    // Set the default order of the grid
    $grid->setDefaultOrder('created', 'DESC');

    // Set the selector of the number of items per page
    $grid->setLimits(array(10, 25, 50, 100));

    //set no data message
    $grid->setNoDataMessage("There are no deleted quotes to show. Please check your filter settings and try again.");


    // Export of the grid
    $grid->addExport(new CSVExport("Deleted Quotes as CSV", "deletedQuotes", array('delimiter'=>','), "UTF-8", "ROLE_BRAND"));


    // Manage the grid redirection, exports and the response of the controller
    return $grid->getGridResponse('QuoteBundle:QuoteVersion:deleted.html.twig');
  }

  /**
   * Lists all Quote Templates( IE Quotes) entities.
   *
   */
  public function templatesAction()
  {
    // hide columns from the screen display
    $hidden = array(
      'quoteReference.id',
      'quoteReference.institution.name',
      'quoteReference.institution.city',
      'converted',
      'deleted',
      'locked',
      'quoteReference.converted',
      'quoteReference.setupComplete',
      'quoteReference.organizer.firstName',
      'quoteReference.organizer.lastName',
      'quoteReference.organizer.email',
      'quoteReference.salesAgent.firstName',
      'quoteReference.salesAgent.lastName',
      'quoteReference.salesAgent.email',
      'quoteReference.destination',
      'created',
      'version',
      'id',
      'duration',
      'tripStatus.name',
      'expiryDate',
      'transportType.name',
      'boardBasis.name',
      'freePlaces',
      'payingPlaces',
      'departureDate',
      'returnDate',
      'pricePerson',
      'currency.name'
    );

    // Creates simple grid based on your entity (ORM)
    $source = new Entity('QuoteBundle:QuoteVersion');

    //add WHERE clause
    $tableAlias=$source->getTableAlias();

    $source->manipulateQuery(
      function ($query) use ($tableAlias)
      {
        $query
          //->andWhere($tableAlias . '.ts IS NULL')
          ->andWhere($tableAlias . '.isTemplate = true');
      }
    );

    /* @var $grid \APY\DataGridBundle\Grid\Grid */
    $grid = $this->get('grid');

    // Attach the source to the grid
    $grid->setSource($source);
    $grid->setId('quoteversiongrid');
    $grid->hideColumns($hidden);

    // Add action column
    $editAction = new RowAction('Edit', 'manage_quote_edit');
    $editAction->manipulateRender(
      function ($action, $row)
      { // business rule is only admins can edit locked quotes
        if ($row->getField('locked') == true) {
          if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return null;
          }
        }
        return $action;
      }
    );
    $grid->addRowAction($editAction);
    $showAction = new RowAction('View', 'manage_quote_show');
    $grid->addRowAction($showAction);
    $cloneAction = new RowAction('Clone', 'manage_quote_clone');
    $grid->addRowAction($cloneAction);
    $deleteAction = new RowAction('Delete', 'manage_quote_quick_delete');
    $deleteAction->setRole('ROLE_ADMIN');
    $deleteAction->setConfirm(true);
    $grid->addRowAction($deleteAction);

    // templates shouldnt have these fields or filters:
    // reference
    $reference = $grid->getColumn('quoteNumber');
    $reference->setFilterable(false);
    // institution
    $institution = $grid->getColumn('quoteReference.institution.name');
    $institution->setFilterable(false);
    // organizer
    $organizer = $grid->getColumn('organizer_full');
    $organizer->setFilterable(false);

    // Set the default order of the grid
    $grid->setDefaultOrder('created', 'DESC');

    // Set the selector of the number of items per page
    $grid->setLimits(array(10, 25, 50, 100));

    //set no data message
    $grid->setNoDataMessage("There are no templates to show. Please check your filter settings and try again.");


    // Export of the grid
    $grid->addExport(new CSVExport("Quote Templates as CSV", "templatesQuotes", array('delimiter'=>','), "UTF-8", "ROLE_BRAND"));


    // Manage the grid redirection, exports and the response of the controller
    return $grid->getGridResponse('QuoteBundle:QuoteVersion:templates.html.twig');
  }



  /**
     * Creates a new QuoteVersion entity.
     *
     */
    public function createAction(Request $request)
    {
//        //Handling the autocomplete field for organizer.  We need to convert the string from organizer into the object.
        $entity = new QuoteVersion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();

        //handling ajax request for organizer
        $o_data = $form->getData()->getQuoteReference()->getOrganizer();
        if(preg_match('/<+(.*?)>/',$o_data, $o_matches)) {
          $email = $o_matches[1];
          $entities = $em->getRepository('TUIToolkitUserBundle:User')
            ->findByEmail($email);
          if (NULL !== $entities) {
            $organizer = array_shift($entities);
            $form->getData()->getQuoteReference()->setOrganizer($organizer);
          }
        }
        //handling ajax request for SalesAgent same as we did with organizer
        $a_data = $form->getData()->getQuoteReference()->getSalesAgent();
        if(preg_match('/<+(.*?)>/',$a_data, $a_matches)) {
          $agentEmail = $a_matches[1];

          $agentEntities = $em->getRepository('TUIToolkitUserBundle:User')
            ->findByEmail($agentEmail);
          if (NULL !== $agentEntities) {
            $salesAgent = array_shift($agentEntities);
            $form->getData()->getQuoteReference()->setSalesAgent($salesAgent);
          }
        }

        //handling ajax request for SecondaryContact same as we did with organizer
        $s_data = $form->getData()->getQuoteReference()->getSecondaryContact();
        if(preg_match('/<+(.*?)>/',$s_data, $s_matches)) {
          $secondEmail = $s_matches[1];
          $secondEntities = $em->getRepository('TUIToolkitUserBundle:User')
            ->findByEmail($secondEmail);
          if (NULL !== $secondEntities) {
            $secondAgent = array_shift($secondEntities);
            $form->getData()
              ->getQuoteReference()
              ->setSecondaryContact($secondAgent);
          }
        }

        //Handling the request for institution a little different than we did for the other 2.
        $institutionName =  explode(' - ', $form->getData()->getQuoteReference()->getInstitution());
        $institutionEntities = $em->getRepository('InstitutionBundle:Institution')->findByName($institutionName[0]);
        if(null!==$institutionEntities) {
          $institution = array_shift($institutionEntities);
          $form->getData()->getQuoteReference()->setInstitution($institution);
        }

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();
          // Create organizer permission
          $permission = $this->get("permission.set_permission")->setPermission($entity->getQuoteReference()->getId(), 'quote', $entity->getQuoteReference()->getOrganizer(), 'organizer');
            $this->get('session')->getFlashBag()->add('notice', 'Quote Saved: '. $entity->getName());

            return $this->redirect($this->generateUrl('manage_quote'));
        }
        $date_format = $this->container->getParameter('date_format');
        return $this->render('QuoteBundle:QuoteVersion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'date_format' => $date_format,
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
    $em = $this->getDoctrine()->getManager();

    //handling ajax request for SalesAgent
    $a_data = $form->getData()->getQuoteReference()->getSalesAgent();
    if(preg_match('/<+(.*?)>/',$a_data, $a_matches)) {
      $agentEmail = $a_matches[1];
      $agentEntities = $em->getRepository('TUIToolkitUserBundle:User')
        ->findByEmail($agentEmail);
      if (NULL !== $agentEntities) {
        $salesAgent = array_shift($agentEntities);
        $form->getData()->getQuoteReference()->setSalesAgent($salesAgent);
      }
    }
    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $em->persist($entity);
      $em->flush();
      $this->get('session')->getFlashBag()->add('notice', 'Quote Template Saved: '. $entity->getName());

      return $this->redirect($this->generateUrl('manage_quote_templates'));
    }

    $date_format = $this->container->getParameter('date_format');
    return $this->render('QuoteBundle:QuoteVersion:new.html.twig', array(
      'entity' => $entity,
      'form'   => $form->createView(),
      'template' => 'Template',
      'date_format' => $date_format,
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
        $locale = $this->container->getParameter('locale');
        $currency_code = $this->container->getParameter('currency');
        $em = $this->getDoctrine()->getManager();
        $currency = $em->getRepository('CurrencyBundle:Currency')->findByCode($currency_code);
        $currency = array_shift($currency);
        $form = $this->createForm(new QuoteVersionType($locale), $entity, array(
            'action' => $this->generateUrl('manage_quoteversion_create'),
            'method' => 'POST',
        ));
        $form->get('quoteReference')->get('salesAgent')->setData($this->get('security.token_storage')->getToken()->getUser());
        $form->get('currency')->setdata($currency);
        $form->get('expiryDate')->setdata(new \DateTime('now + 30 days'));
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
    $locale = $this->container->getParameter('locale');
    $currency_code = $this->container->getParameter('currency');
    $em = $this->getDoctrine()->getManager();
    $currency = $em->getRepository('CurrencyBundle:Currency')->findByCode($currency_code);
    $currency = array_shift($currency);
    $form = $this->createForm(new QuoteVersionType($locale), $entity, array(
      'action' => $this->generateUrl('manage_quoteversion_createtemplate'),
      'method' => 'POST',
    ));

    $form->get('quoteReference')->get('salesAgent')->setData($this->get('security.token_storage')->getToken()->getUser());
    $form->get('currency')->setdata($currency);
    $form->add('submit', 'submit', array('label' => 'Create Template'));

    return $form;
  }

    /**
     * Displays a form to create a new QuoteVersion entity.
     *
     */
    public function newAction()
    {
        $date_format = $this->container->getParameter('date_format');
        $entity = new QuoteVersion();
        $form   = $this->createCreateForm($entity);

        return $this->render('QuoteBundle:QuoteVersion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'date_format' => $date_format,
        ));
    }

  /**
   * Displays a form to create a new Quote Template entity.
   *
   */
  public function newTemplateAction()
  {
    $date_format = $this->container->getParameter('date_format');
    $entity = new QuoteVersion();
    $form   = $this->createTemplateCreateForm($entity);

    return $this->render('QuoteBundle:QuoteVersion:new.html.twig', array(
      'entity' => $entity,
      'form'   => $form->createView(),
      'template' => "Template",
      'date_format' => $date_format,
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

      $locale = $this->container->getParameter('locale');

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


        $deleteForm = $this->createDeleteForm($id);

        return $this->render('QuoteBundle:QuoteVersion:show.html.twig', array(
            'entity'      => $entity[0],
            'delete_form' => $deleteForm->createView(),
            'locale'      => $locale,
            'items'       => $items,
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
      $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find QuoteVersion entity.' . $id);
        }

         if($entity->getIsTemplate()){
           $template='Template';
         } else {$template='';}
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($entity->getId());
        $date_format = $this->container->getParameter('date_format');

        return $this->render('QuoteBundle:QuoteVersion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'template'    => $template,
            'date_format' => $date_format,
        ));
    }


  /**
   * Displays a form to clone an existing QuoteVersion entity.
   * @param $id id of the parent Quote object
   */
  public function cloneAction($id)
  {
    $em = $this->getDoctrine()->getManager();
    $template=null;

    // Get all Quote versions referencing Parent Quote object
    $original_entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

    if (!$original_entity) {
      throw $this->createNotFoundException('Unable to find QuoteVersion entity.' . $id);
    }

    $deepCopy = new DeepCopy();
    $deepCopy->addFilter(new SetNullFilter(), new PropertyNameMatcher('id'));
    $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher('QuoteVersion', 'currency'));
    $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher('QuoteVersion', 'tripStatus'));
    $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher('QuoteVersion', 'transportType'));
    $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher('QuoteVersion', 'boardBasis'));
    $deepCopy->addFilter(new DoctrineCollectionFilter(), new PropertyTypeMatcher('Doctrine\Common\Collections\Collection'));
    $new_entity = $deepCopy->copy($original_entity);


    if($original_entity->getIsTemplate()){
      $new_entity->setIsTemplate(false);
      $template = 'Template';
    }

    $em->persist($new_entity);

    $cloneForm = $this->createCloneForm($new_entity);
    $date_format = $this->container->getParameter('date_format');

    return $this->render('QuoteBundle:QuoteVersion:edit.html.twig', array(
      'entity'      => $new_entity,
      'edit_form'   => $cloneForm->createView(),
      'clone'       => 'Clone',
      'template'    =>  $template,
      'date_format' => $date_format,
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
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new QuoteVersionType($locale), $entity, array(
            'action' => $this->generateUrl('manage_quoteversion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

  /**
   * Creates a form to clone a QuoteVersion entity.
   *
   * @param QuoteVersion $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createCloneForm(QuoteVersion $entity)
  {
    $locale = $this->container->getParameter('locale');
    $form = $this->createForm(new QuoteVersionType($locale), $entity, array(
      'action' => $this->generateUrl('manage_quoteversion_clone'),
      'method' => 'PUT',
    ));

    $form->add('submit', 'submit', array('label' => 'Save as Clone'));

    return $form;
  }


    /**
     * Edits an existing QuoteVersion entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $date_format = $this->container->getParameter('date_format');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
        if (!$entity) {
          throw $this->createNotFoundException('Unable to find QuoteVersion entity.');
        }
        if($entity->getIsTemplate()){
          $template='Template'; $route = '_templates';
        } else {$template=''; $route = '';}

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        //handling ajax request for organizer
        $o_data = $editForm->getData()->getQuoteReference()->getOrganizer();
        if(preg_match('/<+(.*?)>/',$o_data, $o_matches)) {
          $email = $o_matches[1];
          $entities = $em->getRepository('TUIToolkitUserBundle:User')
            ->findByEmail($email);
          if (NULL !== $entities) {
            $organizer = array_shift($entities);
            $editForm->getData()->getQuoteReference()->setOrganizer($organizer);
          }
        }
        //handling ajax request for SalesAgent same as we did with organizer
        $a_data = $editForm->getData()->getQuoteReference()->getSalesAgent();
        if(preg_match('/<+(.*?)>/',$a_data, $a_matches)) {
          $agentEmail = $a_matches[1];
          $agentEntities = $em->getRepository('TUIToolkitUserBundle:User')
            ->findByEmail($agentEmail);
          if (NULL !== $agentEntities) {
            $salesAgent = array_shift($agentEntities);
            $editForm->getData()
              ->getQuoteReference()
              ->setSalesAgent($salesAgent);
          }
        }

        //handling ajax request for SecondaryContact same as we did with organizer
        $s_data = $editForm->getData()->getQuoteReference()->getSecondaryContact();
        if(preg_match('/<+(.*?)>/',$s_data, $s_matches)) {
          $secondEmail = $s_matches[1];
          $secondEntities = $em->getRepository('TUIToolkitUserBundle:User')
            ->findByEmail($secondEmail);
          if (NULL !== $secondEntities) {
            $secondAgent = array_shift($secondEntities);
            $editForm->getData()
              ->getQuoteReference()
              ->setSecondaryContact($secondAgent);
          }
        }

        //Handling the request for institution a little different than we did for the other 2.
        $institutionName =  explode(' - ', $editForm->getData()->getQuoteReference()->getInstitution());
        $institutionEntities = $em->getRepository('InstitutionBundle:Institution')->findByName($institutionName[0]);
        $institutionEntities = $em->getRepository('InstitutionBundle:Institution')->findByName($institutionName);
        if(null!==$institutionEntities) {
          $institution = array_shift($institutionEntities);
          $editForm->getData()->getQuoteReference()->setInstitution($institution);
        }

        // Handling if the Save as New Revision button was clicked
        if($_POST['tui_toolkit_quotebundle_quoteversion']['revision']=='revision'){
          // this is a save as new revision call so duplicate the quoteversion
          $new_entity = clone($entity);
          $new_entity->setVersion($entity->getVersion() + 1);

          // Validate the quoteNumber field is unique
          $uniqueEntity = new UniqueEntity('quoteNumber');
          $message= 'This Quote Number is already used on another Quote.';
          $uniqueEntity->message = $message;
          $errors = $this->get('validator')->validate(
            $new_entity,
            $uniqueEntity
          );
          if (!$errors) {
            $new_entity->setId(null);
            $em->detach($entity);
            $em->persist($new_entity);
            $em->flush($new_entity);
            $permission = $this->get("permission.set_permission")->setPermission($new_entity->getQuoteReference()->getId(), 'quote', $new_entity->getQuoteReference()->getOrganizer(), 'organizer');
            $this->get('session')->getFlashBag()->add('notice', 'Quote Saved: '. $new_entity->getName());
            return $this->redirect($this->generateUrl('manage_quote' . $route));
          }
          $this->get('session')->getFlashBag()->add('error', $message);
          return $this->render('QuoteBundle:QuoteVersion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'template'    => $template,
            'date_format' => $date_format,
          ));
        }

      // handling a standard edit (not a save as new version)
      if ($editForm->isValid()) {
        $em->flush();
        $permission = $this->get("permission.set_permission")->setPermission($entity->getQuoteReference()->getId(), 'quote', $entity->getQuoteReference()->getOrganizer(), 'organizer');
        $this->get('session')->getFlashBag()->add('notice', 'Quote Saved: '. $entity->getName());
        return $this->redirect($this->generateUrl('manage_quote' . $route));
      }

      return $this->render('QuoteBundle:QuoteVersion:edit.html.twig', array(
          'entity'      => $entity,
          'edit_form'   => $editForm->createView(),
          'delete_form' => $deleteForm->createView(),
          'template'    => $template,
          'date_format' => $date_format,
      ));
    }


  /**
   * Edits an existing QuoteVersion entity.
   *
   */
  public function cloneUpdateAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = new QuoteVersion();
    $cloneform = $this->createCloneForm($entity);
    $cloneform->handleRequest($request);

    if($entity->getIsTemplate()){
      $template='Template'; $route = '_templates';
    } else {$template=''; $route = '';}


    //handling ajax request for organizer
    $o_data = $cloneform->getData()->getQuoteReference()->getOrganizer();
    if(preg_match('/<+(.*?)>/',$o_data, $o_matches)) {
      $email = $o_matches[1];
      $entities = $em->getRepository('TUIToolkitUserBundle:User')
        ->findByEmail($email);
      if (NULL !== $entities) {
        $organizer = array_shift($entities);
        $cloneform->getData()->getQuoteReference()->setOrganizer($organizer);
      }
    }
    //handling ajax request for SalesAgent same as we did with organizer
    $a_data = $cloneform->getData()->getQuoteReference()->getSalesAgent();
    if(preg_match('/<+(.*?)>/',$a_data, $a_matches)) {
      $agentEmail = $a_matches[1];
      $agentEntities = $em->getRepository('TUIToolkitUserBundle:User')
        ->findByEmail($agentEmail);
      if (NULL !== $agentEntities) {
        $salesAgent = array_shift($agentEntities);
        $cloneform->getData()
          ->getQuoteReference()
          ->setSalesAgent($salesAgent);
      }
    }

    //handling ajax request for SecondaryContact same as we did with organizer
    $s_data = $cloneform->getData()->getQuoteReference()->getSecondaryContact();
    if(preg_match('/<+(.*?)>/',$s_data, $s_matches)) {
      $secondEmail = $s_matches[1];
      $secondEntities = $em->getRepository('TUIToolkitUserBundle:User')
        ->findByEmail($secondEmail);
      if (NULL !== $secondEntities) {
        $secondAgent = array_shift($secondEntities);
        $cloneform->getData()
          ->getQuoteReference()
          ->setSecondaryContact($secondAgent);
      }
    }

    //Handling the request for institution a little different than we did for the other 2.
    $institutionName =  explode(' - ', $cloneform->getData()->getQuoteReference()->getInstitution());
    $institutionEntities = $em->getRepository('InstitutionBundle:Institution')->findByName($institutionName[0]);
    $institutionEntities = $em->getRepository('InstitutionBundle:Institution')->findByName($institutionName);
    if(null!==$institutionEntities) {
      $institution = array_shift($institutionEntities);
      $cloneform->getData()->getQuoteReference()->setInstitution($institution);
    }

    // clone the content blocks, but first load the original entity since we never did that - only used form values
    $pathArr = explode('/', $_SERVER['HTTP_REFERER']);
    if(is_numeric($pathArr[5])){
      $originalEntity = $em->getRepository('QuoteBundle:QuoteVersion')->find($pathArr[5]);
      $entity->setContent($this->cloneContentBlocks($originalEntity->getContent()));

      // And clone the Header block if it has one
      $headerBlock = $originalEntity->getHeaderBlock()->getId();
      $entity->setHeaderBlock($this->cloneHeaderBlock($headerBlock));
    }



    if ($cloneform->isValid()) {
      $em->persist($entity);
      $em->flush();
      $permission = $this->get("permission.set_permission")->setPermission($entity->getQuoteReference()->getId(), 'quote', $entity->getQuoteReference()->getOrganizer(), 'organizer');
      $this->get('session')->getFlashBag()->add('notice', 'Quote Saved: '. $entity->getName());


      return $this->redirect($this->generateUrl('manage_quote_show', array('id' => $entity->getId())));
    }

    $date_format = $this->container->getParameter('date_format');
    return $this->render('QuoteBundle:QuoteVersion:edit.html.twig', array(
      'entity'      => $entity,
      'edit_form'   => $cloneform->createView(),
      'template'    => $template,
      'date_format' => $date_format,
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
            ->add('submit', 'button', array(
              'label' => 'Delete',
              'attr' => array(
                'class' => 'delete-btn'
              )
            ))
            ->getForm()
        ;
    }

  /**
   * Adds a new tab into the content blocks array field
   *
   * @param mixed $id The entity id
   *
   * @return Symfony\Component\HttpFoundation\Response
   */
  public function newTabAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $entity = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
    $content = $entity->getContent();
    $content=array();

    if ($request->getMethod() == 'POST') {
      $newContent = $request->request->all();
    }

    foreach($newContent as $tab => $blocks){
      $tab = str_replace('_', ' ', $tab);
      if($blocks !='') {
        foreach ($blocks as $block) {
          $content[$tab][] = substr($block, 15);
        }
      } else {
        $content[$tab]=array();
      }
  }
    $entity->setContent($content);
    $em->persist($entity);
    $em->flush();

    $responseContent = json_encode($entity->getContent());

    return new Response($responseContent,
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
  }


  /**
   * quickly Deletes QuoteVersion entity.
   *
   */
  public function quickdeleteAction(Request $request, $id)
  {

    $em = $this->getDoctrine()->getManager();
    // dont forget to disable softdelete filter so doctrine can *find* the deleted entity

    $quoteVersion = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);
    if (!$quoteVersion) {
      throw $this->createNotFoundException('Unable to find Quote Version entity.');
    }
    $em->remove($quoteVersion);
    $em->flush();
    $this->get('session')->getFlashBag()->add('notice', 'Quote Deleted: ' . $quoteVersion->getName());

    return $this->redirect($this->generateUrl('manage_quote'));
  }


  /**
   * Creates a form to Restore a deleted QuoteVersion entity by id.
   *
   * @param mixed $id The entity id
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createRestoreForm($id)
  {
    return $this->createFormBuilder()
      ->setAction($this->generateUrl('manage_quote_restore', array('id' => $id)))
      ->setMethod('POST')
      ->add('submit', 'submit', array('label' => 'RESTORE'))
      ->getForm();
  }



  /**
   * Restores a Deleted Quote entity.
   *
   */
  public function restoreAction(Request $request, $id)
  {

    $em = $this->getDoctrine()->getManager();
    // dont forget to disable softdelete filter so doctrine can *find* the deleted entity
    $filters = $em->getFilters();
    $filters->disable('softdeleteable');

    $quoteVersion = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

    if (!$quoteVersion) {
      throw $this->createNotFoundException('Unable to find Quote entity.');
    }
    $quoteVersion->setDeleted(NULL);
    $em->persist($quoteVersion);
    $em->flush();
    $this->get('session')->getFlashBag()->add('notice', 'Quote Restored: ' . $quoteVersion->getName());

    return $this->redirect($this->generateUrl('manage_quote'));
  }


  /**
   * Toggles lock status on Quote entity.
   *
   */
  public function lockAction(Request $request, $id)
  {

    $em = $this->getDoctrine()->getManager();

    $quoteVersion = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

    if (!$quoteVersion) {
      throw $this->createNotFoundException('Unable to find Quote entity.');
    }

    if($quoteVersion->getLocked()==false){
      $status = true;
    } else {
      $status = false;
    }
    $quoteVersion->setLocked($status);
    $em->persist($quoteVersion);
    $em->flush();
    $this->get('session')->getFlashBag()->add('notice', 'Quote Lock has been toggled ');

    return new Response(json_encode( (array) $quoteVersion));

  }

  /**
   * Toggles lock status Quote entity without ajax.
   *
   */
  public function lockNonajaxAction(Request $request, $id)
  {

    $em = $this->getDoctrine()->getManager();

    $quoteVersion = $em->getRepository('QuoteBundle:QuoteVersion')->find($id);

    if (!$quoteVersion) {
      throw $this->createNotFoundException('Unable to find Quote entity.');
    }

    if($quoteVersion->getLocked()==false){
      $status = true;
    } else {
      $status = false;
    }
    $quoteVersion->setLocked($status);
    $em->persist($quoteVersion);
    $em->flush();
    $this->get('session')->getFlashBag()->add('notice', 'Quote Lock has been toggled ');

    return $this->redirect($this->generateUrl('manage_quote'));

  }

  /**
   * Clone Content Blocks for a QuoteVersion
   *
   */

  public function cloneContentBlocks($content = array())
  {
    $newContentArray = array();
    if(!empty($content) && $content!= NULL){
      foreach($content as $tab => $blocks){
        foreach($blocks as $block){ // block should be an ID number
          $em = $this->getDoctrine()->getManager();

          $originalBlock = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);

          if(!$originalBlock){
            throw $this->createNotFoundException('Unable to find Content entity.');
          }

          $newBlock = clone $originalBlock;
          $newBlock->setId(null);
          $em->persist($newBlock);
          $em->flush($newBlock);

          $newContentArray[$tab][] = $newBlock->getID();
        }
      }
    }

    return $newContentArray;
  }

  /**
   * Clone Header Block for a QuoteVersion
   *
   */

  public function cloneHeaderBlock($block)
  {
  $result = NULL;
    if(!empty($block) && $block!= NULL){
          $em = $this->getDoctrine()->getManager();

          $originalBlock = $em->getRepository('ContentBlocksBundle:ContentBlock')->find($block);

          if(!$originalBlock){
            throw $this->createNotFoundException('Unable to find Content entity.');
          }

          $newBlock = clone $originalBlock;
          $newBlock->setId(null);
          $em->persist($newBlock);
          $em->flush($newBlock);
          $result = $newBlock;
    }

    return $result;
  }
}
