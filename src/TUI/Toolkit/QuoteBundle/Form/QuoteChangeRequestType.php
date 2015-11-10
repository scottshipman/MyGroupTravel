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
                  'Our numbers have changed' => 'quote.form.changes.choices.numbers',
                  'Need advice on how to reduce price' => 'quote.form.changes.choices.reduce_price',
                  'Can we look at different dates?' => 'quote.form.changes.choices.dates',
                  'Where else do you recommend?' => 'quote.form.changes.choices.destination'
                ),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'mapped' => false,
                'translation_domain'  => 'messages',

            ))
            ->add('additional', 'textarea', array(
                'label' => 'quote.form.changes.additional',
                'translation_domain'  => 'messages',
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'maxlength' => 500,
                )
            ))
            ->add('submit', 'submit', array('label' => 'quote.form.changes.submit', 'translation_domain'  => 'messages',))
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