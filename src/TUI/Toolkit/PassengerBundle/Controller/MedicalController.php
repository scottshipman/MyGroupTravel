<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Medical;
use TUI\Toolkit\PassengerBundle\Form\MedicalType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Medical controller.
 *
 */
class MedicalController extends Controller
{

    /**
     * Lists all Medical entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PassengerBundle:Medical')->findAll();

        return $this->render('PassengerBundle:Medical:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Medical entity.
     *
     */
    public function createAction(Request $request)
    {
        $reference = $request->request->get('tui_toolkit_passengerbundle_medical')['passengerReference'];

        if (empty($reference)) {
            throw $this->createAccessDeniedException('passengerReference is missing from the request.');
        }

        $em = $this->getDoctrine()->getManager();
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($reference);
        $passengerId = $passenger->getId();
        $tourId = $passenger->getTourReference()->getId();

        // Check context permissions.
        $this->get("permission.set_permission")->checkUserPermissionsMultiple(
          TRUE,
          array(
            array(
              'class' => 'passenger',
              'object' => $passengerId,
              'grants' => ['parent'],
              'role_override' => 'ROLE_BRAND',
            ),
            array(
              'class' => 'tour',
              'object' => $tourId,
              'grants' => ['organizer', 'assistant'],
              'role_override' => 'ROLE_BRAND',
            ),
          )
        );

        $entity = new Medical();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setPassengerReference($passenger);
            $em->persist($entity);
            $em->flush();

            $passenger->setMedicalReference($entity);
            $em->persist($passenger);
            $em->flush();

            return $this->createJsonResponse($entity);

        }

        $errors = $this->get("app.form.validation")->getErrorMessages($form);

        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');

        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;


    }

    /**
     * Creates a form to create a Medical entity.
     *
     * @param Medical $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Medical $entity)
    {
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new MedicalType($locale), $entity, array(
            'action' => $this->generateUrl('medical_create'),
            'method' => 'POST',
            'attr'  => array (
                'id' => 'ajax_new_medical_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Save'));

        return $form;
    }

    /**
     * Displays a form to create a new Medical entity.
     *
     */
    public function newAction()
    {
        $entity = new Medical();
        $form   = $this->createCreateForm($entity);

        return $this->render('PassengerBundle:Medical:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Medical entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Medical')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Medical entity.');
        }

        $passengerId = $entity->getPassengerReference()->getId();
        $tourId = $entity->getPassengerReference()->getTourReference()->getId();

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $tour_permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            $passenger_permission = $this->get("permission.set_permission")->getPermission($passengerId, 'passenger', $user->getId());
            $permission_pass = FALSE;

            if ($passenger_permission != NULL && in_array('parent', $passenger_permission)) {
                $permission_pass = TRUE;
            }

            if ($tour_permission != NULL && (in_array('organizer', $tour_permission) || in_array('assistant', $tour_permission))) {
                $permission_pass = TRUE;
            }

            if (!$permission_pass) {
                throw $this->createAccessDeniedException();
            }
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PassengerBundle:Medical:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Medical entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Medical')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Medical entity.');
        }

        $passengerId = $entity->getPassengerReference()->getId();
        $tourId = $entity->getPassengerReference()->getTourReference()->getId();

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $tour_permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            $passenger_permission = $this->get("permission.set_permission")->getPermission($passengerId, 'passenger', $user->getId());
            $permission_pass = FALSE;

            if ($passenger_permission != NULL && in_array('parent', $passenger_permission)) {
                $permission_pass = TRUE;
            }

            if ($tour_permission != NULL && (in_array('organizer', $tour_permission) || in_array('assistant', $tour_permission))) {
                $permission_pass = TRUE;
            }

            if (!$permission_pass) {
                throw $this->createAccessDeniedException();
            }
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PassengerBundle:Medical:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Medical entity.
    *
    * @param Medical $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Medical $entity)
    {
        $locale = $this->container->getParameter('locale');

        $form = $this->createForm(new MedicalType($locale), $entity, array(
            'action' => $this->generateUrl('medical_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'  => array (
                'id' => 'ajax_medical_form',
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Medical entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Medical')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Medical entity.');
        }

        $passengerId = $entity->getPassengerReference()->getId();
        $tourId = $entity->getPassengerReference()->getTourReference()->getId();

        // Check context permissions.
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('ROLE_BRAND')) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $tour_permission = $this->get("permission.set_permission")->getPermission($tourId, 'tour', $user->getId());
            $passenger_permission = $this->get("permission.set_permission")->getPermission($passengerId, 'passenger', $user->getId());
            $permission_pass = FALSE;

            if ($passenger_permission != NULL && in_array('parent', $passenger_permission)) {
                $permission_pass = TRUE;
            }

            if ($tour_permission != NULL && (in_array('organizer', $tour_permission) || in_array('assistant', $tour_permission))) {
                $permission_pass = TRUE;
            }

            if (!$permission_pass) {
                throw $this->createAccessDeniedException();
            }
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $passengerId = $entity->getPassengerReference();
//        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);


        if ($editForm->isValid()) {
            $em->flush();

            return $this->createJsonResponse($entity);

        }

        $errors = $this->get("app.form.validation")->getErrorMessages($editForm);


        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');

        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;
    }
    /**
     * Deletes a Medical entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PassengerBundle:Medical')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Medical entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('medical'));
    }

    /**
     * Creates a form to delete a Medical entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('medical_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Given the entity, build a JSON response for the front end to use
     * @param Medical $entity
     * @return JsonResponse
     */
    private function createJsonResponse(Medical $entity)
    {
        return new JsonResponse(array(
        'id' => $entity->getId(),
        'name' => $entity->getDoctorName(),
        'number' => $entity->getDoctorNumber(),
        'conditions' => $entity->getConditions(),
        'medications' => $entity->getMedications()
    ));
    }

}
