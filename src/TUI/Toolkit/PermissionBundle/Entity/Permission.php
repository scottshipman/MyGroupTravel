<?php

namespace TUI\Toolkit\PermissionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Permission
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=255)
     */
    private $class;

    /**
     * @var integer
     *
     * @ORM\Column(name="object", type="integer")
     */
    private $object;

    /**
     * @var integer
     *
     * @ORM\ManytoOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"}, inversedBy="i")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="grants", type="string", length=255)
     */
    private $grants;

  /**
   * toString() function
   */

    public function toString(){
      $class = !is_null($this->class) ? $this->class : '~';
      $user = !is_null($this->user) ? $this->user->getEmail() : '~';
      $object = !is_null($this->object) ? $this->object : '~';
      $grants = !is_null($this->grants) ? $this->grants : '~';

      return $class . ' / ' . $object . ' / '. $user;

    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set class
     *
     * @param string $class
     * @return Permission
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set object
     *
     * @param integer $object
     * @return Permission
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return integer 
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set user
     *
     * @param integer $user
     * @return Permission
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return integer 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set grants
     *
     * @param string $grants
     * @return Permission
     */
    public function setGrants($grants)
    {
        $this->grants = $grants;

        return $this;
    }

    /**
     * Get grants
     *
     * @return string 
     */
    public function getGrants()
    {
        return $this->grants;
    }
}
