<?php

namespace TUI\Toolkit\PassengerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PassportType extends AbstractType
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
            ->add('passportNumber', 'text', array(
                'required' => false,
                'label' => 'Passport Number:',
            ))
            ->add('passportFirstName', 'text', array(
                'required' => false,
                'label' => 'First Name:',
            ))
            ->add('passportLastName', 'text', array(
                'required' => false,
                'label' => 'Last Name:',
            ))
            ->add('passportNationality', 'country', array(
                'required' => false,
                'label' => 'Nationality:',
            ))
            ->add('passportDateOfIssue', 'birthday', array(
                'format' => $date_format,
                'required' => true,
                'attr' => array(
                    'class' => 'dateOfIssue',
                ),
                'years' => range(date('Y') - 15, date('Y') - 1)
            ))
            ->add('passportDateOfExpiry', 'birthday', array(
                'format' => $date_format,
                'required' => true,
                'attr' => array(
                    'class' => 'dateOfExpiry',
                ),
                'years' => range(date('Y') - 15, date('Y') - 1)
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
            'data_class' => 'TUI\Toolkit\PassengerBundle\Entity\Passport'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_passengerbundle_passport';
    }
}
