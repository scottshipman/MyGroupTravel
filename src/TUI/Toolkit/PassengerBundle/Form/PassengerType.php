<?php

namespace TUI\Toolkit\PassengerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PassengerType extends AbstractType
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
        switch ($this->locale) {
            case 'en_GB.utf8':
                $date_label = '(DD-MM-YYYY)';
                $date_format = 'dd-MM-yyyy';
                break;
            default:
                $date_label = '(MM-DD-YYYY)';
                $date_format = 'MM-dd-yyyy';
                break;
        }


        $builder
            ->add('over18', 'choice', array(
                'choices'  => array(
                    'Yes' => 'Yes',
                    'No' => 'No',
                ),
                'required' => false,
                'expanded' => true,
                'label' => 'Is the passenger over 18?',
                'mapped' => false,
                'empty_value' => false

            ))
            ->add('userReference', 'hidden', array(
                'required' => false
            ))
            ->add('firstName')
            ->add('lastName')
            ->add('dateOfBirth', 'datetime', array(
                'format' => $date_format,
            ))
            ->add('Gender', 'choice', array(
                'choices' => array(
                    'Male' => 'Male',
                    'Female' => 'Female'
                )
            ))
            ->add('guardian', 'hidden', array(
                'required' => false
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\PassengerBundle\Entity\Passenger'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_passengerbundle_passenger';
    }
}
