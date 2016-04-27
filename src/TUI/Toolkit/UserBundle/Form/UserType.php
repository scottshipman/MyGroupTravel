<?php

namespace TUI\Toolkit\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use libphonenumber\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;
use TUI\Toolkit\UserBundle\Controller\UserController;

class UserType extends AbstractType
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
//
//      switch (true){
//        case strstr($this->locale, 'en_GB'):
//          $phoneFormat = PhoneNumberFormat::NATIONAL;
//          $defaultRegion = 'GB';
//          break;
//        default:
//          $phoneFormat = PhoneNumberFormat::NATIONAL;
//          $defaultRegion = 'US';
//          break;
     // }

      // todo: Add logic so you cant add any role greater than your own
        $builder
          ->add('honorific', 'choice', array(
            'required' => false,
            'placeholder' => 'Select',
            'label' => 'user.form.honorific',
            'translation_domain'  => 'messages',
              'choices' => array(
                'Mr.' => 'Mr.',
                'Mrs.' => 'Mrs.',
                'Ms.' => 'Ms.',
                'Miss' => 'Miss',
                'Dr.' => 'Dr.',
                )
              ))
          ->add('firstName', 'text', array(
            'label' => 'user.form.fname',
            'translation_domain'  => 'messages',
            'required' => true,

              ))
          ->add('lastName', 'text', array(
            'label' => 'user.form.lname',
            'translation_domain'  => 'messages',
            'required' => true,
              ))
          ->add('displayName', 'text', array(
            'label' => 'user.form.display_name',
            'translation_domain'  => 'messages',
            'required' => false,
          ))
          ->add('email', 'email', array(
            'label' => 'user.form.email',
            'translation_domain'  => 'messages',
            'required'  => true,

          ))
            ->add('phoneNumber', 'text', array(
                'label' => 'user.form.phone',
                'translation_domain'  => 'messages',
                'required' => false,
            ))
            ->add('media', 'hidden', array(
                'required' => false,
                'data_class' => 'TUI\Toolkit\MediaBundle\Entity\Media',
                'attr' => array(
                  'class' => 'media-placeholder',
                )
            ))
        ;
        $user = $options['user'];
        if (!empty($user)) {
            $roles = $user->getRoles();

            if (in_array('ROLE_SUPER_ADMIN', $roles)) {
                $builder->add('roles', 'choice', array(
                    'choices' => array(
                        'ROLE_CUSTOMER' => 'CUSTOMER',
                        'ROLE_BRAND' => 'BRAND',
                        'ROLE_ADMIN' => 'ADMIN',
                        'ROLE_SUPER_ADMIN' => 'SUPER_ADMIN',
                    ),
                    'multiple' => TRUE,
                    'expanded' => TRUE,
                ));
            }
            elseif (in_array('ROLE_ADMIN', $roles)) {
                $builder->add('roles', 'choice', array(
                    'choices' => array(
                        'ROLE_CUSTOMER' => 'CUSTOMER',
                        'ROLE_BRAND' => 'BRAND',
                        'ROLE_ADMIN' => 'ADMIN',
                    ),
                    'multiple' => TRUE,
                    'expanded' => TRUE,
                ));
            }
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'user' => null
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
