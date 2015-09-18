<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use TUI\Toolkit\UserBundle\Form\UserType;

class TourType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('secondaryContact', 'genemu_jqueryautocomplete_entity', array(
              'class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'required' => FALSE,
              'route_name' => 'retrieve_salesagent_name',
              'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'label' => 'Other Business Admin',
              //'multiple' => true,
            ))
            ->add('organizer', 'genemu_jqueryautocomplete_entity', array(
              'route_name' => 'retrieve_organizers_name',
              'class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'configs' => array('minLength' => 3),
              'attr' => array(
                'class' => 'suggest',
              ),
            ))
            ->add('institution', 'genemu_jqueryautocomplete_entity', array(
              'class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution',
              'route_name' => 'retrieve_institution_name',
              'data_class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution',
              'configs' => array('minLength' => 3),
              'attr' => array(
                'class' => 'suggest',
              ),

            ))

            ->add('name', 'text', array(
              'label' => 'Tour Name',
            ))
            ->add('destination', 'genemu_jqueryautocomplete_entity', array(
                'class' => 'TUI\Toolkit\QuoteBundle\Entity\Quote',
                'property' => 'destination',
                'label'   => 'Destination',
            ))

            ->add('salesAgent', 'genemu_jqueryautocomplete_entity', array(
                'required' => true,
                'class' => 'TUI\Toolkit\UserBundle\Entity\User',
                'route_name' => 'retrieve_salesagent_name',
                'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
                'label' => 'Primary Business Admin',
            ))

            ->add('media', 'hidden', array(
                'required' => false,
//                'data_class' => 'TUI\Toolkit\MediaBundle\Entity\Media',
                'attr' => array(
                    'class' => 'media-placeholder',
//                    'multiple' => true
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
            'data_class' => 'TUI\Toolkit\TourBundle\Entity\Tour',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_tourbundle_tour';
    }
}
