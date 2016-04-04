<?php

namespace TUI\Toolkit\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Annotations;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Emergency
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Emergency
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
     * @ORM\Column(name="emergencyName", type="text", nullable=true)
     * @GRID\Column(title="Emergency Contact Name", export=true)
     * @Assert\NotBlank()
     */
    private $emergencyName;

    /**
     * @var string
     *
     * @ORM\Column(name="emergencyRelationship", type="text", nullable=true)
     * @GRID\Column(title="Emergency Relationship", export=true)
     * @Assert\NotBlank()
     */
    private $emergencyRelationship;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $emergencyNumber;


    /**
     * @var string
     *
     * @ORM\Column(name="emergencyEmail", type="text", nullable=true)
     * @GRID\Column(title="Emergency Email", export=true)
     * @Assert\NotBlank()
     * @Assert\Email(
     *   message = "The email '{{ value }}' is not a valid email."
     * )
     *
     */
    private $emergencyEmail;


    /**
     * @var \TUI\Toolkit\PassengerBundle\Entity\Passenger
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="passenger", referencedColumnName="id")
     */
    protected $passengerReference;


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
     * @param $emergencyName
     * @return $this
     *
     */
    public function setEmergencyName($emergencyName){

        $this->emergencyName = $emergencyName;

        return $this;
    }

    /**
     * @return emergencyName
     *
     */

    public function getEmergencyName(){

        return $this->emergencyName;
    }

    /**
     * @param $emergencyRelationship
     * @return $this
     *
     */
    public function setEmergencyRelationship($emergencyRelationship){

        $this->emergencyRelationship = $emergencyRelationship;

        return $this;
    }

    /**
     * @return emergencyRelationship
     *
     */

    public function getEmergencyRelationship(){

        return $this->emergencyRelationship;
    }

    /**
     * @param $emergencyNumber
     * @return $this
     *
     */
    public function setEmergencyNumber($emergencyNumber){

        $this->emergencyNumber = $emergencyNumber;

        return $this;
    }

    /**
     * @return emergencyNumber
     *
     */

    public function getEmergencyNumber(){

        return $this->emergencyNumber;
    }

    /**
     * @param $emergencyEmail
     * @return $this
     *
     */
    public function setEmergencyEmail($emergencyEmail){

        $this->emergencyEmail = $emergencyEmail;

        return $this;
    }

    /**
     * @return emergencyEmail
     *
     */

    public function getEmergencyEmail(){

        return $this->emergencyEmail;
    }
    /**
     * @param $passengerReference
     * @return $this
     *
     */
    public function setPassengerReference($passengerReference) {

        $this->passengerReference = $passengerReference;

        return $this;

    }

    /**
     * @return Medical
     *
     */
    public function getPassengerReference() {
        return $this->passengerReference;
    }
}
