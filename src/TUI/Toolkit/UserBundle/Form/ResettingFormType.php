<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TUI\Toolkit\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use TUI\Toolkit\UserBundle\EventListener\AddTermsFieldSubscriber;

class ResettingFormType extends AbstractType
{
  // http://symfony.com/doc/current/bundles/FOSUserBundle/overriding_forms.html
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

      $builder

        ->add('answerConfirm', 'text', array(
          'label' => 'user.form.answer',
          'translation_domain'  => 'messages',
          'required' => true,
          'mapped'  => false,
        ))
        ->add('plainPassword', 'repeated', array(
          'type' => 'password',
          'options' => array('translation_domain' => 'FOSUserBundle'),
          'first_options' => array('label' => 'form.new_password'),
          'second_options' => array('label' => 'form.new_password_confirmation'),
          'invalid_message' => 'fos_user.password.mismatch',
          'required' => true,
        ))
      ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_user_password_reset';
    }
}
