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
//            ->add('media', 'sonata_media_type', array(
//                'provider' => 'sonata.media.provider.image',
//                'context' => 'brand'
//
//            ))
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
