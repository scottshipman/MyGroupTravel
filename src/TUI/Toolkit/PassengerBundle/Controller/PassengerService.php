<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;

use Symfony\Component\Form\Form;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * Passenger Service controller.
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

        $travellingUsers = array();

        $passengerData = (object) [];
        //Get Waitlist Passengers
        $waitListUsers = $this->getPassengersByStatus('waitlist', $id);
        $waitlist = count($waitListUsers);
        $passengerData->waitlist = $waitlist;

        //Get Accepted Passengers
        $acceptedUsers = $this->getPassengersByStatus('accepted', $id);
        $accepted = count($acceptedUsers);
        if (!empty($acceptedUsers)) {
            $travellingUsers = array_merge($travellingUsers, $acceptedUsers);
        }

        //Get free passengers
        $freeUsers = $this->getPassengersByStatus('free', $id);
        $free = count($freeUsers);
        $passengerData->free = $free;
        if (!empty($freeUsers)) {
            $travellingUsers = array_merge($travellingUsers, $freeUsers);
        }

        //Get Organizers
        $organizerCount = $container->get("permission.set_permission")->getUser('organizer', $tour->getId(), 'tour');
        $assistantCount = $container->get("permission.set_permission")->getUser('assistant', $tour->getId(), 'tour');
        $totalOrganizerCount = count($organizerCount) + count($assistantCount);
        $passengerData->totalOrganizerCount = $totalOrganizerCount;

        foreach ($travellingUsers as $travellingUser){
           $parent = $container->get("permission.set_permission")->getUser('parent', $travellingUser->getId(), 'passenger');
            if (!empty($parent)){
                $parentObject = $em->getRepository('TUIToolkitUserBundle:User')->find($parent[1]);
            } else {
                $parentObject = "";
            }
            $travellingUser->parent = $parentObject;
        }

//        $travellingUsers = from($travellingUsers)->orderBy('')
        $passengerData->travellingUsers = $travellingUsers;



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
        foreach ($travellingUsers as $travellingUser) {

            if ($travellingUser->getMedicalReference() != null) {
                $completedPassengerData['medical'][] = $travellingUser;
            }
            if ($travellingUser->getDietaryReference() != null) {
                $completedPassengerData['dietary'][] = $travellingUser;
            }
            if ($travellingUser->getEmergencyReference() != null) {
                $completedPassengerData['emergency'][] = $travellingUser;
            }
            if ($travellingUser->getPassportReference() != null) {
                $completedPassengerData['passport'][] = $travellingUser;
            }


        }

        $totalCompletedTasks = array();

        if ($completedPassengerData['medical'] == count($travellingUsers)) {
            $totalCompletedTasks[] = $completedPassengerData['medical'];
        }

        if ($completedPassengerData['dietary'] == count($travellingUsers)) {
            $totalCompletedTasks[] = $completedPassengerData['dietary'];
        }

        if ($completedPassengerData['emergency'] == count($travellingUsers)) {
            $totalCompletedTasks[] = $completedPassengerData['emergency'];
        }

        if ($completedPassengerData['passport'] == count($travellingUsers)) {
            $totalCompletedTasks[] = $completedPassengerData['passport'];
        }

        $totalCompletedTasksCount = count($totalCompletedTasks);
        $passengerData->totalCompletedTasksCount = $totalCompletedTasksCount;


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
            $possibleTasks['medical'] = $medicalTask;
        }
        if ($tour->getDietaryDate() != null){
            $possibleTasks['dietary'] = $dietaryTask;
        }
        if ($tour->getEmergencyDate() != null) {
            $possibleTasks['emergency'] = $emergencyTask;
        }
        if ($tour->getPassportDate() != null){
            $possibleTasks['passport'] = $passportTask;
        }

        $possibleTasksCount = count($possibleTasks);

        return $possibleTasks;


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
            $possibleTasks['medical'] = $medicalTask;
        }
        if ($passenger->getDietaryReference() != null){
            $possibleTasks['dietary'] = $dietaryTask;
        }
        if ($passenger->getEmergencyReference() != null) {
            $possibleTasks['emergency'] = $emergencyTask;
        }
        if ($passenger->getPassportReference() != null){
            $possibleTasks['passport'] = $passportTask;
        }

        $possibleTasksCount = count($possibleTasks);

        return $possibleTasks;
    }

    /**
     * @param $passengers
     * @param $em
     * @return array
     */

    public function addPassengerParents($passengers, $em)
    {
        $combinedObjects = array();
        $container = $this->container;

        if (empty($passengers)) {
            return array();
        }

        foreach($passengers as $passenger) {
            $object = $passenger->getId();
            $tourId = $passenger->getTourReference()->getId();
            $parent = $container->get("permission.set_permission")->getUser('parent', $object, 'passenger');

            if (!empty($parent)){
                $parentObject = $em->getRepository('TUIToolkitUserBundle:User')->find($parent[1]);
            } else {
                $parentObject = "";
            }
            $isOrganizer = $container->get("permission.set_permission")->getPermission($tourId, 'tour', $parentObject)[0]=='organizer' ? TRUE : FALSE;
            $isOrganizer = $container->get("permission.set_permission")->getPermission($tourId, 'tour', $parentObject)[0]=='assistant' ? TRUE : $isOrganizer;


            $combinedObjects[]= array($passenger, $parentObject, $isOrganizer);
        }

        return $combinedObjects;
    }

    /**
     * @param $passengers
     * @return array
     */

    public function getParticipantCounts($passengers)
    {
        $count=array(
            'organizer'=>array(
                'accepted' => 0,
                'waitlist' => 0,
                'free' => 0,
            ),
            'passenger' => array(
                'accepted' => 0,
                'waitlist' => 0,
                'free' => 0,
            ),
        );

        foreach($passengers as $passenger) {
            //loop to see if organizer
            if($passenger[2]===true){
                $category = 'organizer';
            } else {
                $category = 'passenger';
            }

            if($passenger[0]->getStatus() == 'accepted' && $passenger[0]->getFree() == FALSE) {
                $count[$category]['accepted'] ++;
            }
            if($passenger[0]->getStatus() == 'waitlist') {
                $count[$category]['waitlist'] ++;
            }
            if($passenger[0]->getFree() == TRUE) {
                $count[$category]['free'] ++;
            }
        }

        return $count;
    }

    /**
     * Helper function to build array of organizer, passenger and boolean organizer flag
     * @param organizerList, entity manager
     * @return array
     *
     */
    public function addOrganizerPassengers($organizers, $tourId, $em)
    {
        $combinedObjects = array();
        $container = $this->container;

        if (empty($organizers)) {
            return NULL;
        }

        foreach($organizers as $organizer) {

            $passengerObject = new Passenger();
            $passengerObject->setStatus('Pending Invite');

            $user = $organizer->getId();
            $passenger = $container->get("permission.set_permission")->getObject('parent', $user, 'passenger');
            $isOrganizer = TRUE;

            if (!empty($passenger)){
                // could be more than one passenger object
                foreach($passenger as $pax){
                    $paxObject = $em->getRepository('PassengerBundle:Passenger')->find($pax['object']);
                    if( $paxObject &&
                        /*strtolower(trim($organizer->getFirstName())) == strtolower(trim($paxObject->getFName())) &&
                        strtolower(trim($organizer->getLastName())) == strtolower(trim($paxObject->getLName())) &&*/
                        $paxObject->getSelf() == TRUE &&
                        $tourId == $paxObject->getTourReference()->getId())
                    {
                        $passengerObject = $paxObject;
                        $break;
                    }
                }

            } else {
                // need to add name data to fake passenger data
                $passengerObject->setfName($organizer->getFirstName());
                $passengerObject->setlname($organizer->getLastName());

            }
            $combinedObjects[]= array($passengerObject, $organizer, $isOrganizer);
        }

        return $combinedObjects;
    }

    public function getUnActivatedUsers($tourId){
        $container = $this->container;
        $em = $this->em;
        $unactivated = array();

        //Get Accepted Passengers
        $acceptedUsers = $this->getPassengersByStatus('accepted', $tourId);
        $accepted = count($acceptedUsers);

        foreach ($acceptedUsers as $acceptedUser){
            $object = $acceptedUser->getId();
            $users = $container->get("permission.set_permission")->getUser('parent', $object, 'passenger');

            foreach ($users as $user) {
                $user = $em->getRepository('TUIToolkitUserBundle:User')->find($user);
                if ($user->isEnabled() == false) {
                    $unactivated[] = $user;
                }
            }
        }

        $uanctivatedCount = count($unactivated);
        return $uanctivatedCount;
    }

}