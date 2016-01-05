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
    protected $passportLastName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    protected $passportNationality;

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
