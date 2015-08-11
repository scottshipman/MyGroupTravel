<?php

namespace TUI\Toolkit\ContentBlocksBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentBlockType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locked')
            ->add('hidden')
            ->add('layoutType', 'entity', array(
                'class' => 'ContentBlocksBundle:LayoutType',
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => false
            ))
            ->add('title')
            ->add('body', 'ckeditor', array(
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
//                'data_class' => 'TUI\Toolkit\MediaBundle\Entity\Media',
                'attr' => array(
                    'class' => 'media-placeholder',
//                    'multiple' => true
                )
            ))
            ->add('sortOrder')
            ->add('doubleWidth')
            ->getForm()
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\ContentBlocksBundle\Entity\ContentBlock'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_contentblocksbundle_contentblock';
    }
}
