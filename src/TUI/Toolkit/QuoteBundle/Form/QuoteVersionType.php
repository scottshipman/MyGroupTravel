<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class QuoteVersionType extends AbstractType
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
            ->add('revision', 'hidden', array(
              'mapped' => false,
            ))
            ->add('version', 'integer', array('read_only' => true, 'label' => 'Version Number'))
            // add the QuoteType form first
            ->add('quoteReference', new QuoteType(), array(
              'label' => 'Quote details'
              ))
            // now the versionable fields
              // Expire default should be now + 30 days
            ->add('expiryDate', 'genemu_jquerydate', array(
              'widget' => 'single_text',
              'required' => false,
              'label' => 'Quote Expiration? ' .$date_label,
              'format' => $date_format,
            ))
            ->add('departureDate', 'genemu_jquerydate', array(
              'widget' => 'single_text',
              'required' => false,
              'label' => 'Departure Date ' .$date_label,
            ))
            ->add('returnDate', 'genemu_jquerydate', array(
              'widget' => 'single_text',
              'required' => false,
              'label' => 'Return Date ' .$date_label,
            ))
            ->add('duration', 'text', array(
              'required'  => false,
              'label' => 'Duration',
            ))
            ->add('boardBasis','entity', array(
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
            ->add('transportType','entity', array(
              'required' => false,
              'placeholder' => 'Select',
              'class' => 'TransportBundle:Transport',
              'property' => 'name',
              'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('t')
                    ->orderBy('t.name', 'ASC');
                    },
                ))
            ->add('freePlaces', 'integer', array(
              'label' => 'Free Places',
              'required'  => false,
            ))
            ->add('payingPlaces', 'integer', array(
              'label' => 'Paying Places',
              'required'  => false,
            ))
            ->add('pricePerson', 'integer', array(
              'label' => 'Price per Person',
              'required'  => false,
            ))
            ->add('currency', 'entity', array(
                'required' => false,
                'placeholder' => 'Select',
                'class' => 'CurrencyBundle:Currency',
                'property'  =>  'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC');
                    },
              ))
            ->add('content', 'hidden', array(
                'empty_data' => 0, //'set this to whatever is the parent'
              ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\QuoteBundle\Entity\QuoteVersion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_quotebundle_quoteversion';
    }
}
