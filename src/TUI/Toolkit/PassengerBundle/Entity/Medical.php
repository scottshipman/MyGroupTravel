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
 * Medical
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Medical
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="doctorName", type="text", nullable=true)
     * @GRID\Column(title="Display Name", export=true)
     */
    private $doctorName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $doctorNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="conditions", type="text", nullable=true)
     * @GRID\Column(title="Conditions", export=true)
     */
    private $conditions;

    /**
     * @var string
     *
     * @ORM\Column(name="medications", type="text", nullable=true)
     * @GRID\Column(title="Medications", export=true)
     */
    private $medications;

    /**
     * @var \TUI\Toolkit\PassengerBundle\Entity\Passenger
     * @ORM\OneToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="passenger", referencedColumnName="id")
     */
    protected $passengerReference;


    /**
     * @param $doctorName
     * @return $this
     *
     */
    public function setDoctorName($doctorName){

        $this->doctorName = $doctorName;

        return $this;
    }

    /**
     * @return string
     *
     */

    public function getDoctorName(){

        return $this->doctorName;
    }


    /**
     * @param $doctorNumber
     * @return $this
     *
     */
    public function setDoctorNumber($doctorNumber){

        $this->doctorNumber = $doctorNumber;

        return $this;
    }

    /**
     * @return null
     *
     */

    public function getDoctorNumber(){

        return $this->doctorNumber;
    }

    /**
     * @param $conditions
     * @return $this
     *
     */
    public function setConditions($conditions){

        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return string
     *
     */

    public function getConditions(){

        return $this->conditions;
    }

    /**
     * @param $conditions
     * @return $this
     *
     */
    public function setMedications($medications){

        $this->medications = $medications;

        return $this;
    }

    /**
     * @return string
     *
     */

    public function getMedications(){

        return $this->medications;
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


