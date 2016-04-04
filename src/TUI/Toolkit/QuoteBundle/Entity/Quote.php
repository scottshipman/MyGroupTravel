<?php

namespace TUI\Toolkit\QuoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Annotations;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Quote
 *
 * @ORM\Table(name="quote")
 * @ORM\Entity
 * @GRID\Source(columns="id, name, destination, institution.name, orgfullname, organizer.firstName, organizer.lastName, bizfullname, salesAgent.firstName, salesAgent.lastName, secondaryContact.firstName, secondaryContact.lastName, secondaryContact.email, converted, setupComplete", filterable=false, sortable=true)
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
     * @Assert\NotBlank()
     * @GRID\Column(title="Tour Name", filterable=true, operatorsVisible=false, export=true)
     *
     */
    private $name;


    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="organizer", referencedColumnName="id")
     * @GRID\Column(field="organizer.firstName", type="text", title="Organizer First", export=true)
     * @GRID\Column(field="organizer.lastName", type="text", title="Organizer Last", export=true)
     */
    private $organizer;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $converted = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="setupComplete", type="boolean")
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $setupComplete = false;


    /**
     * @var integer
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="salesAgent", referencedColumnName="id")
     * @GRID\Column(field="salesAgent.firstName", type="text", title="Business Admin first", filterable=false, export=true)
     * @GRID\Column(field="salesAgent.lastName", type="text", title="Business Admin last", filterable=false, export=true)
     *
     */
    private $salesAgent;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="secondaryContact", referencedColumnName="id")
     * @GRID\Column(field="secondaryContact.firstName", type="text", title="Secondary Contact first", filterable=false, export=true)
     * @GRID\Column(field="secondaryContact.lastName", type="text", title="Secondary Contact last", filterable=false, export=true)
     * @GRID\Column(field="secondaryContact.email", type="text", title="Secondary Contact email", filterable=false, export=true)
     */
    private $secondaryContact;

    /**
     * @var integer
     * @ORM\JoinColumn(name="institution", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\InstitutionBundle\Entity\Institution", cascade={"persist"}, fetch="EAGER")
     * @GRID\Column(field="institution.name", title="Institution", filterable=true, operatorsVisible=false, export=true)
     */
    private $institution;


  /**
   * @var string
   *
   * @ORM\Column(name="destination", type="string", nullable=true)
   * @GRID\Column(title="Destination", filterable=true, operatorsVisible=false, sortable=true, export=true)
   */
  private $destination;

    /**
     * @var \TUI\Toolkit\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="media", referencedColumnName="id")
     */
    protected $media;

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
   * Set secondaryContact
   *
   * @param integer $secondaryContact
   * @return Quote
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
     * @param $media
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
}
