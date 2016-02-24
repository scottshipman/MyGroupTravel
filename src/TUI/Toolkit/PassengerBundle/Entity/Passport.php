<?php

namespace TUI\Toolkit\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Annotations;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Passport
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Passport
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
     * @ORM\Column(name="passport_number", type="text", nullable=true)
     * @GRID\Column(title="Passport Number", export=true)
     */
    private $passportNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $passportFirstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $passportMiddleName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $passportLastName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $passportTitle;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $passportGender;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $passportNationality;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     * @Assert\Length(
     *    min=3,
     *    max=3,
     *    minMessage = "Your first name must be at least {{ limit }} characters long",
     *    maxMessage = "Your first name cannot be longer than {{ limit }} characters"
     * )
     */
    private $passportIssuingState;

    /**
     * @var date
     *
     * @ORM\Column(name="date_of_issue", type="date", nullable=true)
     * @GRID\Column(visible=true, export=true, operatorsVisible=false)
     */
    private $passportDateOfIssue;

    /**
     * @var date
     *
     * @ORM\Column(name="date_of_expiry", type="date", nullable=true)
     * @GRID\Column(visible=true, export=true, operatorsVisible=false)
     */
    private $passportDateOfExpiry;

    /**
     * @var date
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     * @GRID\Column(visible=true, export=true, operatorsVisible=false)
     */
    private $passportDateOfBirth;

    /**
     * @var \TUI\Toolkit\PassengerBundle\Entity\Passenger
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="passenger", referencedColumnName="id")
     */
    protected $passengerReference;

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
     * @param $passportNumber
     * @return $this
     *
     */
    public function setPassportNumber($passportNumber) {

        $this->passportNumber = $passportNumber;

        return $this;

    }

    /**
     * @return PassportNumber
     *
     */
    public function getPassportNumber() {
        return $this->passportNumber;
    }

    /**
     * @param $passportFirstName
     * @return $this
     *
     */
    public function setPassportFirstName($passportFirstName) {

        $this->passportFirstName = $passportFirstName;

        return $this;

    }

    /**
     * @return passportFirstName
     *
     */
    public function getPassportFirstName() {
        return $this->passportFirstName;
    }

    /**
     * @param $passportMiddleName
     * @return $this
     *
     */
    public function setPassportMiddleName($passportMiddleName) {

        $this->passportMiddleName = $passportMiddleName;

        return $this;

    }

    /**
     * @return passportMiddleName
     *
     */
    public function getPassportMiddleName() {
        return $this->passportMiddleName;
    }

    /**
     * @param $passportLastName
     * @return $this
     *
     */
    public function setPassportLastName($passportLastName) {

        $this->passportLastName = $passportLastName;

        return $this;

    }

    /**
     * @return passportLastName
     *
     */
    public function getPassportLastName() {
        return $this->passportLastName;
    }

    /**
     * @param $passportTitle
     * @return $this
     *
     */
    public function setPassportTitle($passportTitle) {

        $this->passportTitle = $passportTitle;

        return $this;

    }

    /**
     * @return passportTitle
     *
     */
    public function getPassportTitle() {
        return $this->passportTitle;
    }

    /**
     * @param $passportGender
     * @return $this
     *
     */
    public function setPassportGender($passportGender) {

        $this->passportGender = $passportGender;

        return $this;

    }

    /**
     * @return passportGender
     *
     */
    public function getPassportGender() {
        return $this->passportGender;
    }

    /**
     * @param $passportIssuingState
     * @return $this
     *
     */
    public function setPassportIssuingState($passportIssuingState) {

        $this->passportIssuingState = $passportIssuingState;

        return $this;

    }

    /**
     * @return passportGender
     *
     */
    public function getPassportIssuingState() {
        return $this->passportIssuingState;
    }

    /**
     * @param $passportNationality
     * @return $this
     *
     */
    public function setPassportNationality($passportNationality) {

        $this->passportNationality = $passportNationality;

        return $this;

    }

    /**
     * @return passportNationality
     *
     */
    public function getPassportNationality() {
        return $this->passportNationality;
    }

    /**
     * @param $passportDateOfIssue
     * @return $this
     *
     */
    public function setPassportDateOfIssue($passportDateOfIssue) {

        $this->passportDateOfIssue = $passportDateOfIssue;

        return $this;

    }

    /**
     * @return passportDateOfIssue
     *
     */
    public function getPassportDateOfIssue() {
        return $this->passportDateOfIssue;
    }

    /**
     * @param $passportDateOfExpiry
     * @return $this
     *
     */
    public function setPassportDateOfExpiry($passportDateOfExpiry) {

        $this->passportDateOfExpiry = $passportDateOfExpiry;

        return $this;

    }

    /**
     * @return passportDateOfExpiry
     *
     */
    public function getPassportDateOfExpiry() {
        return $this->passportDateOfExpiry;
    }

    /**
     * @param $passportDateOfBirth
     * @return $this
     *
     */
    public function setPassportDateOfBirth($passportDateOfBirth) {

        $this->passportDateOfExpiry = $passportDateOfBirth;

        return $this;

    }

    /**
     * @return passportDateOfBirth
     *
     */
    public function getPassportDateOfBirth() {
        return $this->passportDateOfBirth;
    }

    /**
     * @param $passengerReference
     * @return $this
     *
     */
    public function setPassengerReference($passengerReference) {

        $this->passengerReference = $passengerReference;

        return $this;

    }

    /**
     * @return Medical
     *
     */
    public function getPassengerReference() {
        return $this->passengerReference;
    }

}
