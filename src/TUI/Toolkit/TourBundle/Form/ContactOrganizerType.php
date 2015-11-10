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

    public function __construct($locale)
    {
        $this->locale = $locale;
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
        $builder

            ->add('message', 'textarea', array(
                'label' => 'tour.form.contact_organizer.message',
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'maxlength' => 800,
                )
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
//            'data_class' => 'TUI\Toolkit\TourBundle\Entity\Tour',
//            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_tourbundle_contactorganizer';
    }
}
