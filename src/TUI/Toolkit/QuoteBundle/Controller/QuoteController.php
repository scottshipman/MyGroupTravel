<?php
/*
 * DataGridBundle documentation:
 * https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/summary.md
 */

namespace TUI\Toolkit\QuoteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\QuoteBundle\Entity\Quote;
use TUI\Toolkit\QuoteBundle\Form\QuoteType;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Export\CSVExport;

/**
 * Quote controller.
 *
 */
class QuoteController extends Controller
{

    /**
     * Lists all unconverted Quote entities.
     *
     */
    public function indexAction(Request $request)
    {
     // list hidden columns
      $hidden = array(
        'isTemplate',
        'deleted',
        'converted',
        'setupComplete',
        'locked',
        'destination',
        'salesAgent.firstName',
        'salesAgent.lastName',
        'organizer.firstName',
        'organizer.lastName'
      );

     // Creates simple grid based on your entity (ORM)
      $source = new Entity('QuoteBundle:Quote');

      //add WHERE clause
      $tableAlias=$source->getTableAlias();
      $source->manipulateQuery(
        function ($query) use ($tableAlias)
        {
          $query->andWhere($tableAlias . '.converted = false');
        }
      );

      /* @var $grid \APY\DataGridBundle\Grid\Grid */
      $grid = $this->get('grid');

      // Attach the source to the grid
      $grid->setSource($source);
      $grid->setId('quotegrid');
      $grid->hideColumns($hidden);

      // Set the selector of the number of items per page
      $grid->setLimits(array(10, 25, 50, 100));

      // Export of the grid
      $grid->addExport(new CSVExport("Active Quotes as CSV", "activeQuotes", array('delimiter'=>','), "UTF-8", "ROLE_BRAND"));

      // Manage the grid redirection, exports and the response of the controller
      return $grid->getGridResponse('QuoteBundle:Quote:index.html.twig');

    }



  /**
   * Lists all CONVERTED Quote entities.
   *
   */
  public function convertedAction(Request $request)
  {
    // list hidden columns
    $hidden = array(
      'isTemplate',
      'deleted',
      'converted',
      'setupComplete',
      'locked',
      'destination'
    );

      // Creates simple grid based on your entity (ORM)
      $source = new Entity('QuoteBundle:Quote');

      //add WHERE clause
      $tableAlias=$source->getTableAlias();
      $source->manipulateQuery(
        function ($query) use ($tableAlias)
        {
          $query->andWhere($tableAlias . '.converted = true');
        }
      );

      /* @var $grid \APY\DataGridBundle\Grid\Grid */
      $grid = $this->get('grid');

      // Attach the source to the grid
      $grid->setSource($source);
      $grid->setId('quotegrid');
      $grid->hideColumns($hidden);

      // Set the selector of the number of items per page
      $grid->setLimits(array(10, 25, 50, 100));

      // Export of the grid
      $grid->addExport(new CSVExport("Converted Quotes as CSV", "convertedQuotes", array('delimiter'=>','), "UTF-8", "ROLE_BRAND"));


    // Manage the grid redirection, exports and the response of the controller
      return $grid->getGridResponse('QuoteBundle:Quote:converted.html.twig');
   }



  /**
   * Lists all DELETED Quote entities.
   *
   */
  public function showDeletedAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $filters = $em->getFilters();
    $filters->disable('softdeleteable');

    // list hidden columns
    $hidden = array(
      'isTemplate',
      'deleted',
      'converted',
      'setupComplete',
      'locked',
      'destination'
    );

    // Creates simple grid based on your entity (ORM)
    $source = new Entity('QuoteBundle:Quote');

    //add WHERE clause
    $tableAlias=$source->getTableAlias();
    $source->manipulateQuery(
      function ($query) use ($tableAlias)
      {
        $query->andWhere($tableAlias . '.deleted IS NOT NULL');
      }
    );

    /* @var $grid \APY\DataGridBundle\Grid\Grid */
    $grid = $this->get('grid');

    // Attach the source to the grid
    $grid->setSource($source);
    $grid->setId('quotegrid');
    $grid->hideColumns($hidden);

    // Set the selector of the number of items per page
    $grid->setLimits(array(10, 25, 50, 100));

    // Export of the grid
    $grid->addExport(new CSVExport("Deleted Quotes as CSV", "deletedQuotes", array('delimiter'=>','), "UTF-8", "ROLE_BRAND"));


    // Manage the grid redirection, exports and the response of the controller
    return $grid->getGridResponse('QuoteBundle:Quote:deleted.html.twig');
  }



  /**
   * Lists all DELETED Quote entities.
   *
   */
  public function templatesAction(Request $request)
  {
    // list hidden columns
    $hidden = array(
      'isTemplate',
      'deleted',
      'converted',
      'setupComplete',
      'locked',
      'destination'
    );

   // Creates simple grid based on your entity (ORM)
    $source = new Entity('QuoteBundle:Quote');

    //add WHERE clause
    $tableAlias=$source->getTableAlias();
    $source->manipulateQuery(
      function ($query) use ($tableAlias)
      {
        $query->andWhere($tableAlias . '.isTemplate = true');
      }
    );

    /* @var $grid \APY\DataGridBundle\Grid\Grid */
    $grid = $this->get('grid');

    // Attach the source to the grid
    $grid->setSource($source);
    $grid->setId('quotegrid');
    $grid->hideColumns($hidden);

    // Set the selector of the number of items per page
    $grid->setLimits(array(10, 25, 50, 100));

    // Export of the grid
    $grid->addExport(new CSVExport("Quote Templates as CSV", "templatesQuotes", array('delimiter'=>','), "UTF-8", "ROLE_BRAND"));


    // Manage the grid redirection, exports and the response of the controller
    return $grid->getGridResponse('QuoteBundle:Quote:templates.html.twig');
  }


    /**
     * Creates a new Quote entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Quote();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'Quote Saved: '. $entity->getName());
            return $this->redirect($this->generateUrl('manage_quote_show', array('id' => $entity->getId())));
        }

        return $this->render('QuoteBundle:Quote:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Quote entity.
     *
     * @param Quote $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Quote $entity)
    {
        $brandUsers = $this->getBrandUsers();
        $institutions = $this->getInstitutionList();

        $form = $this->createForm(new QuoteType(), $entity, array(
            'action' => $this->generateUrl('manage_quote_create'),
            'method' => 'POST',
        ));
        $form->add('salesAgent', 'choice', array(
          'placeholder' => 'Select',
        'choices' => $brandUsers,
          ));
      $form->add('institution', 'choice', array(
        'placeholder' => 'Select',
        'choices' => $institutions,
      ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        $choices = array();


        return $form;
    }

    /**
     * Displays a form to create a new Quote entity.
     *
     */
    public function newAction()
    {
        $entity = new Quote();
        $form   = $this->createCreateForm($entity);

        return $this->render('QuoteBundle:Quote:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Quote entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('QuoteBundle:Quote')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Quote entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('QuoteBundle:Quote:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Quote entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('QuoteBundle:Quote')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Quote entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('QuoteBundle:Quote:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Quote entity.
    *
    * @param Quote $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Quote $entity)
    {
        $form = $this->createForm(new QuoteType(), $entity, array(
            'action' => $this->generateUrl('manage_quote_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Quote entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('QuoteBundle:Quote')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Quote entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
          $this->get('session')->getFlashBag()->add('notice', 'Quote Saved: '. $entity->getName());

            return $this->redirect($this->generateUrl('manage_quote_edit', array('id' => $id)));
        }

        return $this->render('QuoteBundle:Quote:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Quote entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('QuoteBundle:Quote')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Quote entity.');
            }

            $em->remove($entity);
            $em->flush();
          $this->get('session')->getFlashBag()->add('notice', 'Quote Deleted: '. $entity->getName());
        }

        return $this->redirect($this->generateUrl('manage_quote'));
    }

    /**
     * Creates a form to delete a Quote entity by id.
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
      $entity = $em->getRepository('QuoteBundle:Quote')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find Quote entity.');
      }
      $entity->setDeleted(NULL);
      $em->persist($entity);
      $em->flush();
      $this->get('session')->getFlashBag()->add('notice', 'Quote Restored: '. $entity->getName());

    return $this->redirect($this->generateUrl('manage_quote'));
  }


  /**
   * Set Pagination Items.
   *
   */
  public function pageRangeAction(Request $request, $entities = NULL)
  {
    $query='';
    $pageRange = '10';
    $requestUrl = $_SERVER['REDIRECT_URL'];
    $query_str =  $_SERVER['QUERY_STRING'];
    if(!empty($query_str)){
      $query_arr =  explode('&', $query_str);
      foreach($query_arr as $key=>$attr){
        if(strpos($attr, 'pageRange') !== FALSE){
          $range=explode('=', $attr);
          $pageRange = $range[1];
        } else {
        $query.= '&' . $attr;
        }
      }
    }
    return $this->render('QuoteBundle:Quote:pagination.html.twig', array(
      'pageRange'      => $pageRange,
      'paginationUrl'  => $requestUrl,
      'query'       => $query,
      'entities'      => $entities,
    ));
  }



  /**
   * Creates a form to Restore a deleted Quote entity by id.
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
      ->getForm()
      ;
  }







  function getBrandUsers()
  {
    $choices=array();
    $em = $this->getDoctrine()->getManager();
    $qb = $em->createQueryBuilder('TUIToolkitUserBundle:User');
    $qb->select('u.id, u.username')
      ->from('TUIToolkitUserBundle:User', 'u')
      ->where(
        $qb->expr()->like('u.roles', '?1')
      )
      ->orderBy('u.username', 'ASC')
      ->setParameter(1, '%ROLE_BRAND%');
    $query = $qb->getQuery();
    $users = $query->getArrayResult();
    foreach($users as $user){
      $choices[$user['id']] = $user['username'];
    }
    return $choices;
  }

  function getInstitutionList()
  {
    $choices=array();
    $em = $this->getDoctrine()->getManager();
    $qb = $em->createQueryBuilder('InstitutionBundle:Institution');
    $qb->select('i.id, i.name')
      ->from('InstitutionBundle:Institution', 'i')
     // ->where(
     //   $qb->expr()->like('u.roles', '?1')
     // )
      ->orderBy('i.name', 'ASC')
    //  ->setParameter(1, '%ROLE_BRAND%')
    ;
    $query = $qb->getQuery();
    $institutions = $query->getArrayResult();
    foreach($institutions as $institution){
      $choices[$institution['id']] = $institution['name'];
    }
    return $choices;
  }
}
