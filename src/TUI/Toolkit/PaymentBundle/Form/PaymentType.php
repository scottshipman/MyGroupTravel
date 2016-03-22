<?php

namespace TUI\Toolkit\PaymentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use TUI\Toolkit\TourBundle\Entity\Tour;
use TUI\Toolkit\CurrencyBundle\Entity\Currency;

class PaymentType extends AbstractType
{
    private $locale;
    private $tour;
    private $passenger;

    public function __construct($locale, $tour, $passenger)
    {
        $this->passenger = $passenger;
        $this->tour = $tour;
        $this->locale = $locale;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         switch (true) {
            case strstr($this->locale, 'en_GB'):
                $date_format = 'dd-MM-yyyy';
                break;
            default:
                $date_format = 'MM-dd-yyyy';
                break;
        }

        $builder
            ->add('value', 'money', array(
                'label' => 'payment.form.value',
                'translation_domain'  => 'messages',
                'required' => true,
                'currency' => $this->tour->getCurrency()->getCode(),
                'constraints' => array(
                  new Callback(array(
                    'callback' => array($this, 'validateValueTotal')
                  ))
                )
            ))
            ->add('date', 'genemu_jquerydate', array(
                'widget' => 'single_text',
                'required' => true,
                'label' => 'payment.form.date',
                'format' => $date_format,
            ))
            ->add('note', 'textarea', array(
                'label' => 'payment.form.note',
                'translation_domain'  => 'messages',
                'required' => false,
            ))
            ->add('tour', 'hidden', array(
                'data' => $this->tour->getId()
            ))
            ->add('passenger','hidden', array(
                'data' => $this->passenger->getId()
    ))
        ;
    }

    /**
     * The total of all payments for the passenger should always be positive.
     *
     * @param $object
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateValueTotal($object, ExecutionContextInterface $context)
    {
        $payments = $this->passenger->getPayments();
        $total = $object;

        // Work out the total value of all payments for the passenger.
        foreach($payments as $payment) {
            $total += $payment->getValue();
        }

        // If the total is less than or equal to
        if ($total < 0) {
            $context->buildViolation('You cannot refund more than has been paid.' . $total)
              ->atPath('value')
              ->addViolation();
        }
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\PaymentBundle\Entity\Payment',
            'cascade_validation' => true,
            'error_bubbling' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_paymentbundle_payment';
    }
}
