<?php
/**
 * Created by PhpStorm.
 * User: scottshipman
 * Date: 10/28/15
 * Time: 9:28 AM
 */

namespace TUI\Toolkit\UserBundle\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class AddTermsFieldSubscriber implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    // Tells the dispatcher that you want to listen on the form.pre_set_data
    // event and that the preSetData method should be called.
    return array(FormEvents::PRE_SET_DATA => 'preSetData');
  }

  public function preSetData(FormEvent $event)
  {
    $entity = $event->getData();
    $form = $event->getForm();

    // if not enabled then show "I agree" checkbox
    if (!empty($entity)) {
      if (FALSE === $entity->isEnabled()) {
        $form
/*          ->add('termsAgree', 'checkbox', array(
          'mapped' => FALSE,
          'required' => TRUE,
          ))
          ->add('question', 'text', array(
            'label' => 'user.form.question',
            'translation_domain'  => 'messages',
            'read_only' => false,
            'disabled'  => false,
          ))
          ->add('answer', 'text', array(
            'label' => 'user.form.answer',
            'translation_domain'  => 'messages',
            'required' => true,
          ))
        ;
      }
    }
  }
}