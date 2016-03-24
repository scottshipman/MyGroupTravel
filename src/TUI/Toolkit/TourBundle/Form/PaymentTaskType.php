<?php

namespace TUI\Toolkit\TourBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use TUI\Toolkit\TourBundle\Entity\Tour;

class PaymentTaskType extends AbstractType
{
  private $locale;
  private $tour;

  public function __construct (Tour $tour, $locale)
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
              'format' => $date_format,
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
