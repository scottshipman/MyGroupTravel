<?php
/**
 * Created by  scottshipman
 * Date: 6/22/15
 */

namespace TUI\Toolkit\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Null;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
    protected $id;

  /**
   * @ORM\Column(type="json_array")
   */
  protected $responsibility=array();

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $userParent = null;

    public function __construct()
      {
        parent::__construct();
        // your own logic

        $this->roles = array('ROLE_USER', 'ROLE_CUSTOMER');

/*      existing roles are:
            super admin
            admin
            brand
            customer -> with the following json psuedo roles / responsibilities
              organizers
              assistants
              parents
              passengers
            user

*/
      }

  public function setResponsibility($object, $responsibility)
  {
    $old_responsibility = $this->responsibility;

    // add to json array whatever is passed in
  }

  public function getResponsibility()
  {
    $responsibility = $this->responsibility;
    return array_unique($responsibility);
  }

  /**
   * @param mixed $user_parent
   */
  public function setUserParent($userParent) {
    $this->userParent = $userParent;
  }

  /**
   * @return mixed
   */
  public function getUserParent() {
    return $this->userParent;
  }


} 