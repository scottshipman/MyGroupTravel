<?php

namespace TUI\Toolkit\QuoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
        $require_qn = true;

        // Complex event listener for dealing with Templates and Clones
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            // is this an existing Quote and is a template
            $entity = $event->getData();
            $form = $event->getForm();
            $request = explode('/', $_SERVER['REQUEST_URI']);
            $newTemplate = false;
            $isNew = false;
            $hasTemplate = false;
            $showAll = false;

            if (!$entity || null === $entity->getId()) {
                $isNew = true;
            }

            if ($entity->getIsTemplate() == true) {
                $hasTemplate = true;
            }
            if (isset($request[3]) && ($request[3] == "new" || $request[3] == 'create') && isset($request[4]) && $request[4] == "template") {
                $newTemplate = true;
            }

            if(isset($request[4]) && $request[4] == "duplicate-template"){
                $newTemplate = true;
            }

            if(isset($_REQUEST['tui_toolkit_quotebundle_quoteversion']['isTemplate']) && $_REQUEST['tui_toolkit_quotebundle_quoteversion']['isTemplate'] =='1'){
              $newTemplate = true;
          }

            // CASE: New Object - hidden isTemplate w value of newTemplate
            if ($isNew && $newTemplate) {
                $require_qn = false;
                $form->add('isTemplate', 'hidden', array(
                    'data' => true,
                     ))
                     ->add('quoteNumber', 'text', array(
                        'label' => 'Quote Number',
                        'required' => $require_qn,
                     ));
            }
            if ($isNew && !$newTemplate) {
                $showAll = true;
            }

            // CASE: Editing an existing Template - hidden isTemplate w/ true value
            if ($hasTemplate) {
                $require_qn = false;
                $form->add('isTemplate', 'hidden', array(
                    'data' => $hasTemplate,
                     ))
                     ->add('quoteNumber', 'text', array(
                        'label' => 'Quote Number',
                        'required' => $require_qn,
                     ));
            }

            // CASE Editing an existing quote - hide isTemplate - show other fields
            if (!$isNew && !$hasTemplate) {
                $form->add('isTemplate', 'checkbox', array(
                    'required' => FALSE,
                    'label' => "Convert to Template?",
                ));
                $showAll = true;
            };
            $_SESSION['showAll'] = $showAll;
        });

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
            ->add('revision', 'hidden', array(
                'mapped' => false,
            ))
            ->add('quoteReference', new QuoteType(), array(
                'label' => 'Quote details',
            ))
            // now the versionable fields
            ->add('name', 'text', array(
                'label' => 'Quote Name',
                'required' => $require_qn,
            ))
            ->add('quoteNumber', 'text', array(
                'label' => 'Quote Number',
                'required' => $require_qn,
            ))
            // Expire default should be now + 30 days
            ->add('expiryDate', 'genemu_jquerydate', array(
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Quote Expiration? ' . $date_label,
                'format' => $date_format,
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
            ->add('duration', 'text', array(
                'required' => false,
                'label' => 'Duration',
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
            ->add('freePlaces', 'integer', array(
                'label' => 'Free Places',
                'required' => false,
            ))
            ->add('payingPlaces', 'integer', array(
                'label' => 'Paying Places',
                'required' => false,
            ))
            ->add('pricePerson', 'integer', array(
                'label' => 'Price per Person',
                'required' => false,
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
            ->add('welcomeMsg', 'ckeditor', array(
                'label' => 'Summary Message',
            ));
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
        return 'tui_toolkit_quotebundle_quoteversion';
    }
}
