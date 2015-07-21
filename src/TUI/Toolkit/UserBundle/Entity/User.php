<?php
/**
 * Created by  scottshipman
 * Date: 6/22/15
 */

namespace TUI\Toolkit\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Sonata\MediaBundle\Model\MediaInterface;
use Application\Sonata\MediaBundle;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $userParent = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $honorific = null;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    protected $phoneNumber = null;


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

  public function __toString()
  {
    return $this->firstName . ' ' . $this->lastName;
  }

    /**
     * @param mixed $user_parent
     */
    public function setUserParent($userParent)
    {
        $this->userParent = $userParent;
    }

    /**
     * @return mixed
     */
    public function getUserParent()
    {
        return $this->userParent;
    }

    /**
     * @var \Application\Sonata\MediaBundle\Entity\Media
     * @ORM\OneToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $media;

    /**
     * @param MediaInterface $media
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $media;
    }

    /**
     * @return MediaInterface
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param mixed $honorific
     */
    public function setHonorific($honorific)
    {
        $this->honorific = $honorific;
    }

    /**
     * @return mixed
     */
    public function getHonorific()
    {
        return $this->honorific;
    }


    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }
} 