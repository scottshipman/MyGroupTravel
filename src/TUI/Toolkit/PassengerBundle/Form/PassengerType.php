<?php

namespace TUI\Toolkit\PassengerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;


class PassengerType extends AbstractType
{
    private $locale;
    private $tourId;

    public function __construct($locale, $tourId)
    {
        $this->locale = $locale;
        $this->tourId = $tourId;
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
            ->add('fName', 'text', array(
                'required' => true,
                'label' => 'passenger.form.invite.first',
                'constraints' => new NotBlank(array(
                    'message' => 'Please enter a First Name'
                )),
            ))
            ->add('lName', 'text', array(
                'required' => true,
                'label' => 'passenger.form.invite.last',
                'constraints' => new NotBlank(array(
                    'message' => 'Please enter a Last Name'
                )),
            ))
            ->add('dateOfBirth', 'birthday', array(
                'format' => $date_format,
                'required' => true,
                'attr' => array(
                    'class' => 'dateOfBirth',
                ),
                'years' => range(date('Y') - 30, date('Y') - 1),
                'invalid_message' => 'Please enter a valid date'
            ))
            ->add('gender', 'choice', array(
                'choices' => array(
                    'Male' => 'Male',
                    'Female' => 'Female'
                ),
                'label' => 'passenger.form.invite.gender',
                'required' => true,
            ))
            ->add('tourReference', 'entity', array(
                'class' => 'TourBundle:Tour',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.id = ?1')
                        ->setParameter(1, $this->tourId )
                        ->orderBy('t.name', 'ASC');
                },
                'attr' => array(
                    'class' => 'tour-reference'
                )
            ))
            ->add('media', 'hidden', array(
                'required' => false,
                'data_class' => 'TUI\Toolkit\MediaBundle\Entity\Media',
                'attr' => array(
                    'class' => 'media-placeholder dropzone-form-primary',
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
            'data_class' => 'TUI\Toolkit\PassengerBundle\Entity\Passenger',
            'cascade_validation' => true,
            'error_bubbling' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_passengerbundle_passenger';
    }
}
