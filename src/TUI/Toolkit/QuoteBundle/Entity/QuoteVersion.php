<?php

namespace TUI\Toolkit\QuoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuoteVersion
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class QuoteVersion
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
     * @var integer
     *
     * @ORM\Column(name="boardBasis", type="integer")
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\BoardBasisBundle\Entity\BoardBasis", cascade={"all"}, fetch="EAGER", inversedBy = "id")
     */
    private $boardBasis;

    /**
     * @var array
     *
     * @ORM\Column(name="content", type="json_array")
     */
    private $content;

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
     * @var integer
     *
     * @ORM\Column(name="parent", type="integer")
     */
    private $parent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="signupDeadline", type="date")
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\QuoteBundle\Entity\Quote", cascade={"all"}, fetch="EAGER", inversedBy = "id")
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
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TripStatusBundle\Entity\TripStatus", cascade={"all"}, fetch="EAGER", inversedBy = "id")
     */
    private $tripStatus;

    /**
     * @var integer
     *
     * @ORM\Column(name="transportType", type="integer")
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TransportBundle\Entity\Transport", cascade={"all"}, fetch="EAGER", inversedBy = "id")
     */
    private $transportType;

    /**
     * @var string
     *
     * @ORM\Column(name="welcomeMsg", type="text")
     */
    private $welcomeMsg;

  /**
   * @var float
   *
   * @ORM\Column(name="pricePerson", type="float")
   */
  private $pricePerson;

  /**
   * @var DateTime
   *
   * @ORM\Column(name="returnDate", type="date")
   */
  private $returnDate;

  /**
   * @var integer
   *
   * @ORM\Column(name="currency", type="integer")
   *
   * @ORM\ManyToOne(targetEntity="TUI\Toolkit\CurrencyBundle\Entity\Currency", cascade={"all"}, fetch="EAGER", inversedBy = "id")
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
     * Set parent
     *
     * @param integer $parent
     * @return QuoteVersion
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return integer 
     */
    public function getParent()
    {
        return $this->parent;
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
}
