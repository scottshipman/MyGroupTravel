<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class QuoteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('isTemplate', 'checkbox', array('required' => FALSE, 'label' => "Is this a template?"))
            ->add('name')
            ->add('destination')
            ->add('reference')
            ->add('organizer', 'entity', array(
              'required' => false,
              'placeholder' => 'Select',
              'class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                    ->where('u.roles LIKE :role')
                    ->setParameters(array('role' => "%ROLE_CUSTOMER%"))
                    ->orderBy('u.email', 'ASC');
                },
              ))
            ->add('institution', 'entity', array(
              'required' => false,
              'placeholder' => 'Select',
              'class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution',
              'property' => 'name',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('i')
                    ->orderBy('i.name', 'ASC');
                },
              ))
            ->add('salesAgent','entity', array(
              'required' => false,
              'placeholder' => 'Select',
              'class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                    ->where('u.roles LIKE :role')
                    ->setParameters(array('role' => "%ROLE_BRAND%"))
                    ->orderBy('u.email', 'ASC');
                    },
                ))
            ->add('converted', 'hidden', array('required' => FALSE,))
            ->add('setupComplete', 'hidden', array('required' => FALSE,))
            ->add('locked', 'hidden', array('required' => FALSE,))
            ->add('media', 'sonata_media_type', array(
                'required' => false,
                'provider' => 'sonata.media.provider.image',
                'context' => 'quote'

            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\QuoteBundle\Entity\Quote'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_quotebundle_quote';
    }
}
