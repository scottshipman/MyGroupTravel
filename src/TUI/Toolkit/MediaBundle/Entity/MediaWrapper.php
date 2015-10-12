<?php


namespace TUI\Toolkit\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * @ORM\Entity
 * @ORM\Table(name="mediawrapper")
 */
class MediaWrapper
{
    /**
     * @var integer $id
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var  $string $title
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer", nullable=true)
     */
    protected $weight;

    /**
     * Bidirectional - Many media have many mediaWrappers (OWNING SIDE)
     *
     * @ORM\ManyToMany(targetEntity="TUI\Toolkit\MediaBundle\Entity\Media", inversedBy="mediaWrappers")
     * @ORM\JoinTable(name="media_wrapper_media",
     *      joinColumns={@ORM\JoinColumn(name="media_wrapper_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="media_id", referencedColumnName="id")}
     *  )
     */
    protected $media;

    /**
     * @param $media
     */


    public function __toString() {
        if(null !== $this->title){
            return $this->title;
        } else {return '';}
    }

    /**
     * Set id
     *
     * @return media
     */
    public function setId($id)
    {
        $this->id=$id;
        return $this;
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @return media
     */
    public function setTitle($title)
    {
        $this->title=$title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set weight
     *
     * @return mediaWrapper
     */
    public function setWeight($weight)
    {
        $this->weight=$weight;
        return $this;
    }

    /**
     * Get weight
     *
     * @return string $weight
     */
    public function getWeight()
    {
        return $this->weight;
    }


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