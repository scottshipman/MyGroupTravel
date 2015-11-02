<?php

namespace TUI\Toolkit\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use libphonenumber\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;

class SecurityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
          ->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'options' => array('translation_domain' => 'FOSUserBundle'),
            'first_options' => array('label' => 'form.new_password'),
            'second_options' => array('label' => 'form.new_password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
            'required' => false,
          ))
          ->add('newQuestion', 'text', array(
            'label' => 'user.form.question',
            'translation_domain'  => 'messages',
            'mapped' => false,
            'required' => false,
          ))
          ->add('newAnswer', 'text', array(
            'label' => 'user.form.answer',
            'translation_domain'  => 'messages',
            'mapped' => false,
            'required'=> false,
          ))
           ->add('originalAnswer', 'text', array(
            'label' => 'user.form.answer',
            'translation_domain'  => 'messages',
               'mapped' => false,
               'required'=> true,
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
        return 'tui_toolkit_user_security_edit';
    }
}
