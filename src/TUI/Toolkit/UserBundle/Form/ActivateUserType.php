<?php

namespace TUI\Toolkit\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\IsTrue;


class ActivateUserType extends AbstractType
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
            'options' => array(
                'translation_domain' => 'FOSUserBundle',
            ),
            'first_options' => array('label' => 'form.new_password'),
            'second_options' => array('label' => 'form.new_password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
            'required' => true,
            'constraints' => new NotBlank(array(
                'groups' => 'Activation',
            )),
          ))
          ->add('question', 'text', array(
            'label' => 'user.form.question',
            'translation_domain'  => 'messages',
            'required' => true,
          ))
          ->add('answer', 'text', array(
            'label' => 'user.form.answer',
            'translation_domain'  => 'messages',
            'required' => true,
          ))
          ->add('termsAgree', 'checkbox', array(
            'mapped' => FALSE,
            'constraints' => new IsTrue(array(
                'message' => 'user.activation.error.terms',
                'groups' => 'Activation'
            )),
          ))
            // Add these for when an Organizer is registering
            ->add('role', 'hidden', array(
                'mapped' => FALSE,
            ))
            ->add('tour', 'hidden', array(
                'mapped' => FALSE,
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'validation_groups' => array('Activation')
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_user_register_confirm';
    }
}
