<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class QuoteSummaryType extends AbstractType
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

              // Expire default should be now + 30 days
            ->add('expiryDate', 'genemu_jquerydate', array(
              'widget' => 'single_text',
              'required' => false,
              'label' => 'quote.form.summary.expiry',
                'translation_domain'  => 'messages',
              'format' => $date_format,
            ))
            ->add('departureDate', 'genemu_jquerydate', array(
              'widget' => 'single_text',
              'required' => false,
              'label' => 'quote.form.summary.departure',
              'translation_domain'  => 'messages',
              'format' => $date_format,
            ))
            ->add('returnDate', 'genemu_jquerydate', array(
              'widget' => 'single_text',
              'required' => false,
              'label' => 'quote.form.summary.return',
              'translation_domain'  => 'messages',
              'format' => $date_format,
            ))
            ->add('duration', 'text', array(
              'required'  => false,
              'label' => 'quote.form.summary.duration',
              'translation_domain'  => 'messages',
            ))
            /* ->add('displayName', 'text', array(
              'required'  => false,
              'label' => 'quote.form.summary.display_name',
              'translation_domain'  => 'messages',
            )) */
            ->add('boardBasis','entity', array(
            'label' => 'quote.form.summary.boardBasis',
              'translation_domain'  => 'messages',
            'required' => false,
            'placeholder' => 'Select',
            'class' => 'BoardBasisBundle:BoardBasis',
            'property' => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('b')
                  ->orderBy('b.name', 'ASC');
                },
              ))
            ->add('transportType','entity', array(
              'required' => false,
              'label' => 'quote.form.summary.transport',
              'translation_domain'  => 'messages',
              'placeholder' => 'Select',
              'class' => 'TransportBundle:Transport',
              'property' => 'name',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('t')
                    ->orderBy('t.name', 'ASC');
                    },
                ))
            ->add('freePlaces', 'integer', array(
              'label' => 'quote.form.summary.freePlaces',
              'translation_domain'  => 'messages',
              'required'  => false,
            ))
            ->add('payingPlaces', 'integer', array(
              'label' => 'quote.form.summary.payingPlaces',
              'translation_domain'  => 'messages',
              'required'  => false,
            ))
            ->add('pricePerson', 'integer', array(
              'label' => 'quote.form.summary.pricePerson',
              'translation_domain'  => 'messages',
              'required'  => false,
            ))
            ->add('currency', 'entity', array(
                'required' => false,
              'translation_domain'  => 'messages',
              'label' => 'quote.form.summary.currency',
                'placeholder' => 'Select',
                'class' => 'CurrencyBundle:Currency',
                'property'  =>  'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
                    },
              ))
            ->add('welcomeMsg', 'ckeditor', array(
                'label' => 'quote.form.summary.welcomeMsg',
              'translation_domain'  => 'messages',
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\QuoteBundle\Entity\QuoteVersion',
            'cascade_validation' => true
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_quotebundle_quotesummary';
    }
}
