<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class QuoteType extends AbstractType
{
  /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      // Complex event listener for dealing with Templates
      $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        // is this an existing Quote and is a template
        $entity = $event->getData();
        $form = $event->getForm();
        $request = explode('/', $_SERVER['REQUEST_URI']);
        $newTemplate = false;
        $isNew=false;
        $hasTemplate=false;
        $showAll = false;

        if(!$entity || null===$entity->getId()){
          $isNew = true;
        } elseif ($entity->getIsTemplate()==true){
          $hasTemplate = true;
        }
        if(isset($request[3]) && $request[3]=="new" && isset($request[4]) && $request[4]=="template")
        { $newTemplate = true;}

        // CASE: New Object - hidden isTemplate w value of newTemplate
        if($isNew){
          $form->add('isTemplate', 'hidden', array(
            'data' => $newTemplate,
          ));
          if (!$newTemplate){$showAll = true;}
        }

        // CASE: Editing an existing Template - hidden isTemplate w/ true value
        if((!$isNew && $hasTemplate)){
          $form->add('isTemplate', 'hidden', array(
            'data' => $hasTemplate,
          ));
        }

        // CASE Editing an existing quote - hide isTemplate - show other fields
         if(!$isNew && !$hasTemplate){
            $form->add('isTemplate', 'checkbox', array(
              'required' => FALSE,
              'label' => "Convert to Template?",
            ));
           $showAll = true;
         }

        // CASE Editing a quote or creating a new quote - show non-Template fields
        if($showAll){
          $form
            ->add('organizer', 'entity', array(
              'label' => 'Organizer',
              'required' => false,
              'placeholder' => 'Select',
              'class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                    ->where('u.roles LIKE :role')
                    ->setParameters(array('role' => "%ROLE_CUSTOMER%"))
                    ->orderBy('u.email', 'ASC');
                },
            ))
            ->add('institution', 'entity', array(
              'required' => false,
              'placeholder' => 'Select',
              'class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution',
              'property' => 'name',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('i')
                    ->orderBy('i.name', 'ASC');
                },
            ));

        }

      });

          $builder
            ->add('name')
            ->add('destination')
            ->add('reference')
            ->add('salesAgent','entity', array(
              'required' => false,
              'placeholder' => 'Select',
              'class' => 'TUI\Toolkit\UserBundle\Entity\User',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                    ->where('u.roles LIKE :role')
                    ->setParameters(array('role' => "%ROLE_BRAND%"))
                    ->orderBy('u.email', 'ASC');
                    },
                ))
            ->add('media', 'sonata_media_type', array(
                'required' => false,
                'provider' => 'sonata.media.provider.image',
                'context' => 'quote'

            ))
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
