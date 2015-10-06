<?php

namespace TUI\Toolkit\ContentBlocksBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
    public $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    public $locked;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hidden", type="boolean")
     */
    public $hidden;

    /**
     *
     * @var \TUI\Toolkit\ContentBlocksBundle\Entity\LayoutType
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\ContentBlocksBundle\Entity\LayoutType", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="layouttype", referencedColumnName="id")
     *
     */
    public $layoutType;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    public $title;

    /**
     * @var longtext
     *
     * @ORM\Column(name="body", type="text", length=4294967295, nullable=true)
     */
    public $body;

    /**
     * @var integer
     *
     * @ORM\Column(name="sortOrder", type="integer", nullable=true)
     */
    public $sortOrder;

    /**
     * @var boolean
     *
     * @ORM\Column(name="doubleWidth", type="boolean")
     */
    public $doubleWidth;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isSlideshow", type="boolean")
     */
    public $isSlideshow;

    public function __construct(){
      $this->hidden = false;
      $this->locked = false;
      $this->doubleWidth = false;
      $this->isSlideshow = false;
      $this->sortOrder = 1;
      $this->body = "";
      $this->mediaWrapper = new ArrayCollection();
    }

  /**
   * Set id
   *
   * @return ContentBlock
   */
  public function setId($id)
  {
    $this->id=$id;
    return $this;
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
     * @param text $body
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
     * @return text
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
   * Set isSlideshow
   *
   * @param boolean $doubleWidth
   * @return ContentBlock
   */
  public function setIsSlideshow($isSlideshow)
  {
    $this->isSlideshow = $isSlideshow;

    return $this;
  }

  /**
   * Get isSlideshow
   *
   * @return boolean
   */
  public function getIsSlideshow()
  {
    return $this->isSlideshow;
  }


  /**
     * @var \TUI\Toolkit\MediaBundle\Entity\MediaWrapper
     * @ORM\ManyToMany(targetEntity="TUI\Toolkit\MediaBundle\Entity\MediaWrapper", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="mediawrapper", referencedColumnName="id")
     */
    protected $mediaWrapper;

    /**
     * @param $mediaWrapper
     */

    public function addMediaWrapper($mediaWrapper)
    {

        $this->mediaWrapper[] = $mediaWrapper;
        return $this;
    }

    /**
     * @param  $mediaWrapper
     */

    public function setMediaWrapper($mediaWrapper)
    {
        $this->mediaWrapper = $mediaWrapper;

        return $mediaWrapper;
    }

    /**
     * @return mediaWrapper
     */
    public function getMediaWrapper()
    {
        return $this->mediaWrapper;
    }

}
