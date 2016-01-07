<?php

namespace TUI\Toolkit\PassengerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InviteOrganizerType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
/*        $builder
            ->add('email', 'email', array(
                'required' => true,
                'mapped' => false,
                'label' => 'passenger.form.invite.email',
                'translation_domain'  => 'messages',
            ))
            ->add('message', 'text', array(
                'required' => true,
                'mapped' => false,
                'label' => 'user.form.invite.message',
                'translation_domain'  => 'messages',
            ))
            ;*/

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_passengerbundle_invite_organizer';
    }
}
