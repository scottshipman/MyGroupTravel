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
          $regional_label = 'institution.form.label.county';
          $regional_field = 'county';
          $zip_label = "Post code";
          break;
        default:
          $regional_label = 'institution.form.label.state';
          $regional_field = 'state';
          $zip_label = "Zipcode";
          break;
      }


        $builder
            ->add('name', 'text', array(
              'label' => 'institution.form.label.name',
              'translation_domain'  => 'messages',
            ))
            ->add('address1','text', array(
              'required' => false,
              'label' => 'institution.form.label.address1',
              'translation_domain'  => 'messages',
            ))
            ->add('address2', 'text', array(
              'required' => false,
              'label' => 'institution.form.label.address2',
              'translation_domain'  => 'messages',
            ))
            ->add('city', 'text', array(
              'required' => true,
              'label' => 'institution.form.label.city',
              'translation_domain'  => 'messages',
            ))
            ->add($regional_field, 'text', array(
              'required' => false,
              'label' => $regional_label,
            ))
            ->add('postCode', 'text', array(
              'required' => true,
              'label' => 'institution.form.label.post_code',
              'translation_domain'  => 'messages',
            ))
            ->add('country', 'text', array(
              'required' => false,
              'label' => 'institution.form.label.country',
              'translation_domain'  => 'messages',
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
