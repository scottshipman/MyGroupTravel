<?php

namespace TUI\Toolkit\PermissionBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TUI\Toolkit\PermissionBundle\Entity\Permission;
use TUI\Toolkit\PermissionBundle\Form\PermissionType;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * Permission Service controller.
 *
 */
class PermissionService
{

  protected $em;
  protected $container;

  public function __construct(\Doctrine\ORM\EntityManager $em, Container $container)
  {
    $this->em = $em;
    $this->container = $container;
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
   * @param $class
   * @param $user
   * @return array|null
   */
  public function getAllPermissions($class, $user) {
    // return grants based on user, object and class
    $em = $this->em;
    $qb = $em->createQueryBuilder();
    $qb->select('p.grants')
      ->from('PermissionBundle:Permission', 'p')
      ->where($qb->expr()->andX(
        $qb->expr()->eq('p.class', '?1'),
        $qb->expr()->eq('p.user', '?2')
      ));

    $qb->setParameters(array(1 => $class, 2 => $user ));
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
   * Parameters - Object ID; Class; Grants
   *
   * @return Users
   */
  public function getUsers($grants, $object, $class){
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
      return $result;
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
    $result = $query->getArrayResult();

    if (!$result) {
      return NULL;
    }
    else {
      return $result;
    }
  }

  /**
   * Check user permissions.
   *
   * @param $throw_exception
   * @param $class
   * @param $object
   * @param $grants
   * @param $role_override
   * @return mixed
   */
  public function checkUserPermissions($throw_exception, $class = NULL, $object = NULL, $grants = NULL, $role_override = NULL) {
    $user = $this->container->get('security.token_storage')->getToken()->getUser();

    // If there is a role override, check the users role immediately.
    if (!is_null($role_override) && $this->container->get("security.authorization_checker")->isGranted($role_override, $user)) {
      return TRUE;
    }

    // If a class has been provided check the permissions table for grants.
    if (!is_null($class)) {
      if (!is_null($grants) && !is_array($grants)) {
        $grants = array($grants);
      }

      // If an object has been provided you need to get all permissions.
      if (!is_null($object)) {
        $user_grants = $this->container->get("permission.set_permission")->getPermission($object, $class, $user);
      }
      else {
        $user_grants = $this->container->get("permission.set_permission")->getAllPermissions($class, $user);
      }

      // If the user has grants compare to the ones we are checking for.
      if (!empty($user_grants)) {
        if (!empty($grants)) {
          $matched_grants = array_intersect($grants, $user_grants);

          if (!empty($matched_grants)) {
            return TRUE;
          }
        }
        else {
          return TRUE;
        }
      }
    }

    // Nothing has returned true, assume permission denied.
    if ($throw_exception) {
      throw new AccessDeniedException();
    }
    else {
      return FALSE;
    }
  }

  /**
   * Check multiple user permissions.
   *
   * @param $throw_exception
   * @param $permissions
   * @return mixed
   */
  public function checkUserPermissionsMultiple($throw_exception, $permissions) {
    foreach ($permissions as $permission) {
      if (!isset($permission['class'])) {
        $permission['class'] = NULL;
      }

      if (!isset($permission['object'])) {
        $permission['object'] = NULL;
      }

      if (!isset($permission['grants'])) {
        $permission['grants'] = NULL;
      }

      if (!isset($permission['role_override'])) {
        $permission['role_override'] = NULL;
      }

      if ($this->checkUserPermissions(FALSE, $permission['class'], $permission['object'], $permission['grants'], $permission['role_override'])) {
        return TRUE;
      }
    }

    // Nothing has returned true, assume permission denied.
    if ($throw_exception) {
      throw new AccessDeniedException();
    }
    else {
      return FALSE;
    }
  }
}