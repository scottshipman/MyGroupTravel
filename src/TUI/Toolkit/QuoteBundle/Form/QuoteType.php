<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use TUI\Toolkit\UserBundle\Form\UserType;

class QuoteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      // CASE Editing a quote or creating a new quote - show non-Template fields
      $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        if ($_SESSION['showAll']) {
          $form = $event->getForm();
          $form
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

            ));


        };
      });

      $builder
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
/*            ->add('media', 'sonata_media_type', array(
                'required' => false,
                'provider' => 'sonata.media.provider.image',
                'context' => 'quote'

            ))*/
        ;


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\QuoteBundle\Entity\Quote',
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
