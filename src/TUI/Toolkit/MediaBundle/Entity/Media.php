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
     * @var  $string context
     * @ORM\Column(type="string", nullable=true)
     */
    private $context;

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

}