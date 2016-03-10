<?php

namespace TUI\Toolkit\PassengerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\Email;
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


        $builder
            ->add('firstName', 'text', array(
                'label' => 'passenger.signupform.fname',
                'translation_domain'  => 'messages',
                'required' => true,
                'mapped' => false,

            ))
            ->add('lastName', 'text', array(
                'label' => 'passenger.signupform.lname',
                'translation_domain'  => 'messages',
                'required' => true,
                'mapped' => false,

            ))
            ->add('email', 'email', array(
                'label' => 'passenger.signupform.email',
                'translation_domain'  => 'messages',
                'required'  => true,
                'mapped' => false,
                'constraints' => new Email(array(
                    'message' => 'enter a valid Parent / Guardian email address'
                )),


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

//        $collectionConstraint = new Collection(array(
//            'email' => new Email(array('message' => 'Invalid email address')),
//        ));
        $resolver->setDefaults(array(
            'cascade_validation' => true,
         //   'validation_constraint' => $collectionConstraint,
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
