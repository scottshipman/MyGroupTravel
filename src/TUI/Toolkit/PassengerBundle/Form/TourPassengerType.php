<?php

namespace TUI\Toolkit\PassengerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;
use TUI\Toolkit\UserBundle\Form\UserType;
use TUI\Toolkit\UserBundle\Form\UserPassengerType;
use TUI\Toolkit\PassengerBundle\Controller;

class TourPassengerType extends AbstractType
{
    private $locale;
    private $tourId;

    public function __construct($locale, $tourId)
    {
        $this->locale = $locale;
        $this->tourId = $tourId;
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

        $blank_message = 'This value should not be blank.';;

        $builder
            ->add('firstName', 'text', array(
                'label' => 'passenger.signupform.fname',
                'translation_domain'  => 'messages',
                'required' => true,
                'mapped' => false,
                'constraints' => new NotBlank(array(
                    'message' => $blank_message
                )),

            ))
            ->add('lastName', 'text', array(
                'label' => 'passenger.signupform.lname',
                'translation_domain'  => 'messages',
                'required' => true,
                'mapped' => false,
                'constraints' => new NotBlank(array(
                    'message' => $blank_message
                )),

            ))
            ->add('email', 'email', array(
                'label' => 'passenger.signupform.email',
                'translation_domain'  => 'messages',
                'required'  => true,
                'mapped' => false,
                'constraints' => array(
                    new Email(array(
                    'message' => 'Please enter a valid email address.'
                    )),
                    new NotBlank(array(
                        'message' => $blank_message
                    )),
                ),

            ))
            ->add('passengers', 'collection', array(
                'type' => new PassengerType($this->locale, $this->tourId),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'mapped' => false,
                'required' => true,
            ))
                    // Honeypot field
            ->add('password', 'honeypot')
            ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_passengerbundle_tourpassenger';
    }
}
