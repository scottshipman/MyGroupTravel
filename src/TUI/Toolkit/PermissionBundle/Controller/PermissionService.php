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
    if($grants != 'organizer'){
      // business rule = only 1 organizer, multiple assistants,
      // so overwrite organizer by ommitting User in query when grant = organizer
      $qb->andWhere($qb->expr()->andX(
        $qb->expr()->eq('p.user', '?4')
      ));
      $qb->setParameter(array(4 => $user));
    }
    $qb->setParameters(array(1 => $object, 2 => $class, 3 => $grants ));
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


}