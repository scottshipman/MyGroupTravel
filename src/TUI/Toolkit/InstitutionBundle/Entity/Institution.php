<?php

namespace TUI\Toolkit\InstitutionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\MediaBundle\Model\Media;
use Application\Sonata\MediaBundle;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Institution
 *
 * @ORM\Table()
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 * @GRID\Source(columns="id, location, name, type, localAuthority, code, city, state, country", filterable=false, sortable=true)
 * @GRID\Column(id="location", type="join", title="Location", columns={"city", "state", "country"}, filterable=true, operatorsVisible=false)
 */
class Institution
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var date
     *
     * @ORM\Column(name="deleted", type="date", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     *
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @GRID\Column(title="Name", visible=true, filterable=true, operatorsVisible=false, export=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="string", length=255, nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=255)
     * @GRID\Column(visible=false, filterable=false, export=true, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="county", type="string", length=255, nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $county;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="post_code", type="string", length=255, nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $postCode;

    /**
     * @var string
     *
     * @ORM\Column(name="localAuthority", type="string", length=255, nullable=true)
     * @GRID\Column(title="Local Authority", visible=true, filterable=true, operatorsVisible=false, export=true)
     */
    private $localAuthority;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $country;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", nullable=true)
     * @GRID\Column(title="Code", visible=true, filterable=true, operatorsVisible=false, export=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     * @GRID\Column(title="Type", visible=true, filterable=true, operatorsVisible=false, export=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="websiteAddress", type="string", length=255, nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    private $websiteAddress;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        if(null !== $this->name){return $this->name;}
      else { return '';}
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Institution
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
     * Set address1
     *
     * @param string $address1
     * @return Institution
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string 
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     * @return Institution
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string 
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Institution
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set county
     *
     * @param string $county
     * @return Institution
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return string 
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Institution
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set postCode
     *
     * @param string $postCode
     * @return Institution
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    /**
     * Get postCode
     *
     * @return string 
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * Set localAuthority
     *
     * @param string $localAuthority
     * @return Institution
     */
    public function setLocalAuthority($localAuthority)
    {
        $this->localAuthority = $localAuthority;

        return $this;
    }

    /**
     * Get localAuthority
     *
     * @return string 
     */
    public function getLocalAuthority()
    {
        return $this->localAuthority;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Institution
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set code
     *
     * @param integer $code
     * @return Institution
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return integer 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Institution
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set websiteAddress
     *
     * @param string $websiteAddress
     * @return Institution
     */
    public function setWebsiteAddress($websiteAddress)
    {
        $this->websiteAddress = $websiteAddress;

        return $this;
    }

    /**
     * Get websiteAddress
     *
     * @return string 
     */
    public function getWebsiteAddress()
    {
        return $this->websiteAddress;
    }

    /**
     * @var \TUI\Toolkit\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="media", referencedColumnName="id")
     */
    protected $media;

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
   * Set deleted
   *
   * @param date $deleted
   * @return Institution
   */
  public function setDeleted($deleted)
  {
    $this->deleted = $deleted;

    return $this;
  }

  /**
   * Get deleted
   *
   * @return date
   */
  public function getDeleted()
  {
    return $this->deleted;
  }
}
