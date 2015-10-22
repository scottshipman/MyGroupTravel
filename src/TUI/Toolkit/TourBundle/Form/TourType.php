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
            'label' => 'tour.form.tour.name',
          ))

          ->add('tripStatus', 'entity', array(
            'required' => false,
            'placeholder' => 'tour.form.tour.placeholder',
            'class' => 'TripStatusBundle:TripStatus',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
              return $er->createQueryBuilder('t')
                ->where('t.visible = TRUE' )
                ->orderBy('t.id', 'ASC');
            },
          ))
          ->add('quoteNumber')
          ->add('version', 'text', array(
            'read_only'  => true,
          ))
          ->add('currency', 'entity', array(
            'required' => false,
            'placeholder' => 'tour.form.tour.placeholder',
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
            'label' => 'tour.form.tour.expiration ' . $date_label,
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
            'label' => 'tour.form.tour.sales_agent',
          ))
          ->add('secondaryContact', 'genemu_jqueryautocomplete_entity', array(
            'class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'required' => FALSE,
            'route_name' => 'retrieve_salesagent_name',
            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User',
            'label' => 'tour.form.tour.second_contact',
            //'multiple' => true,
          ))
          ->add('destination', 'genemu_jqueryautocomplete_entity', array(
            'class' => 'TUI\Toolkit\QuoteBundle\Entity\Quote',
            'property' => 'destination',
            'label'   => 'tour.form.tour.destination',
          ))
          ->add('departureDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.departure ' . $date_label,
            'format' => $date_format,
          ))
          ->add('returnDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.return ' . $date_label,
            'format' => $date_format,
          ))
          ->add('duration')
          ->add('transportType', 'entity', array(
            'required' => false,
            'placeholder' => 'tour.form.tour.placeholder',
            'class' => 'TransportBundle:Transport',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
              return $er->createQueryBuilder('t')
                ->orderBy('t.name', 'ASC');
            },
          ))
          ->add('boardBasis', 'entity', array(
            'label' => 'tour.form.tour.board',
            'required' => false,
            'placeholder' => 'tour.form.tour.placeholder',
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
            'type' => new PaymentTaskType($this->locale),
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
          ))

          ->add('passengerDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.passenger_date ' . $date_label,
            'format' => $date_format,
          ))

          ->add('passportDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.passport_date ' . $date_label,
            'format' => $date_format,
          ))

          ->add('medicalDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.medical_date ' . $date_label,
            'format' => $date_format,
          ))

          ->add('dietaryDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.dietary_date ' . $date_label,
            'format' => $date_format,
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
