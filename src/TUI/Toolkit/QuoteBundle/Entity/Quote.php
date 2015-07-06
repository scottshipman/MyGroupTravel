<?php

namespace TUI\Toolkit\QuoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Quote
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Quote
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
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var json
     *
     * @ORM\Column(name="displayTabs", type="json")
     */
    private $displayTabs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiryDate", type="date")
     */
    private $expiryDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="freePlaces", type="integer")
     */
    private $freePlaces;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    private $locked;

    /**
     * @var integer
     *
     * @ORM\Column(name="maxPax", type="integer")
     */
    private $maxPax;

    /**
     * @var integer
     *
     * @ORM\Column(name="minPax", type="integer")
     */
    private $minPax;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="payingPlaces", type="integer")
     */
    private $payingPlaces;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="departureDate", type="date")
     */
    private $departureDate;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255)
     */
    private $reference;

    /**
     * @var boolean
     *
     * @ORM\Column(name="setupComplete", type="boolean")
     */
    private $setupComplete;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="signupDeadline", type="date")
     */
    private $signupDeadline;

    /**
     * @var integer
     *
     * @ORM\Column(name="quoteDays", type="integer")
     */
    private $quoteDays;

    /**
     * @var integer
     *
     * @ORM\Column(name="quoteNights", type="integer")
     */
    private $quoteNights;

    /**
     * @var float
     *
     * @ORM\Column(name="totalPrice", type="float")
     */
    private $totalPrice;

    /**
     * @var integer
     *
     * @ORM\Column(name="tripStatus", type="integer")
     */
    private $tripStatus;

    /**
     * @var integer
     *
     * @ORM\Column(name="boardBasis", type="integer")
     */
    private $boardBasis;

    /**
     * @var integer
     *
     * @ORM\Column(name="transportType", type="integer")
     */
    private $transportType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;


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
     * Set status
     *
     * @param string $status
     * @return Quote
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set displayTabs
     *
     * @param json $displayTabs
     * @return Quote
     */
    public function setDisplayTabs($displayTabs)
    {
        $this->displayTabs = $displayTabs;

        return $this;
    }

    /**
     * Get displayTabs
     *
     * @return json 
     */
    public function getDisplayTabs()
    {
        return $this->displayTabs;
    }

    /**
     * Set expiryDate
     *
     * @param \DateTime $expiryDate
     * @return Quote
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate
     *
     * @return \DateTime 
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set freePlaces
     *
     * @param integer $freePlaces
     * @return Quote
     */
    public function setFreePlaces($freePlaces)
    {
        $this->freePlaces = $freePlaces;

        return $this;
    }

    /**
     * Get freePlaces
     *
     * @return integer 
     */
    public function getFreePlaces()
    {
        return $this->freePlaces;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     * @return Quote
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean 
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set maxPax
     *
     * @param integer $maxPax
     * @return Quote
     */
    public function setMaxPax($maxPax)
    {
        $this->maxPax = $maxPax;

        return $this;
    }

    /**
     * Get maxPax
     *
     * @return integer 
     */
    public function getMaxPax()
    {
        return $this->maxPax;
    }

    /**
     * Set minPax
     *
     * @param integer $minPax
     * @return Quote
     */
    public function setMinPax($minPax)
    {
        $this->minPax = $minPax;

        return $this;
    }

    /**
     * Get minPax
     *
     * @return integer 
     */
    public function getMinPax()
    {
        return $this->minPax;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Quote
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set payingPlaces
     *
     * @param integer $payingPlaces
     * @return Quote
     */
    public function setPayingPlaces($payingPlaces)
    {
        $this->payingPlaces = $payingPlaces;

        return $this;
    }

    /**
     * Get payingPlaces
     *
     * @return integer 
     */
    public function getPayingPlaces()
    {
        return $this->payingPlaces;
    }

    /**
     * Set departureDate
     *
     * @param \DateTime $departureDate
     * @return Quote
     */
    public function setDepartureDate($departureDate)
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    /**
     * Get departureDate
     *
     * @return \DateTime 
     */
    public function getDepartureDate()
    {
        return $this->departureDate;
    }

    /**
     * Set reference
     *
     * @param string $reference
     * @return Quote
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string 
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set setupComplete
     *
     * @param boolean $setupComplete
     * @return Quote
     */
    public function setSetupComplete($setupComplete)
    {
        $this->setupComplete = $setupComplete;

        return $this;
    }

    /**
     * Get setupComplete
     *
     * @return boolean 
     */
    public function getSetupComplete()
    {
        return $this->setupComplete;
    }

    /**
     * Set signupDeadline
     *
     * @param \DateTime $signupDeadline
     * @return Quote
     */
    public function setSignupDeadline($signupDeadline)
    {
        $this->signupDeadline = $signupDeadline;

        return $this;
    }

    /**
     * Get signupDeadline
     *
     * @return \DateTime 
     */
    public function getSignupDeadline()
    {
        return $this->signupDeadline;
    }

    /**
     * Set quoteDays
     *
     * @param integer $quoteDays
     * @return Quote
     */
    public function setQuoteDays($quoteDays)
    {
        $this->quoteDays = $quoteDays;

        return $this;
    }

    /**
     * Get quoteDays
     *
     * @return integer 
     */
    public function getQuoteDays()
    {
        return $this->quoteDays;
    }

    /**
     * Set quoteNights
     *
     * @param integer $quoteNights
     * @return Quote
     */
    public function setQuoteNights($quoteNights)
    {
        $this->quoteNights = $quoteNights;

        return $this;
    }

    /**
     * Get quoteNights
     *
     * @return integer 
     */
    public function getQuoteNights()
    {
        return $this->quoteNights;
    }

    /**
     * Set totalPrice
     *
     * @param float $totalPrice
     * @return Quote
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return float 
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set tripStatus
     *
     * @param integer $tripStatus
     * @return Quote
     */
    public function setTripStatus($tripStatus)
    {
        $this->tripStatus = $tripStatus;

        return $this;
    }

    /**
     * Get tripStatus
     *
     * @return integer 
     */
    public function getTripStatus()
    {
        return $this->tripStatus;
    }

    /**
     * Set boardBasis
     *
     * @param integer $boardBasis
     * @return Quote
     */
    public function setBoardBasis($boardBasis)
    {
        $this->boardBasis = $boardBasis;

        return $this;
    }

    /**
     * Get boardBasis
     *
     * @return integer 
     */
    public function getBoardBasis()
    {
        return $this->boardBasis;
    }

    /**
     * Set transportType
     *
     * @param integer $transportType
     * @return Quote
     */
    public function setTransportType($transportType)
    {
        $this->transportType = $transportType;

        return $this;
    }

    /**
     * Get transportType
     *
     * @return integer 
     */
    public function getTransportType()
    {
        return $this->transportType;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Quote
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}
