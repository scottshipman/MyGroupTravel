<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\PermissionBundle\Entity\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;
use TUI\Toolkit\PassengerBundle\Form\TourPassengerType;


/**
 * Passenger controller.
 *
 */
class PassengerController extends Controller
{

    /**
     * Lists all Passenger entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PassengerBundle:Passenger')->findAll();

        return $this->render('PassengerBundle:Passenger:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new Passenger entity.
     *
     */
    public function createAction(Request $request, $tourId)
    {
        $entity = new Passenger();
        $form = $this->createCreateForm($entity, $tourId);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $userEmail = $form->get('email')->getData();
            $user = $em->getRepository('TUIToolkitUserBundle:User')->findOneByEmail($userEmail);
            $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

            if (!$user) {
                //do some stuff
                $user = new User();
                $user->setUsername($form->get('email')->getData());
                $user->setPassword('');
                $user->setEmail($form->get('email')->getData());
                $user->setFName($form->get('fName')->getData());
                $user->setLName($form->get('lName')->getData());
                $user->setRoles(array('ROLE_CUSTOMER'));
                $em->persist($user);
                $em->flush();
            }

            foreach ($form->get('passengers') as $passenger) {
                //do more stuff
                $newPassenger = new Passenger();
                $newPassenger->setDateOfBirth($passenger->get('dateOfBirth')->getData());
                $newPassenger->setFName($passenger->get('fName')->getData());
                $newPassenger->setGender($passenger->get('gender')->getData());
                $newPassenger->setLName($passenger->get('lName')->getData());
                $newPassenger->setStatus("waitlist");
                $newPassenger->setSignUpDate(new \DateTime());
                $newPassenger->setTourReference($tour);
                $em->persist($newPassenger);
                $em->flush();

                $permission = new Permission();
                $permission->setClass('passenger');
                $permission->setObject($newPassenger->getId());
                $permission->setGrants('parent');
                $permission->setUser($user);
                $em->persist($permission);
                $em->flush();
            }

            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('passenger.flash.save'));


            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        $template = $this->renderView('PassengerBundle:Passenger:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));

        $response = new Response($template);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    /**
     * Creates a form to create a Passenger entity.
     *
     * @param Passenger $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Passenger $entity, $tourId)
    {
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new TourPassengerType($locale), $entity, array(
            'action' => $this->generateUrl('manage_passenger_create', array("tourId" => $tourId)),
            'method' => 'POST',
            'attr'  => array (
                'id' => 'ajax_passenger_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Passenger entity.
     *
     */
    public function newAction($tourId)
    {
        $entity = new Passenger();
        $form = $this->createCreateForm($entity, $tourId);
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');

        return $this->render('PassengerBundle:Passenger:new.html.twig', array(
            'entity' => $entity,
            'locale' => $locale,
            'date_format' => $date_format,
            'tourId' => $tourId,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Passenger entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passenger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passenger entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PassengerBundle:Passenger:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Passenger entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passenger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passenger entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PassengerBundle:Passenger:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Passenger entity.
     *
     * @param Passenger $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Passenger $entity)
    {
        $form = $this->createForm(new PassengerType(), $entity, array(
            'action' => $this->generateUrl('manage_passenger_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Passenger entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passenger')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passenger entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('manage_passenger_edit', array('id' => $id)));
        }

        return $this->render('PassengerBundle:Passenger:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Passenger entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PassengerBundle:Passenger')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Passenger entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('manage_passenger'));
    }

    /**
     * Creates a form to delete a Passenger entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manage_passenger_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }

    public function getPassengerDashboardAction($tourId)
    {
        $em = $this->getDoctrine()->getManager();
        $tour = $em->getRepository('TourBundle:Tour')->find($tourId);

        //Query builder for waitlist
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('PassengerBundle:Passenger', 'p')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('p.status', '?1')
            ));
        $qb->setParameters(array(1 => 'waitlist' ));
        $query = $qb->getQuery();
        $waitList = $query->getScalarResult();

        $waitListObjects = array();

        foreach($waitList as $passenger) {
            $object = $passenger['p_id'];
            $parent = $this->get("permission.set_permission")->getUser('parent', $object, 'passenger');
            $parentObject = $em->getRepository('TUIToolkitUserBundle:User')->find($parent[1]);
            $waitListObjects[]= array($passenger, $parentObject);
        }



        //Query builder for accepted
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('PassengerBundle:Passenger', 'p')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('p.status', '?1')
            ));
        $qb->setParameters(array(1 => 'accepted' ));
        $query = $qb->getQuery();
        $accepted = $query->getScalarResult();

        $acceptedObjects = array();
        foreach($accepted as $passenger) {
            $object = $passenger['p_id'];
            $parent = $this->get("permission.set_permission")->getUser('parent', $object, 'passenger');
            $parentObject = $em->getRepository('TUIToolkitUserBundle:User')->find($parent[1]);
            $acceptedObjects[]= array($passenger, $parentObject);
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

        return $this->render('PassengerBundle:Passenger:dashboard.html.twig', array(
            'tour' => $tour,
            'waitlistobjects' => $waitListObjects,
            'acceptedobjects' => $acceptedObjects,
            'brand' => $brand,
        ));
    }
}
