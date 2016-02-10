<?php

namespace TUI\Toolkit\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Payment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Payment
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
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text")
     */
    private $note;

    /**
     * @var \TUI\Toolkit\TourBundle\Entity\Tour
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TourBundle\Entity\Tour", cascade={"persist"})
     * @ORM\JoinColumn(name="tour", referencedColumnName="id")
     */
    private $tour;

    /**
     * @var \TUI\Toolkit\PassengerBundle\Entity\Passenger
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Passenger", cascade={"persist"})
     * @ORM\JoinColumn(name="passenger", referencedColumnName="id")
     */
    private $passenger;


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
     * Set value
     *
     * @param float $value
     * @return Payment
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Payment
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return Payment
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set tour
     *
     * @param integer $tour
     * @return Payment
     */
    public function setTour($tour)
    {
        $this->tour = $tour;

        return $this;
    }

    /**
     * Get tour
     *
     * @return integer 
     */
    public function getTour()
    {
        return $this->tour;
    }

    /**
     * Set passenger
     *
     * @param integer $passenger
     * @return Payment
     */
    public function setPassenger($passenger)
    {
        $this->passenger = $passenger;

        return $this;
    }

    /**
     * Get passenger
     *
     * @return integer 
     */
    public function getPassenger()
    {
        return $this->passenger;
    }
}
