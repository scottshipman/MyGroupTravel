<?php

namespace TUI\Toolkit\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use libphonenumber\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;

class AjaxuserType extends AbstractType
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
      switch ($this->locale){
        case 'en_GB.utf8':
          $phoneFormat = PhoneNumberFormat::NATIONAL;
          $defaultRegion = 'GB';
          break;
        default:
          $date_label = PhoneNumberFormat::NATIONAL;
          $date_format = 'US';
          break;
      }
      // todo: Add logic so you cant add any role greater than your own
        $builder
          ->add('honorific', 'choice', array(
            'required' => false,
            'placeholder' => 'Select',
            'label' => 'Title',
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
            'required' => true,

          ))
          ->add('lastName', 'text', array(
            'label' => 'Last Name',
            'required' => true,
          ))
          ->add('email', 'email', array(
            'label' => 'Email Address',
            'required'  => true,

          ))
          ->add('phoneNumber', 'tel', array(
            'label' => 'Phone Number',
            'required' => false,
            'default_region' => $defaultRegion,
            'format' => $phoneFormat
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
        return 'tui_toolkit_ajax_userbundle_user';
    }
}
