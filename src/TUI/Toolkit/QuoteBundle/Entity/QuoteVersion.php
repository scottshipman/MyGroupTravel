<?php

namespace TUI\Toolkit\QuoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Annotations;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * QuoteVersion
 *
 * @ORM\Table(name="quote_version", uniqueConstraints={@ORM\UniqueConstraint(name="unique_quoteNumber", columns={"quoteNumber"})})
 * @ORM\Entity
 * @UniqueEntity(fields={"quoteNumber"}, message="This Quote Number already exists on another Quote.", ignoreNull=true)
 *
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 * @GRID\Source(columns="id, institution_full, quoteReference.institution.name, quoteReference.institution.city, quoteReference.name, name, isTemplate, quoteReference.ts, quoteReference.id, quoteNumber, organizer_full, salesAgent_full, salesAgent_name, quoteReference.salesAgent.firstName, quoteReference.salesAgent.lastName,  quoteReference.salesAgent.email, quoteReference.organizer.firstName, quoteReference.organizer.lastName, quoteReference.organizer.email, quoteReference.views, quoteReference.shareViews, quoteReference.converted, deleted, locked, quoteReference.setupComplete, quoteReference.destination, created, version, duration, displayName, tripStatus.name, expiryDate, transportType.name, boardBasis.name, freePlaces, payingPlaces, departureDate, returnDate, pricePerson,  currency.name, converted, views, shareViews, hideAlt, description", filterable=false, sortable=true)
 * @GRID\Column(id="organizer_full", type="join", columns = {"quoteReference.organizer.firstName", "quoteReference.organizer.lastName", "quoteReference.organizer.email"}, title="Organizer", export=false, filterable=false, operatorsVisible=false)
 * @GRID\Column(id="salesAgent_full", type="join", columns = {"quoteReference.salesAgent.firstName", "quoteReference.salesAgent.lastName", "quoteReference.salesAgent.email"}, title="Primary Business Admin", export=false, filterable=false, operatorsVisible=false)
 * @GRID\Column(id="salesAgent_name", type="join", columns = {"quoteReference.salesAgent.firstName", "quoteReference.salesAgent.lastName"}, title="Primary Business Admin", export=false, filterable=false, operatorsVisible=false)
 * @GRID\Column(id="institution_full", type="join", columns = {"quoteReference.institution.name", "quoteReference.institution.city"}, title="Institution", export=false, filterable=false, operatorsVisible=false)
 */

// ,uniqueConstraints={@ORM\UniqueConstraint(name="quoteNumber", columns={"quoteNumber"})}
class QuoteVersion
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
     * @GRID\Column(title="Quote Name", filterable=true, operatorsVisible=false, export=true)
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
   * @var boolean
   *
   * @ORM\Column(name="converted", type="boolean")
   * @GRID\Column(visible=false, filterable=false, export=true)
   */
  private $converted = false;

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
   * @ORM\Column(name="isTemplate", type="boolean")
   * @GRID\Column(visible=false, filterable=false, export=true)
   */
  private $isTemplate = false;

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
     * @GRID\Column(title="Expiry Date", export=true)
     */
    private $expiryDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="freePlaces", type="integer", nullable=true)
     * @GRID\Column(title="Free Places", export=true)
     * @Assert\GreaterThanOrEqual(value = 0)
     */
    private $freePlaces;

    /**
     * @var integer
     *
     * @ORM\Column(name="payingPlaces", type="integer", nullable=true)
     * @GRID\Column(title="Paying Places", export=true)
     * @Assert\GreaterThanOrEqual(value = 0)
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
     * @GRID\Column(field = "quoteReference.salesAgent.firstName", title="BA First", export=true)
     * @GRID\Column(field = "quoteReference.salesAgent.lastName", title="BA Last", export=true)
     * @GRID\Column(field = "quoteReference.salesAgent.email", title="BA email", export=true)
     * @GRID\Column(field = "quoteReference.converted", title="Converted", export=true)
     * @GRID\Column(field = "quoteReference.setupComplete", title="Setup Complete", export=true)
     * @GRID\Column(field = "quoteReference.institution.name", title="Institution Name", export=true, filterable=true, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.institution.city", title="Institution City", export=true, filterable=false, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.organizer.firstName", title="Organiser first", export=true)
     * @GRID\Column(field = "quoteReference.organizer.lastName", title="Organiser last", export=true)
     * @GRID\Column(field = "quoteReference.organizer.email", title="Organiser email", export=true)
     * @GRID\Column(field = "quoteReference.destination", title="Destination", export=true, filterable=false, operatorsVisible=false)
     */
    private $quoteReference;

    /**
     * @var integer
     *
     * @ORM\Column(name="quoteDays", type="integer", nullable=true)
     * @GRID\Column(title="Days", export=true)
     */
    private $quoteDays;

    /**
     * @var integer
     *
     * @ORM\Column(name="quoteNights", type="integer", nullable=true)
     * @GRID\Column(title="Nights", export=true)
     */
    private $quoteNights;

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
     * @var string
     *
     * @ORM\Column(name="displayName", type="text", nullable=true)
     * @GRID\Column(title="Display Name", export=true)
     */
    private $displayName;

    /**
     * @var float
     *
     * @ORM\Column(name="pricePerson", type="float", nullable=true)
     * @GRID\Column(title="Price / Person", export=true)
     * @Assert\GreaterThanOrEqual(value = 0)
     */
    private $pricePerson;

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
     * Get id
     *
     * @return integer
     */

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
     * @ORM\Column(name="shareViews", type="integer")
     * @GRID\Column(title="Shared Views", filterable=false, export=true)
     */
    private $shareViews;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hideAlt", type="boolean")
     * @GRID\Column(visible=false, filterable=false, export=false)
     */
    private $hideAlt = false;


  /**
   * @var string
   *
   * @ORM\Column(name="description", type="text", nullable=true)
   * @GRID\Column(title="Description", export=true)
   */
  private $description;



  public function __construct()
  {
    $this->created = new \DateTime();
    $this->views = 0;
    $this->shareViews = 0;
  }

    /**
     * @Assert\Callback
     */
    public function isExpiryBeforeDeparture(ExecutionContextInterface $context)
    {
      if ($this->getExpiryDate() != NULL) {
        if ($this->getExpiryDate() >= $this->getDepartureDate()) {
          $context->buildViolation('The expiry date must be prior to the departure date.')
              ->atPath('expiryDate')
              ->addViolation();
        }
      }
    }

  /**
   * @Assert\Callback
   */
  public function isDepartureBeforeReturn(ExecutionContextInterface $context)
  {
    if ($this->getDepartureDate() != NULL) {
      if ($this->getDepartureDate() >= $this->getReturnDate()) {
        $context->buildViolation('The departure date must be before the return date.')
            ->atPath('departureDate')
            ->addViolation();
      }
    }
  }


  /**
   * @Assert\Callback
   */
  public function isExpiryBeforeNow(ExecutionContextInterface $context)
  {
    $now = new \DateTime('now');
    if ($this->getExpiryDate() != NULL) {
      if ($now >= $this->getExpiryDate()) {
        $context->buildViolation('The expiry date must be in the future.')
            ->atPath('expiryDate')
            ->addViolation();
      }
    }
  }

    public function getId()
    {
        return $this->id;
    }


  /**
   * Set ID
   *
   * @param integer $id
   * @return QuoteVersion
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
     * Set boardBasis
     *
     * @param integer $boardBasis
     * @return QuoteVersion
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
   * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * Set quoteDays
     *
     * @param integer $quoteDays
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * @return QuoteVersion
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
   * @return QuoteVersion
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
   * Set displayName
   *
   * @param string $displayName
   * @return QuoteVersion
   */
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;

    return $this;
  }

  /**
   * Get displayName
   *
   * @return string
   */
  public function getDisplayName()
  {
    return $this->displayName;
  }




  /**
   * Set pricePerson
   *
   * @param string $pricePerson
   * @return QuoteVersion
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
   * Set returnDate
   *
   * @param string $returnDate
   * @return QuoteVersion
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
   * @return QuoteVersion
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
   * @return QuoteVersion
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
   * @return QuoteVersion
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
   * Set reference
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
   * Get reference
   *
   * @return string
   */
  public function getQuoteNumber()
  {
    return $this->quoteNumber;
  }

  /**
   * Set converted
   *
   * @param boolean $converted
   * @return Quote
   */
  public function setConverted($converted)
  {
    $this->converted = $converted;

    return $this;
  }

  /**
   * Get converted
   *
   * @return boolean
   */
  public function getConverted()
  {
    return $this->converted;
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
   * Set isTemplate
   *
   * @param boolean $isTemplate
   * @return Quote
   */
  public function setIsTemplate($isTemplate)
  {
    $this->isTemplate = $isTemplate;

    return $this;
  }

  /**
   * Get isTemplate
   *
   * @return boolean
   */
  public function getIsTemplate()
  {
    return $this->isTemplate;
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
   * Set shareViews
   *
   * @param integer $shareViews
   * @return Quote
   */
  public function setShareViews($shareViews)
  {
    $this->shareViews = $shareViews;

    return $this;
  }

  /**
   * Get shareViews
   *
   * @return integer
   */
  public function getShareViews()
  {
    return $this->shareViews;
  }

  /**
   * Set hideAlt
   *
   * @param boolean $hideAlt
   * @return Quote
   */
  public function setHideAlt($hideAlt)
  {
    $this->hideAlt = $hideAlt;

    return $this;
  }

  /**
   * Get hideAlt
   *
   * @return boolean
   */
  public function getHideAlt()
  {
    return $this->hideAlt;
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
   * Set description
   *
   * @param string $description
   * @return QuoteVersion
   */
  public function setDescription($description)
  {
    $this->description = $description;

    return $this;
  }

  /**
   * Get description
   *
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }


}
