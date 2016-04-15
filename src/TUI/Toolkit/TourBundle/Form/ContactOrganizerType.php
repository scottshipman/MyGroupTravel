<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContactOrganizerType extends AbstractType
{

    private $locale;

    public function __construct($locale, $active_organizer)
    {
        $this->locale = $locale;
        $this->active_organizer = $active_organizer;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        switch (true){
            case strstr($this->locale, 'en_GB'):
                $date_label = '(DD-MM-YYYY)';
                $date_format = 'dd-MM-yyyy';
                break;
            default:
                $date_label = '(MM-DD-YYYY)';
                $date_format = 'MM-dd-yyyy';
                break;
        }

        $label = 'tour.form.contact_organizer.label';

        // Send different message if organizer is active.
        if ($this->active_organizer) {
            $message = 'We have created a set of tools to help you manage your tour online and you need to log in to start using them.';
        }
        else {
            $message = 'We have created a set of tools to help you manage your tour online and you need to activate your account to start using them.';
        }

        $builder
            ->add('message', 'textarea', array(
                'label' => $label,
                'mapped' => false,
                'required' => false,
                'data' => $message,
                'attr' => array(
                    'maxlength' => 800,
                ),
            ))
        ;

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_tourbundle_contactorganizer';
    }
}
