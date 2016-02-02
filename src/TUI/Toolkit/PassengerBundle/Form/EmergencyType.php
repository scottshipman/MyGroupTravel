<?php

namespace TUI\Toolkit\PassengerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use libphonenumber\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;

class EmergencyType extends AbstractType
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

        $builder
            ->add('emergencyName', 'text', array(
                'required' => false,
                'label' => 'passenger.labels.emergency_name',
            ))
            ->add('emergencyRelationship', 'text', array(
                'required' => false,
                'label' => 'passenger.labels.emergency_relationship',
            ))
            ->add('emergencyNumber', 'text', array(
                'required' => false,
                'label' => 'passenger.labels.emergency_number',
            ))
            ->add('emergencyEmail', 'email', array(
                'required' => false,
                'label' => 'passenger.labels.emergency_email'
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
            'data_class' => 'TUI\Toolkit\PassengerBundle\Entity\Emergency'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_passengerbundle_emergency';
    }
}
