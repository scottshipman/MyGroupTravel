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
                $date_format = 'dd MM yyyy';
                break;
            default:
                $date_format = 'MM dd yyyy';
                break;
        }


        $builder
            ->add('passportLastName', 'text', array(
                'required' => true,
                'label' => 'passenger.labels.passport_family_name',
            ))
            ->add('passportFirstName', 'text', array(
                'required' => true,
                'label' => 'passenger.labels.passport_first_name',
            ))
            ->add('passportMiddleName', 'text', array(
                'required' => false,
                'label' => 'passenger.labels.passport_middle_name',
            ))
            ->add('passportTitle', 'choice', array(
                'choices' => array(
                  "Mr" => "Mr.",
                  "Mrs" => "Mrs.",
                  "Ms" => "Ms.",
                  "Miss" => "Miss",
                  "Master" => "Master"
                ),
                'required' => true,
                'label' => 'passenger.labels.passport_title',
            ))
            ->add('passportGender', 'choice', array(
                'choices' => array(
                    'Male' => 'Male',
                    'Female' => 'Female'
                ),
                'label' => 'passenger.form.invite.gender',
                'required' => true,
            ))
            ->add('passportIssuingState', 'text', array(
                'required' => true,
                'attr' => array(
                    'maxlength' => '3',
                ),
                'label' => 'passenger.labels.passport_state_issue',
            ))
            ->add('passportNumber', 'text', array(
                'required' => true,
                'label' => 'passenger.labels.passport_number',
            ))
            ->add('passportNationality', 'country', array(
                'required' => true,
                'label' => 'passenger.labels.passport_nationality',
            ))
            ->add('passportDateOfBirth', 'birthday', array(
                'format' => $date_format,
                'required' => true,
                'attr' => array(
                    'class' => 'dateOfBirth',
                ),
                'invalid_message' => 'Please enter a valid date',
                'years' => range(date('Y') - 1, date('Y') - 100),
            ))
            ->add('passportDateOfIssue', 'birthday', array(
                'format' => $date_format,
                'required' => true,
                'attr' => array(
                    'class' => 'dateOfIssue',
                ),
                'invalid_message' => 'Please enter a valid date',
                'years' => range(date('Y') - 10, date('Y')),
            ))
            ->add('passportDateOfExpiry', 'birthday', array(
                'format' => $date_format,
                'required' => true,
                'attr' => array(
                    'class' => 'dateOfExpiry',
                ),
                'invalid_message' => 'Please enter a valid date',
                'years' => range(date('Y'), date('Y') + 11),
            ))
            ->add('passengerReference', 'hidden', array(
                'required' => true,
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
