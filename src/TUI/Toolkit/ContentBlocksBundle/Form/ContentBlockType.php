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
            ->add('layoutType')
            ->add('title')
            ->add('body', 'ckeditor', array(
                'transformers' => array('html_purifier'),
                'toolbar' => array('document','basicstyles'),
                'toolbar_groups' => array(
                    'document' => array('Source')
                ),
                'width' => '100%',
                'height' => '320',
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
