<?php
// http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html
// or https://sonata-project.org/bundles/media/master/doc/reference/installation.html
// &  https://github.com/liip/LiipImagineBundle
// for cookbook on handling images

namespace TUI\Toolkit\BrandBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Brand
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TUI\Toolkit\BrandBundle\Entity\BrandRepository")
 */
class Brand
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
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="division", type="string", length=100)
     */
    private $division;

    /**
     * @var string
     *
     * @ORM\Column(name="primaryColor", type="string", length=32)
     */
    private $primaryColor;

    /**
     * @var string
     *
     * @ORM\Column(name="secondaryColor", type="string", length=32)
     */
    private $secondaryColor;

    // @var string
    // @ORM\Column(name="hoverColor", type="string", length=32)
    // private $hoverColor; */

    /**
     * @var longtext
     *
     * @ORM\Column(name="footerBody", type="text", length=4294967295, nullable=true)
     */
    private $footerBody;

    /**
     * @var longtext
     *
     * @ORM\Column(name="terms", type="text", length=4294967295, nullable=true)
     */
    private $terms;


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
     * @return Brand
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
     * Set division
     *
     * @param string $division
     * @return Brand
     */
    public function setDivision($division)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division
     *
     * @return string
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Set primaryColor
     *
     * @param string $primaryColor
     * @return Brand
     */
    public function setPrimaryColor($primaryColor)
    {
        $this->primaryColor = $primaryColor;

        return $this;
    }

    /**
     * Get primaryColor
     *
     * @return string
     */
    public function getPrimaryColor()
    {
        return $this->primaryColor;
    }

    /**
     * Set secondaryColor
     *
     * @param string $secondaryColor
     * @return Brand
     */
    public function setSecondaryColor($secondaryColor)
    {
        $this->secondaryColor = $secondaryColor;

        return $this;
    }

    /**
     * Get secondaryColor
     *
     * @return string
     */
    public function getSecondaryColor()
    {
        return $this->secondaryColor;
    }

    /*
     * Set hoverColor
     *
     * @param string $hoverColor
     * @return Brand

    public function setHoverColor($hoverColor)
    {
        $this->hoverColor = $hoverColor;

        return $this;
    }

    /**
     * Get hoverColor
     *
     * @return string

    public function getHoverColor()
    {
        return $this->hoverColor;
    } */

    /**
     * Set footerBody
     *
     * @param text $footerBody
     * @return Brand
     */
    public function setFooterBody($footerBody)
    {
        $this->footerBody = $footerBody;

        return $this;
    }

    /**
     * Get footerBody
     *
     * @return text
     */
    public function getFooterBody()
    {
        return $this->footerBody;
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
     * Set terms
     *
     * @param text $terms
     * @return Brand
     */
    public function setTerms($terms)
    {
      $this->terms = $terms;

      return $this;
    }

    /**
     * Get terms
     *
     * @return text
     */
    public function getTerms()
    {
      return $this->terms;
    }
}
