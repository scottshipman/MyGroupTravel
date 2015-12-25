<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PassengerBundle\Entity\Passenger;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;

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
     * Parameters - Object ID; Class; User ID
     *
     * @return Grants
     */


//    public function getPermission($object, $class, $user){
//        // return grants based on user, object and class
//        $em = $this->em;
//        $qb = $em->createQueryBuilder();
//        $qb->select('p.grants')
//            ->from('PermissionBundle:Permission', 'p')
//            ->where($qb->expr()->andX(
//                $qb->expr()->eq('p.object', '?1'),
//                $qb->expr()->eq('p.class', '?2'),
//                $qb->expr()->eq('p.user', '?3')
//            ));
//
//        $qb->setParameters(array(1 => $object, 2 => $class, 3 => $user ));
//        $query = $qb->getQuery();
//        $result = $query->getScalarResult();
//
//        if(!$result){
//            return Null;
//        } else {
//            $permission = array_column($result, 'grants');
//            return $permission;
//        }
//
//    }

    public function getPassengerStatus($status, $tourId){


        //Query builder for free passengers
        $em = $this->em;
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('PassengerBundle:Passenger', 'p')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('p.status', '?1'),
                $qb->expr()->eq('p.tourReference', '?2')
            ));
        $qb->setParameters(array(1 => $status, 2 => $tourId ));
        $query = $qb->getQuery();
        $result = $query->getScalarResult();

        return $result;
    }


}