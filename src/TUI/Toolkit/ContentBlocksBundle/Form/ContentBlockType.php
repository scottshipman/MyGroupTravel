<?php

namespace TUI\Toolkit\ContentBlocksBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

class ContentBlockType extends AbstractType
{
  private $securityContext;

  public function __construct(SecurityContext $securityContext)
  {
    $this->securityContext = $securityContext;
  }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $securityContext = $this->securityContext;
      if(strpos($options['action'], '/headerblock/')===false) {

        $builder
          ->add('title', 'text', array(
            'data' => (isset($options['data']) && $options['data']->getTitle() != null) ? $options['data']->getTitle() : 'New Content Block',
              'empty_data' => 'New Content Block'
          ))
          ->add('body', 'ckeditor', array());

      }

            // Dont show locked or hidden fields unless Brand role or higher
            if($securityContext->isGranted('ROLE_BRAND')) {
                if(strpos($options['action'], '/QuoteVersion/')===true) {
                    // but no locked field for quotes, just tours
                    $builder->add('locked', 'checkbox', array(
                        'required' => FALSE
                    ));
                }
              // "Hidden" field is now hidden!
              $builder
                ->add('hidden', 'hidden', array(
                  'data' => 0
                ));
            }

            // only show Layout Type , double wide and slideshow for Content Blocks, not Header Blocks
            if(strpos($options['action'], '/headerblock/')===false) {
              $builder
                ->add('layoutType', 'entity', array(
                  'class' => 'ContentBlocksBundle:LayoutType',
               //   'data_class' => 'TUI\Toolkit\ContentBlocksBundle\Entity\LayoutType',
                  'choice_label' => 'name',
                  'expanded' => TRUE,
                  'multiple' => FALSE,
                  'required' => TRUE
                ))
                ->add('doubleWidth', 'checkbox', array(
                  'required' => false
                ))

                ->add('isSlideshow', 'checkbox', array(
                  'required' => false,
                  'label' => 'This is a Slideshow',
                ));
            }
            $builder
              ->add('mediaWrapper', 'hidden', array(
                'required' => false,
//                'data_class' => 'TUI\Toolkit\MediaBundle\Entity\Media',
                'attr' => array(
                    'class' => 'media-placeholder',
//                    'multiple' => true
                )
              ))

                ->getForm()
            ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TUI\Toolkit\ContentBlocksBundle\Entity\ContentBlock'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tui_toolkit_contentblocksbundle_contentblock';
    }
}
