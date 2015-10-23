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
        $form = $event->getForm();
        if ($_SESSION['showAll']) { // editing a quote
          $form
            ->add('secondaryContact', 'genemu_jqueryautocomplete_entity', array(
              'class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'required' => FALSE,
              'route_name' => 'retrieve_salesagent_name',
              'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'label' => 'quote.form.quote.secondaryContact',
              'translation_domain'  => 'messages',
              //'multiple' => true,
            ))
            ->add('organizer', 'genemu_jqueryautocomplete_entity', array(
              'route_name' => 'retrieve_organizers_name',
              'class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'label' => 'quote.form.quote.organizer',
              'translation_domain'  => 'messages',
              'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'configs' => array('minLength' => 3),
              'attr' => array(
                'class' => 'suggest',
              ),
            ))
            ->add('institution', 'genemu_jqueryautocomplete_entity', array(
              'class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution',
              'label' => 'quote.form.quote.institution',
              'translation_domain'  => 'messages',
              'route_name' => 'retrieve_institution_name',
              'data_class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution',
              'configs' => array('minLength' => 3),
              'attr' => array(
                'class' => 'suggest',
              ),

            ));


        } else {
          // change label on Prim Biz Admin
          $form->add('salesAgent', 'genemu_jqueryautocomplete_entity', array(
            'required' => true,
            'class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'route_name' => 'retrieve_salesagent_name',
            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'label' => 'quote.form.quote.salesAgentTemplate',
            'translation_domain'  => 'messages',
          ));

        }
      });

      $builder
            ->add('name', 'text', array(
              'label' => 'quote.form.quote.name',
              'translation_domain'  => 'messages',
            ))
            ->add('destination', 'genemu_jqueryautocomplete_entity', array(
                'class' => 'TUI\Toolkit\QuoteBundle\Entity\Quote',
                'property' => 'destination',
                'label'   => 'quote.form.quote.destination',
                'translation_domain'  => 'messages',
            ))

            ->add('salesAgent', 'genemu_jqueryautocomplete_entity', array(
                'required' => true,
                'class' => 'TUI\Toolkit\UserBundle\Entity\User',
                'route_name' => 'retrieve_salesagent_name',
                'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
                'label' => 'quote.form.quote.salesAgent',
                'translation_domain'  => 'messages',
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
