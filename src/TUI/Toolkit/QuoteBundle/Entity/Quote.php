<?php

namespace TUI\Toolkit\QuoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Annotations;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Quote
 *
 * @ORM\Table(name="quote")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 * @GRID\Source(columns="id, name, destination, reference, institution.name, created, views, shareViews, orgfullname, organizer.firstName, organizer.lastName, bizfullname, salesAgent.firstName, salesAgent.lastName, converted, deleted, setupComplete, locked, isTemplate", filterable=false, sortable=true)
 * @GRID\Column(id="bizfullname", type="join", title="Business Admin", columns={"salesAgent.firstName", "salesAgent.lastName"}, filterable=true, operatorsVisible=false)
 * @GRID\Column(id="orgfullname", type="join", title="Organizer", columns={"organizer.firstName", "organizer.lastName"}, filterable=true, operatorsVisible=false)
 */
class Quote
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @GRID\Column(title="Name", filterable=true, operatorsVisible=false, export=true)
     *
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255)
     * @GRID\Column(title="Quote Reference", filterable=true, operatorsVisible=false, export=true)
     */
    private $reference;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="organizer", referencedColumnName="id")
     * @GRID\Column(field="organizer.firstName", type="text", title="Organizer First", export=true)
     * @GRID\Column(field="organizer.lastName", type="text", title="Organizer Last", export=true)
     */
    private $organizer;

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
     * @ORM\Column(name="setupComplete", type="boolean")
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $setupComplete = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $locked = false;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="salesAgent", referencedColumnName="id")
     * @GRID\Column(field="salesAgent.firstName", type="text", title="Business Admin first", filterable=false, export=true)
     * @GRID\Column(field="salesAgent.lastName", type="text", title="Business Admin last", filterable=false, export=true)
     *
     */
    private $salesAgent;

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
     * @var integer
     * @ORM\JoinColumn(name="institution", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\InstitutionBundle\Entity\Institution", cascade={"all"}, fetch="EAGER")
     * @GRID\Column(field="institution.name", title="Institution", filterable=true, operatorsVisible=false, export=true)
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
     * @var \TUI\Toolkit\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     *
     */
    protected $media;

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
   * Set destination
   *
   * @param integer $destination
   * @return Quote
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
     * @param MediaInterface $media
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $media;
    }

    /**
     * @return MediaInterface
     */
    public function getMedia()
    {
        return $this->media;
    }
}
