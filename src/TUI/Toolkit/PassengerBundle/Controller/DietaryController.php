<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Dietary;
use TUI\Toolkit\PassengerBundle\Form\DietaryType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Dietary controller.
 *
 */
class DietaryController extends Controller
{

    /**
     * Lists all Dietary entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PassengerBundle:Dietary')->findAll();

        return $this->render('PassengerBundle:Dietary:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Dietary entity.
     *
     */
    public function createAction(Request $request)
    {
        $reference = $request->request->get('tui_toolkit_passengerbundle_dietary')['passengerReference'];

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

        $entity = new Dietary();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setPassengerReference($passenger);
            $em->persist($entity);
            $em->flush();

            $passenger->setDietaryReference($entity);
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
     * Creates a form to create a Dietary entity.
     *
     * @param Dietary $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Dietary $entity)
    {
        $form = $this->createForm(new DietaryType(), $entity, array(
            'action' => $this->generateUrl('dietary_create'),
            'method' => 'POST',
            'attr'  => array (
                'id' => 'ajax_new_dietary_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Save'));

        return $form;
    }

    /**
     * Displays a form to create a new Dietary entity.
     *
     */
    public function newAction()
    {
        $entity = new Dietary();
        $form   = $this->createCreateForm($entity);

        return $this->render('PassengerBundle:Dietary:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Dietary entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Dietary')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dietary entity.');
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

        return $this->render('PassengerBundle:Dietary:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Dietary entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Dietary')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dietary entity.');
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

        return $this->render('PassengerBundle:Dietary:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Dietary entity.
    *
    * @param Dietary $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Dietary $entity)
    {
        $form = $this->createForm(new DietaryType(), $entity, array(
            'action' => $this->generateUrl('dietary_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr'  => array (
                'id' => 'ajax_dietary_form'
            ),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Dietary entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PassengerBundle:Dietary')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dietary entity.');
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
     * Deletes a Dietary entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PassengerBundle:Dietary')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Dietary entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('dietary'));
    }

    /**
     * Creates a form to delete a Dietary entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('dietary_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Given the entity, build a JSON response for the front end to use
     *
     * @param $entity
     * @return JsonResponse
     */
    private function createJsonResponse(Dietary $entity)
    {
        return new JsonResponse(array(
            'id' => $entity->getId(),
            'description' => $entity->getDescription()
        ));
    }
}
