<?php

namespace TUI\Toolkit\InstitutionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sonata\MediaBundle\Model\Media;
use Application\Sonata\MediaBundle;

class InstitutionType extends AbstractType
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
          $city_label = 'Town';
          $regional_field = 'county';
          $zip_label = "Post code";
          break;
        default:
          $city_label = 'City';
          $regional_field = 'state';
          $zip_label = "Zipcode";
          break;
      }


        $builder
            ->add('name')
            ->add('address1','text', array(
              'required' => false,
            ))
            ->add('address2', 'text', array(
              'required' => false,
            ))
            ->add('city', 'text', array(
              'required' => true,
              'label' => $city_label
            ))
            ->add($regional_field, 'text', array(
              'required' => false,
            ))
            ->add('postCode', 'text', array(
              'required' => true,
              'label' => $zip_label
            ))
            ->add('country', 'text', array(
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

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_institutionbundle_institution';
    }
}
