<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use TUI\Toolkit\UserBundle\Form\UserType;

class QuoteChangeRequestType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('changes', 'choice', array(
                'choices' => array(
                    'Our numbers have changed' => 'Our numbers have changed',
                    'Need advice on how to reduce price' => 'Need advice on how to reduce price',
                    'Can we look at different dates?' => 'Can we look at different dates?',
                    'Where else do you recommend?' => 'Where else do you recommend?'
                ),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'mapped' => false,

            ))
            ->add('additional', 'textarea', array(
                'label' => 'Anything else?',
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'maxlength' => 500,
                    'placeholder' => "I'd like to add"
                )
            ))
            ->add('submit', 'submit', array('label' => 'Submit'))
            ->getForm();
        ;


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
//            'data_class' => 'TUI\Toolkit\QuoteBundle\Entity\QuoteVersion',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_quotebundle_quotechangerequest';
    }
}