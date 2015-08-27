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
            ->add('primaryColor')
            ->add('buttonColor')
            ->add('hoverColor')
            ->add('footerBody', 'ckeditor', array(
                'transformers' => array('html_purifier'),
                'toolbar' => array('document', 'editing', 'tools', 'basicstyles'),
                'toolbar_groups' => array(
                    'document' => array('Source')
                ),
                'ui_color' => '#ffffff',
                'startup_outline_blocks' => false,
                'width' => '100%',
                'height' => '320',
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
