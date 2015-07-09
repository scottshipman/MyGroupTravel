<?php

namespace TUI\Toolkit\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('email')
            ->add('username')
            ->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'options' => array('translation_domain' => 'FOSUserBundle'),
            'first_options' => array('label' => 'form.new_password'),
            'second_options' => array('label' => 'form.new_password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
            'required' => false,
          ))
            ->add('enabled')
            ->add('userParent')
            ->add('roles', 'choice', array(
            'choices'  => array('ROLE_USER' => 'User', 'ROLE_CUSTOMER' => 'CUSTOMER', 'ROLE_BRAND'=> 'BRAND', 'ROLE_ADMIN'=>'ADMIN',),
            'multiple' => true,
            'expanded' => TRUE,
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
