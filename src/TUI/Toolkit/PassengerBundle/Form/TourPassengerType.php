<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use TUI\Toolkit\PassengerBundle\Form\PassengerType;
use TUI\Toolkit\UserBundle\Form\UserType;
use TUI\Toolkit\UserBundle\Form\UserPassengerType;

class TourPassengerType extends AbstractType
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
        switch ($this->locale) {
            case 'en_GB.utf8':
                $date_label = '(DD-MM-YYYY)';
                $date_format = 'dd-MM-yyyy';
                break;
            default:
                $date_label = '(MM-DD-YYYY)';
                $date_format = 'MM-dd-yyyy';
                break;
        }


        $builder
            ->add('firstName', 'text', array(
                'label' => 'user.form.fname',
                'translation_domain'  => 'messages',
                'required' => false,

            ))
            ->add('lastName', 'text', array(
                'label' => 'user.form.lname',
                'translation_domain'  => 'messages',
                'required' => false,
            ))
            ->add('email', 'email', array(
                'label' => 'user.form.email',
                'translation_domain'  => 'messages',
                'required'  => false,

            ))
            ->add('passengers', 'collection', array(
                'type' => new PassengerType($this->locale),
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
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
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_tourbundle_tourpassenger';
    }
}
