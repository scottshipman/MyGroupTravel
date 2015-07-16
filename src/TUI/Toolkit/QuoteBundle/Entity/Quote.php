<?php

namespace TUI\Toolkit\QuoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Annotations;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Quote
 *
 * @ORM\Table(name="quote", uniqueConstraints={@ORM\UniqueConstraint(name="reference", columns={"reference"})})
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 *
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="organizer", type="string", length=255)
     */
    private $organizer;

    /**
     * @var boolean
     *
     * @ORM\Column(name="converted", type="boolean")
     */
    private $converted;

    /**
     * @var date
     *
     * @ORM\Column(name="deleted", type="date", nullable=true)
     */
    private $deleted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="setupComplete", type="boolean")
     */
    private $setupComplete;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    private $locked;

    /**
     * @var integer
     *
     * @ORM\Column(name="salesAgent", type="integer")
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"all"}, fetch="EAGER", inversedBy = "id")
     */
    private $salesAgent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isTemplate", type="boolean")
     *
     */
    private $isTemplate;


  /**
   * @var DateTime
   *
   * @ORM\Column(name="created", type="date")
   *
   */
  private $created;

  /**
   * @var integer
   *
   * @ORM\Column(name="views", type="integer")
   *
   */
  private $views;

  /**
 * @var integer
 *
 * @ORM\Column(name="shareViews", type="integer")
 *
 */
  private $shareViews;

  /**
   * @var integer
   *
   * @ORM\Column(name="institution", type="integer")
   *
   * @ORM\ManyToOne(targetEntity="TUI\Toolkit\InstitutionBundle\Entity\Institution", cascade={"all"}, fetch="EAGER", inversedBy = "id")
   *
   */
  private $institution;

  public function __construct()
  {
    $this->created = new \DateTime();
    $this->views = 0;
    $this->shareViews = 0;
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
     * Set organizer
     *
     * @param string $organizer
     * @return Quote
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
     * Set salesAgent
     *
     * @param integer $salesAgent
     * @return Quote
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
  public function setShareViewsiews($shareViews)
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
   * Set institution
   *
   * @param integer $institution
   * @return Quote
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
   * Set created
   *
   * @param date $created
   * @return Quote
   */
  public function setCreated($created)
  {
    if(!$created){
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
}
