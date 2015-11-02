<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TUI\Toolkit\UserBundle\Entity\User;
use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\PermissionBundle\Entity\Permission;



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

            if (!$user){
                //do some stuff
                $user = new User();
                $user->setUsername($form->get('email')->getData());
                $user->setPassword('');
                $user->setEmail($form->get('email')->getData());
                $user->setFirstName($form->get('firstName')->getData());
                $user->setLastName($form->get('lastName')->getData());
                $user->setRoles(array('ROLE_CUSTOMER'));
                $em->persist($user);
                $em->flush();
            }

            foreach ($form->get('passengers') as $passenger){
                //do more stuff
                $newPassenger = new Passenger();
                $newPassenger->setDateOfBirth($passenger->get('dateOfBirth')->getData());
                $newPassenger->setFirstName($passenger->get('firstName')->getData());
                $newPassenger->setGender($passenger->get('gender')->getData());
                $newPassenger->setLastName($passenger->get('lastName')->getData());
                $newPassenger->setStatus("waitlist");
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

            return $this->redirect($this->generateUrl('tour_site_action_show', array('id' => $tourId)));
        }

        return $this->render('PassengerBundle:Passenger:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
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
        $form   = $this->createCreateForm($entity, $tourId);
        $date_format = $this->container->getParameter('date_format');
        $locale = $this->container->getParameter('locale');

        return $this->render('PassengerBundle:Passenger:new.html.twig', array(
            'entity' => $entity,
            'locale' => $locale,
            'date_format' =>$date_format,
            'tourId' => $tourId,
            'form'   => $form->createView(),
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
            'entity'      => $entity,
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
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
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
            ->getForm()
        ;
    }
}
