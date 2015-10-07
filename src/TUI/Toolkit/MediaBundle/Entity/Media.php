<?php


namespace TUI\Toolkit\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * @ORM\Entity
 * @ORM\Table(name="media")
 */
class Media
{
    /**
     * @var integer $id
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var  $string $context
     * @ORM\Column(type="string", nullable=true)
     */
    protected $context;

    /**
     * @var string $filename
     * @ORM\Column(type="string", nullable=true)
     */
    protected $filename;


    /**
     * @var string $hashed_filename
     * @ORM\Column(type="string", nullable=true)
     */
    protected $hashedFilename;

    /**
     * @var string $filepath
     * @ORM\Column(type="string", nullable=true)
     */
    protected $filepath;


    /**
     * @var string $relativepath
     * @ORM\Column(type="string", nullable=true)
     */
    protected $relativepath;


    /**
     * @var string $mimetype
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mimetype;


    /**
     * @var string $filesize
     * @ORM\Column(type="string", nullable=true)
     */
    protected $filesize;

    /**
     * Bidirectional (INVERSE SIDE)
     *
     * @ORM\ManyToMany(targetEntity="MediaWrapper", mappedBy="media")
     */
    private $mediaWrappers;


  public function __toString() {
    if(null !== $this->filename){
      return $this->filename;
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
   * Set context
   *
   * @return media
   */
  public function setContext($context)
  {
    $this->context=$context;
    return $this;
  }

  /**
   * Get context
   *
   * @return string $context
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Set filename
   *
   * @return media
   */
  public function setFilename($filename)
  {
    $this->filename=$filename;
    return $this;
  }

  /**
   * Get filename
   *
   * @return string $filename
   */
  public function getFilename()
  {
    return $this->filename;
  }

  /**
   * Set hashedFilename
   *
   * @return media
   */
  public function setHashedFilename($hashedFilename)
  {
    $this->hashedFilename=$hashedFilename;
    return $this;
  }

  /**
   * Get hashedFilename
   *
   * @return string $hashedFilename
   */
  public function getHashedFilename()
  {
    return $this->hashedFilename;
  }


  /**
   * Set filepath
   *
   * @return media
   */
  public function setFilepath($filepath)
  {
    $this->filepath=$filepath;
    return $this;
  }

  /**
   * Get filepath
   *
   * @return string $filepath
   */
  public function getFilepath()
  {
    return $this->filepath;
  }

  /**
   * Set relativepath
   *
   * @return media
   */
  public function setRelativepath($relativepath)
  {
    $this->relativepath=$relativepath;
    return $this;
  }

  /**
   * Get relativepath
   *
   * @return string $relativepath
   */
  public function getRelativepath()
  {
    return $this->relativepath;
  }

  /**
   * Set mimetype
   *
   * @return media
   */
  public function setMimetype($mimetype)
  {
    $this->mimetype=$mimetype;
    return $this;
  }

  /**
   * Get mimetype
   *
   * @return string $mimetype
   */
  public function getMimetype()
  {
    return $this->mimetype;
  }

  /**
   * Set filesize
   *
   * @return media
   */
  public function setFilesize($filesize)
  {
    $this->filesize=$filesize;
    return $this;
  }

  /**
   * Get filesize
   *
   * @return string $filesize
   */
  public function getFilesize()
  {
    return $this->filesize;
  }

    /**
     * Set mediaWrappers
     *
     * @return media
     */
    public function setMediaWrappers($mediaWrappers)
    {
        $this->mediaWrappers=$mediaWrappers;
        return $this;
    }

    /**
     * Get mediaWrappers
     *
     * @return string $filesize
     */
    public function getMediaWrappers()
    {
        return $this->mediaWrappers;
    }
}