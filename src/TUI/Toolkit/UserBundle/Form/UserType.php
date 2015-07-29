<?php

namespace TUI\Toolkit\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use libphonenumber\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

      // todo: Add logic so you cant add any role greater than your own
        $builder
          ->add('honorific', 'choice', array(
              'choices' => array(
                'Mr.' => 'Mr.',
                'Mrs.' => 'Mrs.',
                'Ms.' => 'Ms.',
                'Miss' => 'Miss',
                'Dr.' => 'Dr.',
                )
              ))
          ->add('firstName', 'text', array(
            'label' => 'First Name',
              ))
          ->add('lastName', 'text', array(
            'label' => 'Last Name',
              ))
          ->add('email')
          ->add('phoneNumber', 'tel', array(
            'label' => 'Phone Number',
            'required' => false,
            'default_region' => 'US',
            'format' => PhoneNumberFormat::NATIONAL
              ))
            ->add('username')
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.new_password'),
                'second_options' => array('label' => 'form.new_password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'required' => false,
              ))
            ->add('media', 'hidden', array(
                'required' => false,
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_userbundle_user';
    }
}
