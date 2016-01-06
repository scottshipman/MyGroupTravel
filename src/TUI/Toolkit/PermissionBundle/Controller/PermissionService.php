<?php

namespace TUI\Toolkit\PermissionBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PermissionBundle\Entity\Permission;
use TUI\Toolkit\PermissionBundle\Form\PermissionType;

/**
 * Permission Service controller.
 *
 */
class PermissionService
{

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }

  /**
   * Update or create a quote permission
   *
   * @return Permission
   */
  public function setPermission($object, $class, $user, $grants){
    // update organizer permission record, or create new one
    $em = $this->em;
    $qb = $em->createQueryBuilder();
    $qb->select(array('p'))
      ->from('PermissionBundle:Permission', 'p')
      ->where($qb->expr()->andX(
        $qb->expr()->eq('p.object', '?1'),
        $qb->expr()->eq('p.class', '?2'),
        $qb->expr()->eq('p.grants', '?3')
      ));
    $qb->setParameters(array(1 => $object, 2 => $class, 3 => $grants ));
    if($grants != 'organizer'){
      // business rule = only 1 organizer, multiple assistants,
      // so overwrite organizer by ommitting User in query when grant = organizer
      $qb->andWhere($qb->expr()->andX(
        $qb->expr()->eq('p.user', '?4')
      ));
      $qb->setParameter(4,  $user);
    }
//    $qb->setParameters(array(1 => $object, 2 => $class, 3 => $grants ));
    $query = $qb->getQuery();
    // there is only ever one organizer
    $result = $query->getOneOrNullResult();
    if(!$result){
      // no record for this combination so create one
      $permission = new Permission();
      $permission->setClass($class);
      $permission->setObject($object);
      $permission->setGrants($grants);
      $permission->setUser($user);
    } else {
      $permission = $result;
      if($grants == 'organizer'){
        // overwrite the user previously assigned
        $permission->setUser($user);
      }
    }

    $em->persist($permission);
    $em->flush();
    return $permission;
  }

  /**
   * Parameters - Object ID; Class; User ID
   *
   * @return Grants
   */
  public function getPermission($object, $class, $user){
    // return grants based on user, object and class
    $em = $this->em;
    $qb = $em->createQueryBuilder();
    $qb->select('p.grants')
      ->from('PermissionBundle:Permission', 'p')
      ->where($qb->expr()->andX(
        $qb->expr()->eq('p.object', '?1'),
        $qb->expr()->eq('p.class', '?2'),
        $qb->expr()->eq('p.user', '?3')
      ));

    $qb->setParameters(array(1 => $object, 2 => $class, 3 => $user ));
    $query = $qb->getQuery();
    $result = $query->getScalarResult();

    if(!$result){
      return Null;
    } else {
      $permission = array_column($result, 'grants');
      return $permission;
    }

  }

  /**
   * Parameters - Object ID; Class; Grants
   *
   * @return User
   */
  public function getUser($grants, $object, $class){
    // return user based on grants, object and class
    $em = $this->em;
    $qb = $em->createQueryBuilder();
    $qb->select('IDENTITY(p.user)')
        ->from('PermissionBundle:Permission', 'p')
        ->where($qb->expr()->andX(
            $qb->expr()->eq('p.grants', '?1'),
            $qb->expr()->eq('p.object', '?2'),
            $qb->expr()->eq('p.class', '?3')
        ));

    $qb->setParameters(array(1 => $grants, 2 => $object, 3 => $class ));
    $query = $qb->getQuery();
    $result = $query->getScalarResult();

    if(!$result){
      return Null;
    } else {
      $user = $result[0];
      return $user;
    }

  }

  /**
   * Parameters - User ID; Class; Grants
   *
   * @return Object ID
   */
  public function getObject($grants, $user, $class) {
    // return user based on grants, object and class
    $em = $this->em;
    $qb = $em->createQueryBuilder();
    $qb->select('p.object')
        ->from('PermissionBundle:Permission', 'p')
        ->where($qb->expr()->andX(
            $qb->expr()->eq('p.grants', '?1'),
            $qb->expr()->eq('p.user', '?2'),
            $qb->expr()->eq('p.class', '?3')
        ));

    $qb->setParameters(array(1 => $grants, 2 => $user, 3 => $class));
    $query = $qb->getQuery();
    $result = $query->getScalarResult();

    if (!$result) {
      return NULL;
    }
    else {
      $object = $result[0];
      return $object;
    }
  }
}