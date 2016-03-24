<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

class TourSetupType extends AbstractType
{
    private $locale;
    private $currency_code;

    public function __construct($locale, $tour)
    {
        $this->locale = $locale;
        $this->tour = $tour;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch (true) {
            case strstr($this->locale, 'en_GB'):
                $date_label = '(DD-MM-YYYY)';
                $date_format = 'dd-MM-yyyy';
                break;
            default:
                $date_label = '(MM-DD-YYYY)';
                $date_format = 'MM-dd-yyyy';
                break;
        }


        $builder
            ->add('pricePersonPublic', 'number', array(
                'label' => 'tour.form.tour_setup.price',
                'constraints' => array(new NotBlank(array('message' => 'Price per Person can not be blank'))),
            ))
            ->add('paymentTasksPassenger', 'collection', array(
                'type' => new PaymentTaskType($this->locale, $this->tour),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('cashPayment', 'checkbox', array(
                'required' => false,
            ))
            ->add('cashPaymentDescription', 'textarea', array(
                'required' => false,
                'label' => 'tour.form.tour_setup.instructions',
            ))
            ->add('bankTransferPayment', 'checkbox', array(
                'required' => false,
            ))
            ->add('bankTransferPaymentDescription', 'textarea', array(
                'required' => false,
                'label' => 'tour.form.tour_setup.instructions',
            ))
            ->add('onlinePayment', 'checkbox', array(
                'required' => false,
            ))
            ->add('onlinePaymentDescription', 'textarea', array(
                'required' => false,
                'label' => 'tour.form.tour_setup.instructions',
            ))
            ->add('otherPayment', 'checkbox', array(
                'required' => false,
            ))
            ->add('otherPaymentDescription', 'textarea', array(
                'required' => false,
                'label' => 'tour.form.tour_setup.instructions'
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\TourBundle\Entity\Tour',
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_tourbundle_toursetup';
    }
}
