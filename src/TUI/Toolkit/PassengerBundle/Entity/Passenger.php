<?php

namespace TUI\Toolkit\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;


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
     * @Encrypted
     */
    protected $fName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Encrypted
     */
    protected $lName = null;


    /**
     * @var string
     *
     * @ORM\Column(name="gender", nullable=true, type="string")
     * @Encrypted
     */
    protected $gender;


    /**
     * @var datetime
     *
     * @Assert\Date()
     * @ORM\Column(name="dob", type="date", nullable=true)
     *
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
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TourBundle\Entity\Tour", cascade={"persist"})
     * @ORM\JoinColumn(name="tour", referencedColumnName="id")
     *
     */
    protected $tourReference;

    /**
     * @var \TUI\Toolkit\PassengerBundle\Entity\Medical
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Medical")
     * @ORM\JoinColumn(name="medical", referencedColumnName="id")
     */
    protected $medicalReference;

    /**
     * @var \TUI\Toolkit\PassengerBundle\Entity\Dietary
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Dietary")
     * @ORM\JoinColumn(name="dietary", referencedColumnName="id")
     */
    protected $dietaryReference;

    /**
     * @var \TUI\Toolkit\PassengerBundle\Entity\Passport
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Passport")
     * @ORM\JoinColumn(name="passport", referencedColumnName="id")
     */
    protected $passportReference;

    /**
     * @var \TUI\Toolkit\PassengerBundle\Entity\Emergency
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Emergency")
     * @ORM\JoinColumn(name="emergency", referencedColumnName="id")
     */
    protected $emergencyReference;

    /**
     * @var \TUI\Toolkit\MediaBundle\Entity\Media
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="media", referencedColumnName="id")
     */
    protected $media;


    /**
     * @var integer
     *
     * @ORM\Column(name="self", type="boolean")
     */
    protected $self;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     */
    protected $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="free", type="boolean")
     */
    protected $free;

    /**
     * @var \TUI\Toolkit\PaymentBundle\Entity\Payment
     *
     * @ORM\OneToMany(targetEntity="TUI\Toolkit\PaymentBundle\Entity\Payment", mappedBy="passenger", cascade={"persist"})
     */
    protected $payments;


    public function __construct()  {
        $this->self = FALSE;
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
      * @param $medicalReference
      * @return $this
      *
      */
    public function setMedicalReference($medicalReference) {

        $this->medicalReference = $medicalReference;

        return $medicalReference;

    }

    /**
     * @return Medical
     *
     */
    public function getMedicalReference() {
        return $this->medicalReference;
    }

    /**
     * @param $dietaryReference
     * @return $this
     *
     */
    public function setDietaryReference($dietaryReference) {

        $this->dietaryReference = $dietaryReference;

        return $dietaryReference;

    }

    /**
     * @return Dietary
     *
     */
    public function getDietaryReference() {
        return $this->dietaryReference;
    }

    /**
     * @param $passportReference
     * @return $this
     *
     */
    public function setPassportReference($passportReference) {

        $this->passportReference = $passportReference;

        return $passportReference;

    }

    /**
     * @return Passport
     *
     */
    public function getPassportReference() {
        return $this->passportReference;
    }

    /**
     * @param $emergencyReference
     * @return $this
     *
     */
    public function setEmergencyReference($emergencyReference) {

        $this->emergencyReference = $emergencyReference;

        return $emergencyReference;

    }

    /**
     * @return Emergency
     *
     */
    public function getEmergencyReference() {
        return $this->emergencyReference;
    }

    /**
     * @param  $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return tourReference
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param  $free
     */
    public function setFree($free)
    {
        $this->free = $free;

        return $this;
    }

    /**
     * @return free
     */
    public function getFree()
    {
        return $this->free;
    }

    /**
     * @param  $media
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param  $self
     */
    public function setSelf($self)
    {
        $this->self = $self;

        return $this;
    }

    /**
     * @return self
     */
    public function getSelf()
    {
        return $this->self;
    }

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }
}
