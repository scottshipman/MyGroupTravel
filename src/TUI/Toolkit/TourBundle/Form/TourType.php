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
          ->add('name', 'text', array(
            'label' => 'Tour Name',
          ))
          ->add('quoteNumber')
          ->add('version', 'text', array(
            'read_only'  => true,
          ))
          ->add('currency', 'entity', array(
            'required' => false,
            'placeholder' => 'Select',
            'class' => 'CurrencyBundle:Currency',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
              return $er->createQueryBuilder('c')
                ->orderBy('c.name', 'ASC');
            },
          ))
          ->add('expiryDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Quote Expiration? ' . $date_label,
            'format' => $date_format,
          ))
          ->add('institution', 'genemu_jqueryautocomplete_entity', array(
            'class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution',
            'route_name' => 'retrieve_institution_name',
            'data_class' => 'TUI\Toolkit\InstitutionBundle\Entity\Institution',
            'configs' => array('minLength' => 3),
            'attr' => array(
              'class' => 'suggest',
            )
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
          ->add('salesAgent', 'genemu_jqueryautocomplete_entity', array(
            'required' => true,
            'class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'route_name' => 'retrieve_salesagent_name',
            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'label' => 'Primary Business Admin',
          ))
          ->add('secondaryContact', 'genemu_jqueryautocomplete_entity', array(
            'class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'required' => FALSE,
            'route_name' => 'retrieve_salesagent_name',
            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'label' => 'Other Business Admin',
            //'multiple' => true,
          ))
          ->add('destination', 'genemu_jqueryautocomplete_entity', array(
            'class' => 'TUI\Toolkit\QuoteBundle\Entity\Quote',
            'property' => 'destination',
            'label'   => 'Destination',
          ))
          ->add('departureDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Departure Date ' . $date_label,
            'format' => $date_format,
          ))
          ->add('returnDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Return Date ' . $date_label,
            'format' => $date_format,
          ))
          ->add('duration')
          ->add('transportType', 'entity', array(
            'required' => false,
            'placeholder' => 'Select',
            'class' => 'TransportBundle:Transport',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
              return $er->createQueryBuilder('t')
                ->orderBy('t.name', 'ASC');
            },
          ))
          ->add('boardBasis', 'entity', array(
            'label' => 'Board Basis',
            'required' => false,
            'placeholder' => 'Select',
            'class' => 'BoardBasisBundle:BoardBasis',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
              return $er->createQueryBuilder('b')
                ->orderBy('b.name', 'ASC');
            },
          ))
          ->add('payingPlaces')
          ->add('freePlaces')
          ->add('pricePerson')
          ->add('paymentTasks', 'collection', array(
            'type' => new PaymentTaskType('institution', $this->locale),
            'allow_add'    => true,
            'by_reference' => false,
            'allow_delete' => true,
          ))

          ->add('passengerDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Passenger Info Due Date ' . $date_label,
            'format' => $date_format,
          ))

          ->add('passportDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Passport Info Due Date ' . $date_label,
            'format' => $date_format,
          ))

          ->add('medicalDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Medical Info Due Date ' . $date_label,
            'format' => $date_format,
          ))

          ->add('dietaryDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Dietary Info Due Date ' . $date_label,
            'format' => $date_format,
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
            'cascade_validation' => true,
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
