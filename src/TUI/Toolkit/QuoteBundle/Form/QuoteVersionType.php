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
            ->add('revision', 'hidden', array(
              'mapped' => false,
            ))
            ->add('version', 'integer', array('read_only' => true, 'label' => 'Version Number'))
            // add the QuoteType form first
            ->add('quoteReference', new QuoteType(), array(
              'label' => 'Quote details'
              ))
            // now the versionable fields
            ->add('welcomeMsg', 'textarea', array(
              'label' => 'Welcome Message',
              ))
            ->add('tripStatus','entity', array(
            'label' => 'Trip Status',
            'required' => false,
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
            'label' => 'Board Basis',
            'required' => false,
            'placeholder' => 'Select',
            'class' => 'BoardBasisBundle:BoardBasis',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('b')
                  ->orderBy('b.name', 'ASC');
                },
              ))
            ->add('transportType','entity', array(
              'required' => false,
              'placeholder' => 'Select',
              'class' => 'TransportBundle:Transport',
              'property' => 'name',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('t')
                    ->orderBy('t.name', 'ASC');
                    },
                ))
            ->add('expiryDate', 'genemu_jquerydate', array(
                  'widget' => 'single_text',
                  'required' => false,
                  'label' => 'When does this quote expire?'
                    ))
            ->add('signupDeadline', 'genemu_jquerydate', array(
                   'widget' => 'single_text',
                   'required' => false,
                   'label' => 'When is the Signup Deadline?'
                    ))
            ->add('freePlaces')
            ->add('payingPlaces')
            ->add('maxPax')
            ->add('minPax')
            ->add('departureDate', 'genemu_jquerydate', array(
                  'widget' => 'single_text',
                  'required' => false
                  ))
            ->add('returnDate', 'genemu_jquerydate', array(
                  'widget' => 'single_text',
                  'required' => false
                  ))

            ->add('quoteDays')
            ->add('quoteNights')
            ->add('totalPrice')
            ->add('pricePerson')
            ->add('currency', 'entity', array(
                'required' => false,
                'placeholder' => 'Select',
                'class' => 'CurrencyBundle:Currency',
                'property'  =>  'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
                    },
              ))
            ->add('content', 'hidden', array(
                'empty_data' => 0, //'set this to whatever is the parent'
              ))
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
