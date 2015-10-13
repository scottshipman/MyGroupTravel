<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use TUI\Toolkit\UserBundle\Form\UserType;

class TourSetupType extends AbstractType
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
            ->add('pricePersonPublic', 'number', array(
                'label' => 'Price Per Person'
            ))
            ->add('paymentTasks', 'collection', array(
                'type' => new PaymentTaskType('institution', $this->locale),
                'allow_add'    => true,
                'by_reference' => false,
                'allow_delete' => true,
            ))
//            ->add('preferredPaymentTypes', 'choice', array(
//                'choices' => array(
//                    'Cash/Cheque' => 'Cash/Cheque',
//                    'Bank transfer' => 'Bank transfer',
//                    'Online payment' => 'Online payment',
//                    'Other' => 'Other'
//                ),
//                'multiple' => true,
//                'expanded' => true,
//                'required' => true
//            ))

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
        return 'tui_toolkit_tourbundle_toursetup';
    }
}
