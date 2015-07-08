<?php
// http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html
// or https://sonata-project.org/bundles/media/master/doc/reference/installation.html
// &  https://github.com/liip/LiipImagineBundle
// for cookbook on handling images

namespace TUI\Toolkit\BrandBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\MediaBundle\Model\MediaInterface;

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
     * @ORM\Column(name="buttonColor", type="string", length=32)
     */
    private $buttonColor;

    /**
     * @var string
     *
     * @ORM\Column(name="hoverColor", type="string", length=32)
     */
    private $hoverColor;


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
     * Set buttonColor
     *
     * @param string $buttonColor
     * @return Brand
     */
    public function setButtonColor($buttonColor)
    {
        $this->buttonColor = $buttonColor;

        return $this;
    }

    /**
     * Get buttonColor
     *
     * @return string
     */
    public function getButtonColor()
    {
        return $this->buttonColor;
    }

    /**
     * Set hoverColor
     *
     * @param string $hoverColor
     * @return Brand
     */
    public function setHoverColor($hoverColor)
    {
        $this->hoverColor = $hoverColor;

        return $this;
    }

    /**
     * Get hoverColor
     *
     * @return string
     */
    public function getHoverColor()
    {
        return $this->hoverColor;
    }
    

    /**
     * @var \Application\Sonata\MediaBundle\Entity\Media
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
     */
    protected $media;

    /**
     * @param MediaInterface $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return MediaInterface
     */
    public function getMedia()
    {
        return $this->media;
    }


}
