<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class QuoteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
            ->add('name')
            ->add('reference')
            ->add('organizer', 'email')
            //->add('salesAgent', 'choice')
            ->add('converted', 'checkbox', array('required'=> FALSE,))
            ->add('deleted', 'checkbox', array('required'=> FALSE,))
            ->add('setupComplete', 'checkbox', array('required'=> FALSE,))
            ->add('locked', 'checkbox', array('required'=> FALSE,))
            ->add('isTemplate', 'checkbox', array('required'=> FALSE,))
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
