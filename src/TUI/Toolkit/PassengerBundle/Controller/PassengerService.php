<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;

use Symfony\Component\Form\Form;
use Symfony\Component\DependencyInjection\Container;

/**
 * Permission Service controller.
 *
 */
class PassengerService
{

    protected $em;
    protected $container;

    public function __construct(\Doctrine\ORM\EntityManager $em, Container $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * @param $status
     * @param $tourId
     * @return array
     */
    public function getPassengersByStatus($status, $tourId){

        // special query case for free status for boolean field
        $statusExpr = $status == 'free' ? 'p.free' : 'p.status';
        $status = $status == 'free' ? 1 : $status;

        //Query builder for passengers
        $em = $this->em;
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('PassengerBundle:Passenger', 'p')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('p.tourReference', '?1')
            ));
        $qb->setParameter(1, $tourId );
        if($status != 'all'){
            $qb->andWhere($qb->expr()->andX(
                $qb->expr()->eq($statusExpr, '?2')
            ));
            $qb->setParameter(2,  $status);
        }
        $qb->orderBy('p.signUpDate', 'DESC');
        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }


    /*
     * Get Pending Invite Organizers
     */
    public function getOrganizers($tourId){

        $organizers=array();
        //Query builder for organizers (assistants)
        $em = $this->em;
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('PermissionBundle:Permission', 'p')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('p.class', '?1'),
                $qb->expr()->eq('p.grants', '?2'),
                $qb->expr()->eq('p.object', '?3')
            ));
        $qb->setParameters(array(1 => 'tour', 2 => 'assistant', 3 => $tourId ));
        $query = $qb->getQuery();
        $results = $query->getResult();

        foreach($results as $result) {
            if ($orgUser = $result->getUser()) {
                if ($orgUser->isEnabled() == FALSE) {
                    $organizers[] = $result->getUser();
                }
            }
        }

        return $organizers;
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

    public function getFlashErrorMessages($errors, $form, $translator){

        $errorCollection = array();

        foreach ($errors as $key => $error) {
            $formLabel = $form->get($key)->getConfig()->getOption('label');
            $translatedLabel = $translator->trans($formLabel);
            $errorCollection[] = $translatedLabel;
        }

        $errorString = implode($errorCollection, ', ');

        return $errorString;
    }

    /**
     * @param $id tour Id
     * @return object is stdObject()
     */
    public function getTourPassengersData($id){

        $em = $this->em;
        $container = $this->container;

        $tour = $em->getRepository('TourBundle:Tour')->find($id);

        $passengerData = (object) [];
        //Get Waitlist Passengers
        $waitListUsers = $this->getPassengersByStatus('waitlist', $id);
        $waitlist = count($waitListUsers);
        $passengerData->waitlist = $waitlist;

        //Get Accepted Passengers
        $acceptedUsers = $this->getPassengersByStatus('accepted', $id);
        $accepted = count($acceptedUsers);

        //Get free passengers
        $free = $this->getPassengersByStatus('free', $id);
        $free = count($free);
        $passengerData->free = $free;

        //Get Organizers
        $organizerCount = $container->get("permission.set_permission")->getUser('organizer', $tour->getId(), 'tour');
        $assistantCount = $container->get("permission.set_permission")->getUser('assistant', $tour->getId(), 'tour');
        $totalOrganizerCount = count($organizerCount) + count($assistantCount);
        $passengerData->totalOrganizerCount = $totalOrganizerCount;



        //Real Accepted Passenger Count
        $accepted = $accepted - $free;
        $passengerData->accepted = $accepted;

        $completedPassengerData = array();
        $medical = array();
        $dietary = array();
        $emergency = array();
        $passport = array();

        $completedPassengerData = array('medical' => $medical, 'dietary' => $dietary, 'emergency' => $emergency, 'passport' => $passport);

        //Get Accepted Users with completed medical information
        foreach ($acceptedUsers as $acceptedUser) {

            if ($acceptedUser->getMedicalReference() != null) {
                $completedPassengerData['medical'][] = $acceptedUser;
            }
            if ($acceptedUser->getDietaryReference() != null) {
                $completedPassengerData['dietary'][] = $acceptedUser;
            }
            if ($acceptedUser->getEmergencyReference() != null) {
                $completedPassengerData['emergency'][] = $acceptedUser;
            }
            if ($acceptedUser->getPassportReference() != null) {
                $completedPassengerData['passport'][] = $acceptedUser;
            }


        }
        $passengerData->completedPassengerData = $completedPassengerData;

        $possibleTasks = array();

        $medicalTask = $tour->getMedicalDate();
        $dietaryTask = $tour->getDietaryDate();
        $emergencyTask = $tour->getEmergencyDate();
        $passportTask = $tour->getPassportDate();

        if ($tour->getMedicalDate() != null) {
            $possibleTasks[] = $medicalTask;
        }
        if ($tour->getDietaryDate() != null){
            $possibleTasks[] = $dietaryTask;
        }
        if ($tour->getEmergencyDate() != null) {
            $possibleTasks[] = $emergencyTask;
        }
        if ($tour->getPassportDate() != null){
            $possibleTasks[] = $passportTask;
        }
        $possibleTasksCount = count($possibleTasks);
        $passengerData->possibleTasksCount = $possibleTasksCount;

        return $passengerData;


    }

    /**
     * @param $id tour id
     * @return int count
     */
    public function getPossibleTourTasks($id)
    {

        $em = $this->em;
        $tour = $em->getRepository('TourBundle:Tour')->find($id);

        $possibleTasks = array();

        $medicalTask = $tour->getMedicalDate();
        $dietaryTask = $tour->getDietaryDate();
        $emergencyTask = $tour->getEmergencyDate();
        $passportTask = $tour->getPassportDate();

        if ($tour->getMedicalDate() != null) {
            $possibleTasks[] = $medicalTask;
        }
        if ($tour->getDietaryDate() != null){
            $possibleTasks[] = $dietaryTask;
        }
        if ($tour->getEmergencyDate() != null) {
            $possibleTasks[] = $emergencyTask;
        }
        if ($tour->getPassportDate() != null){
            $possibleTasks[] = $passportTask;
        }

        $possibleTasksCount = count($possibleTasks);

        return $possibleTasksCount;


    }

    /**
     * @param $id passenger id
     * @return int count
     */
    public function getPassengerCompletedTasks($id) {

        $em = $this->em;
        $passenger = $em->getRepository('PassengerBundle:Passenger')->find($id);

        $possibleTasks = array();

        $medicalTask = $passenger->getMedicalReference();
        $dietaryTask = $passenger->getDietaryReference();
        $emergencyTask = $passenger->getEmergencyReference();
        $passportTask = $passenger->getPassportReference();

        if ($passenger->getMedicalReference() != null) {
            $possibleTasks[] = $medicalTask;
        }
        if ($passenger->getDietaryReference() != null){
            $possibleTasks[] = $dietaryTask;
        }
        if ($passenger->getEmergencyReference() != null) {
            $possibleTasks[] = $emergencyTask;
        }
        if ($passenger->getPassportReference() != null){
            $possibleTasks[] = $passportTask;
        }

        $possibleTasksCount = count($possibleTasks);

        return $possibleTasksCount;
    }

}