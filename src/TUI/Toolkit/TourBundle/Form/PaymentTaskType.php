<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use TUI\Toolkit\TourBundle\Entity\Tour;

class PaymentTaskType extends AbstractType
{
  private $tour;

  public function __construct (Tour $tour)
  {
    $this->tour = $tour;
  }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'tour.form.payment_task.type',
            ))
            ->add('value', 'money', array(
                'label' =>  'tour.form.payment_task.value',
                'currency' => $this->tour->getCurrency()->getCode(),
                'scale' => 2,
            ))
            ->add('dueDate', 'genemu_jquerydate', array(
              'widget' => 'single_text',
              'required' => true,
              'label' => 'tour.form.payment_task.date',
            ))


//            ->add('type', 'hidden', array( //make this hidden eventually
//                  'data' => $this->paymentType,
//            ))
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
