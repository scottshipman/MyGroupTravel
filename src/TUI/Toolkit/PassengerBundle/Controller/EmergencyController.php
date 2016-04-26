<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Emergency;
use TUI\Toolkit\PassengerBundle\Form\EmergencyType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Form\Form;


/**
 * Emergency controller.
 *
 */
class EmergencyController extends Controller
{

    /**
     * Lists all Emergency entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PassengerBundle:Emergency')->findAll();

        return $this->render('PassengerBundle:Emergency:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Emergency entity.
     *
     */
    public function createAction(Request $request)
    {
        $reference = $request->request->get('tui_toolkit_passengerbundle_emergency')['passengerReference'];

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

        $entity = new Emergency();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setPassengerReference($passenger);
            $em->persist($entity);
            $em->flush();

            $passenger->setEmergencyReference($entity);
            $em->persist($passenger);
            $em->flush();


            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('passenger.form.success.message.emergency'));

            return $this->redirect($this->generateUrl('manage_passenger_show', array('id' => $passenger->getId())));

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
     * Creates a form to create a Emergency entity.
     *
     * @param Emergency $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Emergency $entity)
    {
        $locale = $this->container->getParameter('locale');

        $form = $this->createForm(new EmergencyType($locale), $entity, array(
            'action' => $this->generateUrl('emergency_create'),
            'method' => 'POST',
            'attr'  => array (
                'id' => 'ajax_new_emergency_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Save'));

        return $form;
    }

    /**
     * Displays a form to create a new Emergency entity.
     *
     */
    public function newAction()
    {
        $entity = new Emergency();
        $form   = $this->createCreateForm($entity);

        return $this->render('PassengerBundle:Emergency:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Emergency entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Emergency')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Emergency entity.');
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

        return $this->render('PassengerBundle:Emergency:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Emergency entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Emergency')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Emergency entity.');
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

        return $this->render('PassengerBundle:Emergency:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Emergency entity.
    *
    * @param Emergency $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Emergency $entity)
    {
        $locale = $this->container->getParameter('locale');

        $form = $this->createForm(new EmergencyType($locale), $entity, array(
            'action' => $this->generateUrl('emergency_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'  => array (
                'id' => 'ajax_emergency_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Emergency entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Emergency')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Emergency entity.');
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

        if ($editForm->isValid()) {
            $em->flush();

            $emergencyNumber = $entity->getEmergencyNumber();
//            $emergencyNumberFormatted = $emergencyNumber->getNationalNumber();

            $data = array(
                $entity->getEmergencyName(),
                $entity->getEmergencyRelationship(),
                $emergencyNumber,
                $entity->getEmergencyEmail(),
            );

            $responseContent =  json_encode($data);
            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );


        }

        $errors = $this->get("app.form.validation")->getErrorMessages($editForm);


        $serializer = $this->container->get('jms_serializer');
        $errors = $serializer->serialize($errors, 'json');

        $response = new Response($errors);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode('400');
        return $response;
    }

    public function getErrorMessages(Form $form) {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }


    /**
     * Deletes a Emergency entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PassengerBundle:Emergency')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Emergency entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('emergency'));
    }

    /**
     * Creates a form to delete a Emergency entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('emergency_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
