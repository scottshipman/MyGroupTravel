<?php

namespace TUI\Toolkit\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Passenger
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TUI\Toolkit\PassengerBundle\Entity\PassengerRepository")
 */
class Passenger
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastName = null;

    /**
     * @var date
     *
     * @ORM\Column(name="email", type="string", nullable=false)
     */
    protected $email;


    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string")
     */
    protected $gender;


    /**
     * @var datetime
     *
     * @ORM\Column(name="dob", type="date", nullable=false)
     */
    protected $dateOfBirth;


    /**
     *
     * @var \TUI\Toolkit\UserBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     *
     */
    protected $userReference;

    /**
     *
     * @var \TUI\Toolkit\UserBundle\Entity\User
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="guardian", referencedColumnName="id")
     *
     */
    protected $guardian;

    /**
     *
     * @var \TUI\Toolkit\TourBundle\Entity\Tour
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TourBundle\Entity\Tour", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="tour", referencedColumnName="id")
     *
     */
    protected $tourReference;



    /**
     * @param  $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $firstName;
    }

    /**
     * @return firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param  $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $lastName;
    }

    /**
     * @return lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param  $email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $email;
    }

    /**
     * @return email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param  $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $gender;
    }

    /**
     * @return gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param  $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $dateOfBirth;
    }

    /**
     * @return dateOfBirth
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param  $user
     */
    public function setUserReference($user)
    {
        $this->user = $user;

        return $user;
    }

    /**
     * @return user
     */
    public function getUserReference()
    {
        return $this->user;
    }

    /**
     * @param  $guardian
     */
    public function setGuardian($guardian)
    {
        $this->guardian = $guardian;

        return $guardian;
    }

    /**
     * @return guardian
     */
    public function getGuardian()
    {
        return $this->guardian;
    }

}
