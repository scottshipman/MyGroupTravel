<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Passport;
use TUI\Toolkit\PassengerBundle\Form\PassportType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Passport controller.
 *
 */
class PassportController extends Controller
{

    /**
     * Lists all Passport entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PassengerBundle:Passport')->findAll();

        return $this->render('PassengerBundle:Passport:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Passport entity.
     *
     */
    public function createAction(Request $request)
    {
        $reference = $request->request->get('tui_toolkit_passengerbundle_passport')['passengerReference'];

        if (empty($reference)) {
            throw $this->createAccessDeniedException('passengerReference is missing from the request.');
        }

        $entity = new Passport();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $passenger = $em->getRepository('PassengerBundle:Passenger')->find($reference);
            $entity->setPassengerReference($passenger);
            $em->persist($entity);
            $em->flush();

            $passenger->setPassportReference($entity);
            $em->persist($passenger);
            $em->flush();

            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('passenger.form.success.message.passport'));


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
     * Creates a form to create a Passport entity.
     *
     * @param Passport $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Passport $entity)
    {
        $locale = $this->container->getParameter('locale');
        $form = $this->createForm(new PassportType($locale), $entity, array(
            'action' => $this->generateUrl('passport_create'),
            'method' => 'POST',
            'attr'  => array (
                'id' => 'ajax_new_passport_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Save'));

        return $form;
    }

    /**
     * Displays a form to create a new Passport entity.
     *
     */
    public function newAction()
    {
        $entity = new Passport();
        $form   = $this->createCreateForm($entity);

        return $this->render('PassengerBundle:Passport:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Passport entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passport')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passport entity.');
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

        return $this->render('PassengerBundle:Passport:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Passport entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passport')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passport entity.');
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

        return $this->render('PassengerBundle:Passport:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Passport entity.
    *
    * @param Passport $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Passport $entity)
    {
        $locale = $this->container->getParameter('locale');

        $form = $this->createForm(new PassportType($locale), $entity, array(
            'action' => $this->generateUrl('passport_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'  => array (
                'id' => 'ajax_new_passport_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Passport entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $locale = $this->container->getParameter('locale');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Passport')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Passport entity.');
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

            if ($locale == "en_GB") {
                //formatted date of birth
                $dateOfBirth = $entity->getPassportDateOfBirth()->format('d M Y');
                //formatted date of issue
                $dateOfIssue = $entity->getPassportDateOfIssue()->format('d M Y');
                //formatted date of expiry
                $dateOfExpiry = $entity->getPassportDateOfExpiry()->format('d M Y');
            }else {
                //formatted date of birth
                $dateOfBirth = $entity->getPassportDateOfBirth()->format('M d Y');

                $dateOfIssue = $entity->getPassportDateOfIssue()->format('M d Y');
                //formatted date of expiry
                $dateOfExpiry = $entity->getPassportDateOfExpiry()->format('M d Y');
            }


            $data = array(
                $entity->getPassportLastName(),
                $entity->getPassportFirstName(),
                $entity->getPassportMiddleName(),
                $entity->getPassportGender(),
                $entity->getPassportTitle(),
                $entity->getPassportIssuingState(),
                $entity->getPassportNumber(),
                $entity->getPassportNationality(),
                $dateOfBirth,
                $dateOfIssue,
                $dateOfExpiry,
        );
            $em->persist($entity);
            $em->flush();



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
    /**
     * Deletes a Passport entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PassengerBundle:Passport')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Passport entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('passport'));
    }

    /**
     * Creates a form to delete a Passport entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('passport_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
