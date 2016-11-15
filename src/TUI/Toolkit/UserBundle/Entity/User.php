<?php
/**
 * Created by  scottshipman
 * Date: 6/22/15
 */

namespace TUI\Toolkit\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use TUI\Toolkit\UserBundle\Controller\UserController;


/**
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     message="A user already exists for this email."
 * )
 * @ORM\Table(name="fos_user",uniqueConstraints={@ORM\UniqueConstraint(name="email", columns={"email"})})
 * @Gedmo\SoftDeleteable(fieldName="deleted", timeAware=false)
 * @GRID\Source(columns="id, lastName, firstName, fullname, displayName, email, enabled, roles, rolesString, created, lastLogin, locked", filterable=false, sortable=true, exportable=true,)
 * @GRID\Column(id="fullname", type="join", title="Name", columns={"firstName", "lastName"}, filterable=false, operatorsVisible=false)
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @GRID\Column(title="ID", filterable=false, operatorsVisible=false, export=true)
     */
    protected $id;


  /**
   * @var date
   *
   * @ORM\Column(name="created", type="date", nullable=false)
   * @GRID\Column(visible=true, export=true, operatorsVisible=false)
   */
  private $created;


  /**
   * @var date
   *
   * @ORM\Column(name="deleted", type="date", nullable=true)
   * @GRID\Column(visible=false, filterable=false, export=true)
   *
   */
  private $deleted;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     * @Assert\NotBlank()
     */
    protected $firstName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     * @Assert\NotBlank()
     */
    protected $lastName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @GRID\Column(visible=false, filterable=false, export=true)
     */
    protected $displayName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $honorific = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phoneNumber = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"Registration", "Profile", "Activation"})
     * @Assert\Length(
     *     min=10,
     *     max=255,
     *     minMessage="The question is too short.",
     *     maxMessage="The question is too long.",
     *     groups={"Registration", "Profile", "Activation"}
     * )
     */
    protected $question = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"Registration", "Profile", "Activation"})
     * @Assert\Length(
     *     min=5,
     *     max=255,
     *     minMessage="The answer is too short.",
     *     maxMessage="The answer is too long.",
     *     groups={"Registration", "Profile", "Activation"}
     * )
     */
    protected $answer = null;

  /**
   * @ORM\Column(type="string", nullable=true)
   * @GRID\Column(title="Role", filterable=true, operatorsVisible=false, export=true, sortable=false)
   */
  protected $rolesString = null;

    public function __construct()
    {
        parent::__construct();
        // your own logic

        $this->enabled = false;
        $this->created = new \DateTime();

        $this->roles = array('ROLE_CUSTOMER');
        $this->rolesString = implode(', ', $this->roles);


        /*      existing roles are:
                    super admin
                    admin
                    brand
                    customer -> with the following json psuedo roles / responsibilities
                      organizers
                      assistants
                      parents
                      passengers
                    user

        */
    }


  /**
   * @param string $rolesString
   */
  public function setRolesString($rolesString)
  {
    $this->rolesString = $rolesString;
  }

  /**
   * @return string
   */
  public function getRolesString()
  {
    return $this->rolesString;
  }

  public function __toString()
  {
    if (!empty($this->email)){
      $email = ' <'. $this->email . '>';
    } else {
      $email = '';
    }
    return $this->firstName . ' ' . $this->lastName .  $email;
  }


    /**
     * @var \TUI\Toolkit\MediaBundle\Entity\Media
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\MediaBundle\Entity\Media", cascade={"persist"}, fetch="LAZY")
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
     * @param mixed $honorific
     */
    public function setHonorific($honorific)
    {
        $this->honorific = $honorific;
    }

    /**
     * @return mixed
     */
    public function getHonorific()
    {
        return $this->honorific;
    }


    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }


  /**
   * Set deleted
   *
   * @param date $deleted
   * @return User
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



  /**
   * Set created
   *
   * @param date $created
   * @return User
   */
  public function setCreated($created)
  {
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
   * Set question
   *
   * @param date $created
   * @return User
   */
  public function setQuestion($question)
  {
    $this->question = $question;

    return $this;
  }

  /**
   * Get question
   *
   * @return string
   */
  public function getQuestion()
  {
    return $this->question;
  }

  /**
   * Set answer
   *
   * @param string $answer
   * @return User
   */
  public function setAnswer($answer)
  {
    $this->answer = $answer;

    return $this;
  }

  /**
   * Get answer
   *
   * @return string
   */
  public function getAnswer()
  {
    return $this->answer;
  }
}