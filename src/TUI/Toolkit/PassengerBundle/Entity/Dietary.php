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
 * Dietary
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Dietary
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
     * @ORM\Column(name="description", type="text", nullable=true)
     * @GRID\Column(title="Description", export=true)
     * @Assert\NotBlank()
     */
    protected $description;

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
     * @param $description
     * @return $this
     *
     */
    public function setDescription($description) {

        $this->description = $description;

        return $this;

    }

    /**
     * @return Medical
     *
     */
    public function getDescription() {
        return $this->description;
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
