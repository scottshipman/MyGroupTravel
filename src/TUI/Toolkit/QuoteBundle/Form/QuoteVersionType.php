<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class QuoteVersionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tripStatus','entity', array(
            'placeholder' => 'Select',
            'class' => 'TripStatusBundle:TripStatus',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('ts')
                  ->where('ts.visible = 1')
                  ->orderBy('ts.name', 'ASC');
                },
              ))
            ->add('boardBasis','entity', array(
            'placeholder' => 'Select',
            'class' => 'BoardBasisBundle:BoardBasis',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('b')
                  ->orderBy('b.name', 'ASC');
                },
              ))
            ->add('expiryDate')
            ->add('freePlaces')
            ->add('payingPlaces')
            ->add('maxPax')
            ->add('minPax')
            ->add('departureDate')
            ->add('signupDeadline')
            ->add('quoteDays')
            ->add('quoteNights')
            ->add('totalPrice')
            ->add('transportType','entity', array(
                'placeholder' => 'Select',
                'class' => 'TransportBundle:Transport',
                'property' => 'name',
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('t')
                  ->orderBy('t.name', 'ASC');
                  },
              ))
            ->add('welcomeMsg', 'text', array(
                'label' => 'Welcome Message',
              ))
            ->add('content', 'hidden', array(
                'empty_data' => 0, //'set this to whatever is the parent'
              ))
            ->add('parent', 'hidden')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\QuoteBundle\Entity\QuoteVersion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_quotebundle_quoteversion';
    }
}
