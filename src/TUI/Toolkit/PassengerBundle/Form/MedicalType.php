<?php

namespace TUI\Toolkit\PassengerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use libphonenumber\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;

class MedicalType extends AbstractType
{

    private $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        switch (true){
            case strstr($this->locale, 'en_GB'):
                $phoneFormat = PhoneNumberFormat::NATIONAL;
                $defaultRegion = 'GB';
                break;
            default:
                $phoneFormat = PhoneNumberFormat::NATIONAL;
                $defaultRegion = 'US';
                break;
        }
        $builder
            ->add('doctorName', 'text', array(
                'required' => false,
                'label' => 'passenger.labels.doctor_name',
            ))
            ->add('DoctorNumber', 'tel', array(
                'required' => false,
                'label' => 'passenger.labels.doctor_number',
                'default_region' => $defaultRegion,
                'format' => $phoneFormat,
            ))
            ->add('conditions', 'text', array(
                'required' => false,
                'label' => 'passenger.labels.conditions',
            ))
            ->add('medications', 'text', array(
                'required' => false,
                'label' => 'passenger.labels.medication',
            ))
            ->add('passengerReference', 'hidden', array(
                'required' => false,
                'data_class' => 'TUI\Toolkit\PassengerBundle\Entity\Passenger',
                'mapped' => false,

            ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\PassengerBundle\Entity\Medical'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_passengerbundle_medical';
    }
}
