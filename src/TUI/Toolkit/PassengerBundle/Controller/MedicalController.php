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
        $entity = new Medical();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $reference = $form['passengerReference']->getData();


        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $passenger = $em->getRepository('PassengerBundle:Passenger')->find($reference);
            $entity->setPassengerReference($passenger);
            $em->persist($entity);
            $em->flush();

            $passenger->setMedicalReference($entity);
            $em->persist($passenger);
            $em->flush();

            $data = array(
                $entity->getDoctorName(),
                $entity->getDoctorNumber(),
                $entity->getConditions(),
                $entity->getMedications(),
            );

//            Decided to remove the ajax form from the create form
//            $responseContent =  json_encode($data);
//            return new Response($responseContent,
//                Response::HTTP_OK,
//                array('content-type' => 'application/json')
//            );

            $this->get('ras_flash_alert.alert_reporter')->addSuccess($this->get('translator')->trans('passenger.form.success.message.medical'));
            return $this->redirect($this->generateUrl('manage_passenger_show', array('id' => $passenger->getId())));

        }

        $errorString = "";
        $translator = $this->get('translator');
        $errors = $this->get("passenger.actions")->getErrorMessages($form);

        $errorString = $this->get("passenger.actions")->getFlashErrorMessages($errors, $form, $translator);

        $this->get('ras_flash_alert.alert_reporter')->addError($this->get('translator')->trans('passenger.form.error.message.medical')." ".$errorString);

        return $this->redirect($this->generateUrl('manage_passenger_show', array('id' => $reference)));

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
                'id' => 'ajax_medical_form'
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

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $passengerId = $entity->getPassengerReference();
//        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($passengerId);


        if ($editForm->isValid()) {
            $em->flush();

            $doctorNumber = $entity->getDoctorNumber();
            $doctorNumberFormatted = $doctorNumber->getNationalNumber();

            $data = array(
                $entity->getDoctorName(),
                $doctorNumberFormatted,
                $entity->getConditions(),
                $entity->getMedications(),
            );

            $responseContent =  json_encode($data);
            return new Response($responseContent,
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );


//            return $this->redirect($this->generateUrl('manage_passenger_show', array('id' => $passenger->getId())));

        }

        $errors = $this->get("passenger.actions")->getErrorMessages($editForm);


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
}
