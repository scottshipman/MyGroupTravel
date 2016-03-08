<?php

namespace TUI\Toolkit\TourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentOverride
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PaymentTaskOverride
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
     * @var \TUI\Toolkit\PassengerBundle\Entity\Passenger
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\PassengerBundle\Entity\Passenger", cascade={"persist"})
     * @ORM\JoinColumn(name="passenger", referencedColumnName="id")
     */
    private $passenger;

    /**
     * @var \TUI\Toolkit\TourBundle\Entity\PaymentTask
     * @ORM\ManyToOne(targetEntity="TUI\Toolkit\TourBundle\Entity\PaymentTask", cascade={"persist"})
     * @ORM\JoinColumn(name="paymentTaskSource", referencedColumnName="id")
     */
    private $paymentTaskSource;


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
     * @return PaymentOverride
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
     * Set passenger
     *
     * @param integer $passenger
     * @return PaymentOverride
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

    /**
     * Set paymentTaskSource
     *
     * @param integer $paymentTaskSource
     * @return PaymentOverride
     */
    public function setPaymentTaskSource($paymentTaskSource)
    {
        $this->paymentTaskSource = $paymentTaskSource;

        return $this;
    }

    /**
     * Get paymentTaskSource
     *
     * @return integer 
     */
    public function getPaymentTaskSource()
    {
        return $this->paymentTaskSource;
    }
}
