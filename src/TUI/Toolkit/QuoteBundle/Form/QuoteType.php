<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QuoteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status')
            ->add('displayTabs')
            ->add('expiryDate')
            ->add('freePlaces')
            ->add('locked')
            ->add('maxPax')
            ->add('minPax')
            ->add('name')
            ->add('payingPlaces')
            ->add('departureDate')
            ->add('reference')
            ->add('setupComplete')
            ->add('signupDeadline')
            ->add('quoteDays')
            ->add('quoteNights')
            ->add('totalPrice')
            ->add('tripStatus')
            ->add('boardBasis')
            ->add('transportType')
            ->add('deleted')
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
