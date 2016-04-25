<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use TUI\Toolkit\TourBundle\Entity\Tour;

class TourType extends AbstractType
{
   private $tour;
    private $locale;

    public function __construct(Tour $tour, $locale)
    {
      $this->tour = $tour;
      $this->locale = $locale;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      switch (true) {
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
            'label' => 'tour.form.tour.trip_status',
          ))
          ->add('quoteNumber', 'text', array(
            'required' => false,
            'disabled' => true,
            'label' => 'tour.form.tour.quote_number',
          ))
          ->add('tourReference', 'text', array(
            'required' => false,
            'label' => 'tour.form.tour.tour_number',
          ))
          ->add('displayName', 'text', array(
            'required'  => false,
            'label' => 'tour.form.tour.display_name',
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
            'label' => 'tour.form.tour.expiration',
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
            'required' => false,
            'label'   => 'tour.form.tour.destination',
          ))
          ->add('departureDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.departure',
            'format' => $date_format,
          ))
          ->add('returnDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.return',
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
            'label' => 'tour.form.tour.transport_type'
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
          ->add('payingPlaces', 'integer', array(
            'label' => 'tour.form.tour.paying_places',
          ))
          ->add('freePlaces', 'integer', array(
            'label' => 'tour.form.tour.free_places',
          ))
          ->add('pricePerson', 'integer', array(
            'label' => 'tour.form.tour.price_per_person',
          ))
          ->add('paymentTasks', 'collection', array(
            'type' => new PaymentTaskType($this->tour, $this->locale),
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
          ))

          ->add('emergencyDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.passenger_date',
            'format' => $date_format,
          ))

          ->add('passportDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.passport_date',
            'format' => $date_format,
          ))

          ->add('medicalDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.medical_date',
            'format' => $date_format,
          ))

          ->add('dietaryDate', 'genemu_jquerydate', array(
            'widget' => 'single_text',
            'required' => false,
            'label' => 'tour.form.tour.dietary_date',
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
