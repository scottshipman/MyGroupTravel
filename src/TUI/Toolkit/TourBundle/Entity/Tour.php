<?php

namespace TUI\Toolkit\TourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Annotations;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Tour
 *
 * @ORM\Table(name="tour", uniqueConstraints={@ORM\UniqueConstraint(name="unique_quoteNumber", columns={"quoteNumber"})})
 * @ORM\Entity
 * @UniqueEntity(fields={"quoteNumber"}, message="This Quote Number already exists on another Tour.", ignoreNull=true)
 *
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 * @GRID\Source(columns="id, institution_full, name, quoteNumber, tripStatus.name, created, destination, quoteReference.id, institution.name,  institution.city, organizer_full, salesAgent_full, salesAgent.firstName, salesAgent.lastName,  salesAgent.email, organizer.firstName, organizer.lastName, organizer.email, views, deleted, locked,  version, duration, expiryDate, transportType.name, boardBasis.name, freePlaces, payingPlaces, departureDate, returnDate, pricePerson,  pricePersonPublic, currency.name, status, passengerDate, passportDate, medicalDate, dietaryDate", filterable=false, sortable=true)
 * @GRID\Column(id="organizer_full", type="join", columns = {"organizer.firstName", "organizer.lastName", "organizer.email"}, title="Organizer", export=false, filterable=true, operatorsVisible=false)
 * @GRID\Column(id="salesAgent_full",  type="join", columns = {"salesAgent.firstName", "salesAgent.lastName", "salesAgent.email"}, title="Primary Business Admin", export=false, filterable=true, operatorsVisible=false)
 * @GRID\Column(id="institution_full", type="join", columns = {"institution.name", "institution.city"}, title="Institution", export=false, filterable=true, operatorsVisible=false)
 */

class Tour
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @GRID\Column(title="Id", export=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @GRID\Column(title="Tour Name", filterable=true, operatorsVisible=false, export=true)
     *
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="integer")
     * @GRID\Column(field="version", title="Version", export=true)
     */
    private $version = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="quoteNumber", type="string", length=255, nullable=true)
     * @GRID\Column(title="Quote Number", filterable=true, operatorsVisible=false, export=true)
     * @Assert\Length(min=2)
     */
    private $quoteNumber;

    /**
     * @var date
     *
     * @ORM\Column(name="deleted", type="date", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $deleted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $locked = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isComplete", type="boolean")
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $isComplete = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="setupComplete", type="boolean")
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $setupComplete = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="date")
     * @GRID\Column(title="Created On", filterable=false, export=true)
     */
    private $created;

    /**
     * @var datetime
     *
     * @ORM\Column(name="ts", type="datetime", nullable=true)
     * @GRID\Column(field="ts", title="ts", export=false)
     */
    private $ts = null;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="organizer", referencedColumnName="id")
     * @GRID\Column(field="organizer.firstName", type="text", title="Organizer First", export=true)
     * @GRID\Column(field="organizer.lastName", type="text", title="Organizer Last", export=true)
     * @GRID\Column(field="organizer.email", type="text", title="Organizer Email", export=true)
     *
     */
    private $organizer;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="salesAgent", referencedColumnName="id")
     * @GRID\Column(field="salesAgent.firstName", type="text", title="Business Admin first", filterable=false, export=true)
     * @GRID\Column(field="salesAgent.lastName", type="text", title="Business Admin last", filterable=false, export=true)
     * @GRID\Column(field="salesAgent.email", type="text", title="Business Admin Email", filterable=false, export=true)
     *
     */
    private $salesAgent;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="secondaryContact", referencedColumnName="id")
     */
    private $secondaryContact;

    /**
     * @var integer
     * @ORM\JoinColumn(name="institution", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\InstitutionBundle\Entity\Institution", cascade={"all"}, fetch="EAGER")
     * @GRID\Column(field="institution.name", title="Institution", filterable=false, operatorsVisible=false, export=true)
     */
    private $institution;

    /**
     * @var string
     *
     * @ORM\Column(name="destination", type="string")
     * @GRID\Column(title="Destination", filterable=true, operatorsVisible=false, sortable=true, export=true)
     */
    private $destination;

    /**
     * @var integer
     * @GRID\Column(field="boardBasis.name", title="Board Basis", export=true)
     *
     * @ORM\JoinColumn(name="boardBasis", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\BoardBasisBundle\Entity\BoardBasis", cascade="persist")
     */
    private $boardBasis;


    /**
     * @var integer
     *
     * @ORM\JoinColumn(name="headerBlock", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\ContentBlocksBundle\Entity\ContentBlock", cascade="persist")
     */
    private $headerBlock;

    /**
     * @var array
     * @GRID\Column(title="Content", export=true)
     *
     * @ORM\Column(name="content", type="array", nullable=true)
     */
    private $content=array();

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiryDate", type="date", nullable=true)
     * @GRID\Column(title="Expirary Date", export=true)
     */
    private $expiryDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="freePlaces", type="integer", nullable=true)
     * @GRID\Column(title="Free Places", export=true)
     */
    private $freePlaces;

    /**
     * @var integer
     *
     * @ORM\Column(name="payingPlaces", type="integer", nullable=true)
     * @GRID\Column(title="Paying Places", export=true)
     */
    private $payingPlaces;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="departureDate", type="date", nullable=true)
     * @GRID\Column(title="Departure Date", export=true)
     */
    private $departureDate;

    /**
     * @var integer

     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\QuoteBundle\Entity\Quote", cascade={"persist"})
     * @ORM\JoinColumn(name="quoteReference", referencedColumnName="id")
     *
     * @GRID\Column(field = "quoteReference.name", title="Tour Name", export=true, filterable=true, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.id", title="ID", export=true)
     *
     * @GRID\Column(field = "quoteReference.salesAgent.firstName", title="BA First", export=false)
     * @GRID\Column(field = "quoteReference.salesAgent.lastName", title="BA Last", export=false)
     * @GRID\Column(field = "quoteReference.salesAgent.email", title="BA email", export=false)
     * @GRID\Column(field = "quoteReference.converted", title="Converted", export=true)
     * @GRID\Column(field = "quoteReference.setupComplete", title="Setup Complete", export=true)
     * @GRID\Column(field = "quoteReference.institution.name", title="Institution Name", export=true, filterable=false, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.institution.city", title="Institution City", export=true, filterable=false, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.organizer.firstName", title="O first", export=false)
     * @GRID\Column(field = "quoteReference.organizer.lastName", title="O last", export=false)
     * @GRID\Column(field = "quoteReference.organizer.email", title="O email", export=false)
     * @GRID\Column(field = "quoteReference.destination", title="Destination", export=true, filterable=true, operatorsVisible=false)
     */
    private $quoteReference;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\QuoteBundle\Entity\QuoteVersion", cascade={"persist"})
     * @ORM\JoinColumn(name="quoteVersionReference", referencedColumnName="id")
     *
     * @GRID\Column(field = "quoteVersion.name", title="Version Name", export=true, filterable=true, operatorsVisible=false)
     * @GRID\Column(field = "quoteVersion.id", title="ID", export=true)
     */
    private $quoteVersionReference;

    /**
     * @var float
     *
     * @ORM\Column(name="totalPrice", type="float", nullable=true)
     * @GRID\Column(title="Total Price", export=true)
     */
    private $totalPrice;

    /**
     * @var integer
     *
     * @ORM\JoinColumn(name="tripStatus", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TripStatusBundle\Entity\TripStatus", cascade="persist")
     * @GRID\Column(field="tripStatus.name", title="Trip Status", export=true)
     */
    private $tripStatus;

    /**
     * @var integer
     *
     * @ORM\JoinColumn(name="transportType", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TransportBundle\Entity\Transport", cascade="persist")
     * @GRID\Column(field="transportType.name", title="Transport Type", export=true)
     */
    private $transportType;

    /**
     * @var string
     *
     * @ORM\Column(name="welcomeMsg", type="text", nullable=true)
     * @GRID\Column(title="Welcome Message", export=true)
     */
    private $welcomeMsg;

    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="text", nullable=true)
     * @GRID\Column(title="Duration", export=true)
     */
    private $duration;

    /**
     * @var float
     *
     * @ORM\Column(name="pricePerson", type="float", nullable=true)
     * @GRID\Column(title="Price / Person", export=true)
     */
    private $pricePerson;

    /**
     * @var float
     *
     * @ORM\Column(name="pricePersonPublic", type="float", nullable=true)
     * @GRID\Column(title="Price per Person", export=true)
     */
    private $pricePersonPublic;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="returnDate", type="date", nullable=true)
     * @GRID\Column(title="Return Date", export=true)
     */
    private $returnDate;

    /**
     * @var integer
     *
     * @ORM\JoinColumn(name="currency", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\CurrencyBundle\Entity\Currency", cascade="persist")
     * @GRID\Column(field="currency.name", title="Currency", export=true)
     *
     */
    private $currency;

    /**
     * @var integer
     *
     * @ORM\Column(name="views", type="integer")
     * @GRID\Column(title="Views", filterable=false, export=true)
     */
    private $views;

    /**
     * @var integer
     *
     * @ORM\ManyToMany(targetEntity="TUI\Toolkit\TourBundle\Entity\PaymentTask", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="paymenttasks", referencedColumnName="id")
     */
    public $paymentTasks;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="passportDate", type="date", nullable=true)
     * @GRID\Column(title="Passport Info Due Date", export=true)
     */
    private $passportDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="passengerDate", type="date", nullable=true)
     * @GRID\Column(title="Passenger Info Due Date", export=true)
     */
    private $passengerDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="medicalDate", type="date", nullable=true)
     * @GRID\Column(title="Medical Info Due Date", export=true)
     */
    private $medicalDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="dietaryDate", type="date", nullable=true)
     * @GRID\Column(title="Dietary Info Due Date", export=true)
     */
    private $dietaryDate;

    public function __construct()
    {
      $this->created = new \DateTime();
      $this->views = 0;
      $this->shareViews = 0;
      $this->paymentTasks = new ArrayCollection();
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
     * Set ID
     *
     * @param integer $id
     * @return Tour
     */
    public function setId($id)
    {
      $this->id = $id;

      return $this;
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
     * Set organizer
     *
     * @param string $organizer
     * @return Tour
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * Get organizer
     *
     * @return string
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * Set salesAgent
     *
     * @param integer $salesAgent
     * @return Tour
     */
    public function setSalesAgent($salesAgent)
    {
        $this->salesAgent = $salesAgent;

        return $this;
    }

    /**
     * Get salesAgent
     *
     * @return integer
     */
    public function getSalesAgent()
    {
        return $this->salesAgent;
    }


    /**
     * Set secondaryContact
     *
     * @param integer $secondaryContact
     * @return Tour
     */
    public function setSecondaryContact($secondaryContact)
    {
        $this->secondaryContact = $secondaryContact;

        return $this;
    }

    /**
     * Get secondaryContact
     *
     * @return integer
     */
    public function getSecondaryContact()
    {
        return $this->secondaryContact;
    }


    /**
     * Set institution
     *
     * @param integer $institution
     * @return Tour
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution
     *
     * @return integer
     */
    public function getInstitution()
    {
        return $this->institution;
    }


    /**
     * Set destination
     *
     * @param integer $destination
     * @return Tour
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return integer
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set boardBasis
     *
     * @param integer $boardBasis
     * @return Tour
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
     * Set headerBlock
     *
     * @param integer $boardBasis
     * @return Tour
     */
    public function setHeaderBlock($headerBlock)
    {
      $this->headerBlock = $headerBlock;

      return $this;
    }

    /**
     * Get headerBlock
     *
     * @return integer
     */
    public function getHeaderBlock()
    {
      return $this->headerBlock;
    }


    /**
       * Set content
       *
       * @param array $content
       * @return Tour
       */
      public function setContent($content)
      {
          $this->content = $content;

          return $this;
      }

      /**
       * Get content
       *
       * @return array
       */
      public function getContent()
      {
          return $this->content;
      }

    /**
     * Set expiryDate
     *
     * @param \DateTime $expiryDate
     * @return Tour
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
     * @return Tour
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
     * Set payingPlaces
     *
     * @param integer $payingPlaces
     * @return Tour
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
     * @return Tour
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
     * Set quoteReference
     *
     * @param integer $quoteReference
     * @return Tour
     */
    public function setQuoteReference($quoteReference)
    {
        $this->quoteReference = $quoteReference;

        return $this;
    }

    /**
     * Get quoteReference
     *
     * @return integer 
     */
    public function getQuoteReference()
    {
        return $this->quoteReference;
    }

    /**
     * Set quoteVersionReference
     *
     * @param integer $quoteReference
     * @return Tour
     */
    public function setQuoteVersionReference($quoteVersionReference)
    {
      $this->quoteVersionReference = $quoteVersionReference;

      return $this;
    }

    /**
     * Get quoteVersionReference
     *
     * @return integer
     */
    public function getQuoteVersionReference()
    {
      return $this->quoteVersionReference;
    }

    /**
     * Set totalPrice
     *
     * @param float $totalPrice
     * @return Tour
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
     * @param string $tripStatus
     * @return Tour
     */
    public function setTripStatus($tripStatus)
    {
        $this->tripStatus = $tripStatus;

        return $this;
    }

    /**
     * Get tripStatus
     *
     * @return string 
     */
    public function getTripStatus()
    {
        return $this->tripStatus;
    }

    /**
     * Set transportType
     *
     * @param integer $transportType
     * @return Tour
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
     * Set welcomeMsg
     *
     * @param string $welcomeMsg
     * @return Tour
     */
    public function setWelcomeMsg($welcomeMsg)
    {
        $this->welcomeMsg = $welcomeMsg;

        return $this;
    }

    /**
     * Get welcomeMsg
     *
     * @return string 
     */
    public function getWelcomeMsg()
    {
        return $this->welcomeMsg;
    }

    /**
     * Set duration
     *
     * @param string $duration
     * @return Tour
     */
    public function setDuration($duration)
    {
      $this->duration = $duration;

      return $this;
    }

    /**
     * Get duration
     *
     * @return string
     */
    public function getDuration()
    {
      return $this->duration;
    }

    /**
     * Set pricePerson
     *
     * @param string $pricePerson
     * @return Tour
     */
    public function setPricePerson($pricePerson)
    {
      $this->pricePerson = $pricePerson;

      return $this;
    }

    /**
     * Get pricePerson
     *
     * @return float
     */
    public function getPricePerson()
    {
      return $this->pricePerson;
    }
    /**
     * Set pricePersonPublic
     *
     * @param string $pricePersonPublic
     * @return Tour
     */
    public function setPricePersonPublic($pricePersonPublic)
    {
        $this->pricePersonPublic = $pricePersonPublic;

        return $this;
    }

    /**
     * Get pricePersonPublic
     *
     * @return float
     */
    public function getPricePersonPublic()
    {
        return $this->pricePersonPublic;
    }


    /**
     * Set returnDate
     *
     * @param string $returnDate
     * @return Tour
     */
    public function setReturnDate($returnDate)
    {
      $this->returnDate = $returnDate;

      return $this;
    }

    /**
     * Get returnDate
     *
     * @return date
     */
    public function getReturnDate()
    {
      return $this->returnDate;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Tour
     */
    public function setCurrency($currency)
    {
      $this->currency = $currency;

      return $this;
    }

    /**
     * Get currency
     *
     * @return float
     */
    public function getCurrency()
    {
      return $this->currency;
    }

    /**
     * Set version
     *
     * @param integer $version
     * @return Tour
     */
    public function setVersion($version)
    {
      $this->version = $version;

      return $this;
    }

    /**
     * Get version
     *
     * @return integer
     */
    public function getVersion()
    {
      return $this->version;
    }

    /**
     * Set ts
     *
     * @param datetime $ts
     * @return Tour
     */
    public function setTs($ts)
    {
      $this->ts = $ts;

      return $this;
    }

    /**
     * Get ts
     *
     * @return datetime
     */
    public function getTs()
    {
      return $this->ts;
    }


    /**
     * Set quoteNumber
     *
     * @param string $reference
     * @return Quote
     */
    public function setQuoteNumber($quoteNumber)
    {
      $this->quoteNumber = $quoteNumber;

      return $this;
    }

    /**
     * Get quoteNumber
     *
     * @return string
     */
    public function getQuoteNumber()
    {
      return $this->quoteNumber;
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
     * Set isComplete
     *
     * @param boolean $isComplete
     * @return Tour
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;
    }

    /**
     * Get isComplete
     *
     * @return boolean
     */
    public function getIsComplete()
    {
        return $this->isComplete;
    }

    /**
     * Set views
     *
     * @param integer $views
     * @return Quote
     */
    public function setViews($views)
    {
      $this->views = $views;

      return $this;
    }

    /**
     * Get views
     *
     * @return integer
     */
    public function getViews()
    {
      return $this->views;
    }

    /**
     * Set created
     *
     * @param date $created
     * @return Quote
     */
    public function setCreated($created)
    {
      if (!$created) {
        $created = new \DateTime();
      }
      $this->created = $created;

      return $this;
    }

    /**
     * Get created
     *
     * @return date
     */
    public function getCreated()
    {
      return $this->created;
    }


    /**
     * Set passengerDate
     *
     * @param \DateTime $passengerDate
     * @return Tour
     */
    public function setPassengerDate($passengerDate)
    {
      $this->passengerDate = $passengerDate;

      return $this;
    }

    /**
     * Get passengerDate
     *
     * @return \DateTime
     */
    public function getPassengerDate()
    {
      return $this->passengerDate;
    }

    /**
     * Set passportDate
     *
     * @param \DateTime $passportDate
     * @return Tour
     */
    public function setPassportDate($passportDate)
    {
      $this->passportDate = $passportDate;

      return $this;
    }

    /**
     * Get passportDate
     *
     * @return \DateTime
     */
    public function getPassportDate()
    {
      return $this->passportDate;
    }

    /**
     * Set medicalDate
     *
     * @param \DateTime $medicalDate
     * @return Tour
     */
    public function setMedicalDate($medicalDate)
    {
      $this->medicalDate = $medicalDate;

      return $this;
    }

    /**
     * Get medicalDate
     *
     * @return \DateTime
     */
    public function getMedicalDate()
    {
      return $this->medicalDate;
    }

    /**
     * Set dietaryDate
     *
     * @param \DateTime $dietaryDate
     * @return Tour
     */
    public function setDietaryDate($dietaryDate)
    {
      $this->dietaryDate = $dietaryDate;

      return $this;
    }

    /**
     * Get dietaryDate
     *
     * @return \DateTime
     */
    public function getDietaryDate()
    {
      return $this->dietaryDate;
    }

/****** Helper functions *********/

    /*
     * add and remove paymentTasks in forms using js/prototype
     */
    public function addPaymentTask(PaymentTask $paymentTask)
      {
        $this->paymentTasks->add($paymentTask);
      }

    public function removePaymentTask(PaymentTask $paymentTask)
      {
        $this->paymentTasks->removeElement($paymentTask);
      }


    /**
     * @var \TUI\Toolkit\MediaBundle\Entity\Media
     * @ORM\ManyToMany(targetEntity="TUI\Toolkit\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="media", referencedColumnName="id")
     */
    protected $media;

    /**
     * @param $media
     */

    public function addMedia($media)
    {

        $this->media[] = $media;
        return $this;
    }

    /**
     * @param  $media
     */

    public function setMedia($media)
    {
        $this->media = $media;

        return $media;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
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
}
