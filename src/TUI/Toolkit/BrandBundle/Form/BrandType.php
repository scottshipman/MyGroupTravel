<?php

namespace TUI\Toolkit\BrandBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\MediaBundle\Model\Media;
use Application\Sonata\MediaBundle;

class BrandType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('division')
            ->add('primaryColor','text', array(
              'label' => 'brand.form.label.p_color',
                'translation_domain'  => 'messages',
            ))
            ->add('secondaryColor','text', array(
              'label' => 'brand.form.label.s_color',
              'translation_domain'  => 'messages',
            ))
            ->add('tertiaryColor','text', array(
              'label' => 'brand.form.label.t_color',
              'translation_domain'  => 'messages',
            ))
            ->add('footerBody', 'ckeditor', array())
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
            'data_class' => 'TUI\Toolkit\BrandBundle\Entity\Brand'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_brandbundle_brand';
    }
}
