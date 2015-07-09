<?php
/**
 * Created by PhpStorm.
 * User: scottshipman
 * Date: 6/24/15
 * Time: 4:16 PM
 */

namespace AppBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use \Symfony\Component\DependencyInjection\ContainerAware;

class ResponsibilityVoter extends ContainerAware implements VoterInterface
{

  private $responsibility;

  public function __construct($responsibility = array())
  {
    $this->responsibility = $responsibility;
  }

  public function supportsAttribute($attributes)
  {
    // you won't check against a user attribute, so return true
    return true;
  }

  public function supportsClass($class)
  {
    // your voter supports all type of token classes, so return true
    return TRUE;
  }

  /**
   * @var $object
   */
  public function vote(TokenInterface $token, $object, array $attributes)
  {
    // get responsibility paramters
    $parameter =  $this->container->getParameter('responsibility');
    $responsibility_roles = $parameter['responsibility_roles'];
    $responsibility_class = $parameter['responsibility_class'];
    // get current logged in user
    $user = $token->getUser();

    // make sure there is a user object (i.e. that the user is logged in)
    if (!$user instanceof UserInterface) {
      return VoterInterface::ACCESS_DENIED;
    }

    // if user ROLE is BRAND or Higher, grant access.
    $intersect = array_intersect($user->getRoles(), array('ROLE_BRAND', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'));
    if (!empty($intersect)){
      return TRUE;
    }


    /* 1. get user responsibility array
       2. look for $object in array
       3. if $object in array, get associated responsibility
       4. user->responsibility array key must be equal or less than passed in responsility array key
          something like: foreach ($responsibility_roles as $key=>$role){
                            if (user['responsibility'] = $role){
                                $user_privelege =  $key;
                            }
                             if (attributes[0] = $role){
                                $responsibility_privelege =  $key;
                            }
                          }
                        if($user_privelege<=$responsibility_privelege){
                            return TRUE;
                        }

          Or maybe allow passing in array of responsibility roles (parent, passenger,etc),
          and just see if there is an array instersection of User['responsibility'] to attributes.

      $user_privelege=1; $responsibility_privelege = 1; // temporary, delete when above is done

    if($user_privelege <= $responsibility_privelege){
      return TRUE;
    }
*/

    //return VoterInterface::ACCESS_DENIED;
  }
} 