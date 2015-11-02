<?php

namespace TUI\Toolkit\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


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
     * @var \TUI\Toolkit\TourBundle\Entity\Tour
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TourBundle\Entity\Tour", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="tour", referencedColumnName="id")
     *
     */
    protected $tourReference;


    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     */
    protected $status;


    public function __construct()  {
    }


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
     * @param  $tourReference
     */
    public function setTourReference($tourReference)
    {
        $this->tourReference = $tourReference;

        return $tourReference;
    }

    /**
     * @return tourReference
     */
    public function getTourReference()
    {
        return $this->tourReference;
    }

    /**
     * @param  $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $status;
    }

    /**
     * @return tourReference
     */
    public function getStatus()
    {
        return $this->status;
    }
}
