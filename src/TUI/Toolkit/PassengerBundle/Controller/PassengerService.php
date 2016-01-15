<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;

use Symfony\Component\Form\Form;

/**
 * Permission Service controller.
 *
 */
class PassengerService
{

    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
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

        foreach($results as $result){
            if($result->getUser())
            {$organizers[] = $result->getUser();
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



}