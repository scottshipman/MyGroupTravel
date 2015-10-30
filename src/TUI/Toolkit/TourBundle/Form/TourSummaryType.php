<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TourSummaryType extends AbstractType
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
        $require_qn = true;


        switch ($this->locale){
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

            // Expire default should be now + 30 days
            ->add('expiryDate', 'genemu_jquerydate', array(
                'widget' => 'single_text',
                'required' => false,
                'label' => 'tour.form.tour_summary.expiration',
                'format' => $date_format,
            ))
            ->add('departureDate', 'genemu_jquerydate', array(
                'widget' => 'single_text',
                'required' => false,
                'label' => 'tour.form.tour_summary.departure',
                'format' => $date_format,
            ))
            ->add('returnDate', 'genemu_jquerydate', array(
                'widget' => 'single_text',
                'required' => false,
                'label' => 'tour.form.tour_summary.return',
                'format' => $date_format,
            ))
            ->add('duration', 'text', array(
                'required'  => false,
                'label' => 'tour.form.tour_summary.duration',
            ))
            ->add('displayName', 'text', array(
                'required'  => false,
                'label' => 'tour.form.tour_summary.display_name',
            ))
            ->add('boardBasis','entity', array(
                'label' => 'tour.form.tour_summary.board',
                'required' => false,
                'placeholder' => 'tour.form.tour_summary.placeholder',
                'class' => 'BoardBasisBundle:BoardBasis',
                'property' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->orderBy('b.name', 'ASC');
                },
            ))
            ->add('transportType','entity', array(
                'required' => false,
                'placeholder' => 'tour.form.tour_summary.placeholder',
                'class' => 'TransportBundle:Transport',
                'property' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
            ))
            ->add('freePlaces', 'integer', array(
                'label' => 'tour.form.tour_summary.free_places',
                'required'  => false,
            ))
            ->add('payingPlaces', 'integer', array(
                'label' => 'tour.form.tour_summary.paying_places',
                'required'  => false,
            ))
            ->add('pricePerson', 'integer', array(
                'label' => 'tour.form.tour_summary.price',
                'required'  => false,
            ))
            ->add('currency', 'entity', array(
                'required' => false,
                'placeholder' => 'tour.form.tour_summary.placeholder',
                'class' => 'CurrencyBundle:Currency',
                'property'  =>  'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ))
            ->add('welcomeMsg', 'ckeditor', array(
                'label' => 'tour.form.tour_summary.welcomeMsg',
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
            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_tourbundle_toursummary';
    }
}
