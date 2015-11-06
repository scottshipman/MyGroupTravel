<?php

namespace TUI\Toolkit\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Passenger
 *
 * @ORM\Table(name="passenger", uniqueConstraints={@ORM\UniqueConstraint(name="unique_passenger", columns={"f_name", "l_name", "dob", "tour"})})
 * @UniqueEntity(fields={"fName", "lName", "dateOfBirth", "tourReference"}, message="This Passenger is already listed on this tour", ignoreNull=true)
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
    protected $fName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lName = null;


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
     * @var datetime
     *
     * @ORM\Column(name="signup", type="date", nullable=false)
     */
    protected $signUpDate;

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
     * @param  $fName
     */
    public function setFName($fName)
    {
        $this->fName = $fName;

        return $fName;
    }

    /**
     * @return fName
     */
    public function getFName()
    {
        return $this->fName;
    }

    /**
     * @param  $lName
     */
    public function setLName($lName)
    {
        $this->lName = $lName;

        return $lName;
    }

    /**
     * @return lName
     */
    public function getLName()
    {
        return $this->lName;
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
     * @param  $signUpDate
     */
    public function setSignUpDate($signUpDate)
    {
        $this->signUpDate = $signUpDate;

        return $signUpDate;
    }

    /**
     * @return signUpDate
     */
    public function getSignUpDate()
    {
        return $this->signUpDate;
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