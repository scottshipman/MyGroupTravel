<?php

namespace TUI\Toolkit\TourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentTask
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PaymentTask
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dueDate", type="date")
     */
    private $dueDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paidDate", type="date", nullable=true)
     */
    private $paidDate;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255,nullable=true)
     */
    private $type;


    private $overrideValue;

    public function getOverrideValue()
    {
        return $this->overrideValue;
    }

    public function setOverrideValue($overrideValue)
    {
        $this->overrideValue = $overrideValue;
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
     * Set iD
     *
     * @param id
     * @return PaymentTask
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set string
     *
     * @param string $string
     * @return PaymentTask
     */
    public function setString($string)
    {
        $this->string = $string;

        return $this;
    }

    /**
     * Get string
     *
     * @return string 
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return PaymentTask
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param float $value
     * @return PaymentTask
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
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return PaymentTask
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set paidDate
     *
     * @param \DateTime $paidDate
     * @return PaymentTask
     */
    public function setPaidDate($paidDate)
    {
        $this->paidDate = $paidDate;

        return $this;
    }

    /**
     * Get paidDate
     *
     * @return \DateTime 
     */
    public function getPaidDate()
    {
        return $this->paidDate;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return PaymentTask
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }
}
