<?php

namespace TUI\Toolkit\InstitutionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sonata\MediaBundle\Model\Media;
use Application\Sonata\MediaBundle;

class InstitutionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('address1','text', array(
              'required' => false,
            ))
            ->add('address2', 'text', array(
              'required' => false,
            ))
            ->add('city', 'text', array(
              'required' => false,
            ))
            ->add('county', 'text', array(
              'required' => false,
            ))
            ->add('state', 'text', array(
              'required' => false,
            ))
            ->add('postCode', 'text', array(
              'required' => false,
            ))
            ->add('localAuthority', 'text', array(
              'required' => false,
            ))
            ->add('country', 'text', array(
              'required' => false,
            ))
            ->add('code', 'text', array(
              'required' => false,
            ))
            ->add('type', 'text', array(
              'required' => false,
            ))
            ->add('websiteAddress', 'text', array(
              'required' => false,
            ))
/*            ->add('media', 'sonata_media_type', array(
                'provider' => 'sonata.media.provider.image',
                'context' => 'institution'

            ))*/
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
