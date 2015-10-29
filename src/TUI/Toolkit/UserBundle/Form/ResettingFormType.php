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
      $builder->addEventSubscriber(new AddTermsFieldSubscriber());

      $builder
        ->add('question', 'text', array(
          'label' => 'user.form.question_prompt',
          'translation_domain'  => 'messages',
          'read_only' => true,
          'disabled'  => true,
        ))
        ->add('answer', 'text', array(
          'label' => 'user.form.answer',
          'translation_domain'  => 'messages',
          'required' => true,
          'mapped'  => false,
        ))
      ;
    }

    public function getParent()
    {
      return 'fos_user_resetting';
    }

    public function getName()
    {
        return 'tui_user_resetting';
    }
}
