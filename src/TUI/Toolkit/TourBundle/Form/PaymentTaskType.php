<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PaymentTaskType extends AbstractType
{

  protected $paymentType;

  public function __construct ($paymentType = null, $locale = null)
  {
    $this->paymentType = $paymentType;
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
            ->add('name')
            ->add('value', 'integer', array(
              'label' =>  'Amount',
              'scale' => 2,
            ))

            ->add('dueDate', 'genemu_jquerydate', array(
              'widget' => 'single_text',
              'required' => true,
              'label' => 'Due Date ' . $date_label,
              'format' => $date_format,
            ))


            ->add('type', 'hidden', array( //make this hidden eventually
                  'data' => $this->paymentType,
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\TourBundle\Entity\PaymentTask',
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_tourbundle_paymenttask';
    }
}
