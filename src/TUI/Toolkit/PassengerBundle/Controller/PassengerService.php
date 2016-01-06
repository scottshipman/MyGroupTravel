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

    public function getPassengersByStatus($status, $tourId){

        // special query case for free status for boolean field
        $statusExpr = $status == 'free' ? 'p.free' : 'p.status';
        $status = $status == 'free' ? 1 : $status;

        //Query builder for free passengers
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
        $query = $qb->getQuery();
        $result = $query->getScalarResult();

        return $result;
    }

    public function getOrganizers($tourId){


        //Query builder for free passengers
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
        $result = $query->getScalarResult();

        return $result;
    }


}