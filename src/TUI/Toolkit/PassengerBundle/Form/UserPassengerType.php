<?php

namespace TUI\Toolkit\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use libphonenumber\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;
use TUI\Toolkit\UserBundle\Controller\UserController;

class UserPassengerType extends AbstractType
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

        // todo: Add logic so you cant add any role greater than your own
        $builder
            ->add('firstName', 'text', array(
                'label' => 'user.form.fname',
                'translation_domain'  => 'messages',
                'required' => false,

            ))
            ->add('lastName', 'text', array(
                'label' => 'user.form.lname',
                'translation_domain'  => 'messages',
                'required' => false,
            ))
            ->add('email', 'email', array(
                'label' => 'user.form.email',
                'translation_domain'  => 'messages',
                'required'  => false,

            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
//            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_userbundle_userpassenger';
    }
}
