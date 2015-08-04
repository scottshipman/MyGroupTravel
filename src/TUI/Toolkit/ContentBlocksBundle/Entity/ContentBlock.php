<?php

namespace TUI\Toolkit\ContentBlocksBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentBlock
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ContentBlock
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
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    private $locked;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hidden", type="boolean")
     */
    private $hidden;

    /**
     * @var integer
     *
     * @ORM\Column(name="layoutType", type="integer")
     *
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\ContentBlocksBundle\Entity\LayoutType", cascade={"all"}, fetch="EAGER", inversedBy = "id")
     */
    private $layoutType;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var blob
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var integer
     *
     * @ORM\Column(name="sortOrder", type="integer")
     */
    private $sortOrder;

    /**
     * @var boolean
     *
     * @ORM\Column(name="doubleWidth", type="boolean")
     */
    private $doubleWidth;


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
     * Set locked
     *
     * @param boolean $locked
     * @return ContentBlock
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
     * Set hidden
     *
     * @param boolean $hidden
     * @return ContentBlock
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean 
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set layoutType
     *
     * @param integer $layoutType
     * @return ContentBlock
     */
    public function setLayoutType($layoutType)
    {
        $this->layoutType = $layoutType;

        return $this;
    }

    /**
     * Get layoutType
     *
     * @return integer 
     */
    public function getLayoutType()
    {
        return $this->layoutType;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ContentBlock
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param blob $body
     * @return ContentBlock
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return blob
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return ContentBlock
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set doubleWidth
     *
     * @param boolean $doubleWidth
     * @return ContentBlock
     */
    public function setDoubleWidth($doubleWidth)
    {
        $this->doubleWidth = $doubleWidth;

        return $this;
    }

    /**
     * Get doubleWidth
     *
     * @return boolean 
     */
    public function getDoubleWidth()
    {
        return $this->doubleWidth;
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

}
