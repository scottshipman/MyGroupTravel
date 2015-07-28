<?php

namespace TUI\Toolkit\QuoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Annotations;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * QuoteVersion
 *
 * @ORM\Table()
 * @ORM\Entity
 * @GRID\Source(columns="id, quoteReference.ts, quoteReference.id, quoteReference.institution.name, quoteReference.reference, organizer_full, quoteReference.name, salesAgent_full, quoteReference.salesAgent.firstName, quoteReference.salesAgent.lastName,  quoteReference.salesAgent.email, quoteReference.organizer.firstName, quoteReference.organizer.lastName, quoteReference.organizer.email, quoteReference.views, quoteReference.shareViews, quoteReference.converted, quoteReference.deleted, quoteReference.locked, quoteReference.setupComplete, quoteReference.destination, quoteReference.created, version, tripStatus.name, expiryDate, signupDeadline, transportType.name, boardBasis.name, freePlaces, payingPlaces, maxPax, minPax, departureDate, returnDate, quoteDays, quoteNights, welcomeMsg, totalPrice, pricePerson,  currency.name", filterable=false, sortable=true)
 * @GRID\Column(id="organizer_full", type="join", columns = {"quoteReference.organizer.firstName", "quoteReference.organizer.lastName", "quoteReference.organizer.email"}, title="Organizer", export=true, filterable=true, operatorsVisible=false)
 * @GRID\Column(id="salesAgent_full", type="join", columns = {"quoteReference.salesAgent.firstName", "quoteReference.salesAgent.lastName", "quoteReference.salesAgent.email"}, title="Business Admin", export=true, filterable=true, operatorsVisible=false)
 */

// Quote columns = quoteReference.name, quoteReference.destination, quoteReference.reference, quoteReference.institution.name, quoteReference.created, quoteReference.views, quoteReference.shareViews, quoteReference.organizer.firstName, quoteReference.organizer.lastName, quoteReference.salesAgent.firstName, quoteReference.salesAgent.lastName, quoteReference.converted, quoteReference.deleted, quoteReference.setupComplete, quoteReference.locked, quoteReference.isTemplate
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
     * @var integer
     *
     * @ORM\Column(name="version", type="integer")
     * @GRID\Column(field="version", title="Version", export=true)
     */
    private $version = 1;


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
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\BoardBasisBundle\Entity\BoardBasis", cascade={"all"}, fetch="EAGER")
     */
    private $boardBasis;

    /**
     * @var array
     * @GRID\Column(title="Content", export=true)
     *
     * @ORM\Column(name="content", type="json_array", nullable=true)
     */
    private $content;

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
     * @ORM\Column(name="maxPax", type="integer", nullable=true)
     * @GRID\Column(title="Max Pax", export=true)
     */
    private $maxPax;

    /**
     * @var integer
     *
     * @ORM\Column(name="minPax", type="integer", nullable=true)
     * @GRID\Column(title="Min Pax", export=true)
     */
    private $minPax;

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
     * @GRID\Column(field = "quoteReference.name", title="Name", export=true, filterable=true, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.id", title="ID", export=true)
     *
     * @GRID\Column(field = "quoteReference.salesAgent.firstName", title="BA First", export=false)
     * @GRID\Column(field = "quoteReference.salesAgent.lastName", title="BA Last", export=false)
     * @GRID\Column(field = "quoteReference.salesAgent.email", title="BA email", export=false)
     * @GRID\Column(field = "quoteReference.reference", title="Reference", export=true, filterable=true, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.converted", title="Converted", export=true)
     * @GRID\Column(field = "quoteReference.deleted", title="Deleted", export=true)
     * @GRID\Column(field = "quoteReference.locked", title="Locked", export=true)
     * @GRID\Column(field = "quoteReference.setupComplete", title="Setup Complete", export=true)
     * @GRID\Column(field = "quoteReference.views", title="Views", export=true)
     * @GRID\Column(field = "quoteReference.shareViews", title="Shared Views", export=true)
     * @GRID\Column(field = "quoteReference.institution.name", title="Institution", export=true, filterable=true, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.organizer.firstName", title="O first", export=false)
     * @GRID\Column(field = "quoteReference.organizer.lastName", title="O last", export=false)
     * @GRID\Column(field = "quoteReference.organizer.email", title="O email", export=false)
     * @GRID\Column(field = "quoteReference.destination", title="Destination", export=true, filterable=true, operatorsVisible=false)
     * @GRID\Column(field = "quoteReference.created", title="Created", type="date", export=true)
     */
    private $quoteReference;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="signupDeadline", type="date", nullable=true)
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\QuoteBundle\Entity\Quote", cascade={"all"}, fetch="EAGER", inversedBy = "id")
     * @ORM\JoinColumn(name="tripStatus", referencedColumnName="id")
     * @GRID\Column(title="Signup Deadline", export=true)
     */
    private $signupDeadline;

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
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TripStatusBundle\Entity\TripStatus", cascade={"all"}, fetch="EAGER")
     * @GRID\Column(field="tripStatus.name", title="Trip Status", export=true)
     */
    private $tripStatus;

    /**
     * @var integer
     *
     * @ORM\JoinColumn(name="transportType", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TransportBundle\Entity\Transport", cascade={"all"}, fetch="EAGER")
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
   * @var float
   *
   * @ORM\Column(name="pricePerson", type="float", nullable=true)
   * @GRID\Column(title="Price / Person", export=true)
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
   * @ORM\ManyToOne(targetEntity="TUI\Toolkit\CurrencyBundle\Entity\Currency", cascade={"all"}, fetch="EAGER")
   * @GRID\Column(field="currency.name", title="Currency", export=true)
   *
   */
  private $currency;
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
     * Set maxPax
     *
     * @param integer $maxPax
     * @return QuoteVersion
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
     * @return QuoteVersion
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
     * Set signupDeadline
     *
     * @param \DateTime $signupDeadline
     * @return QuoteVersion
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
}
