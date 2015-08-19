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
        $builder
            ->add('title')
            ->add('body', 'ckeditor', array(
                'transformers' => array('html_purifier'),
                'toolbar' => array('document', 'editing', 'tools', 'basicstyles'),
                'toolbar_groups' => array(
                    'document' => array('Source')
                ),
                'ui_color' => '#ffffff',
                'startup_outline_blocks' => false,
                'width' => '100%',
                'height' => '320',
            ));

            // Dont show locked or hidden fields unless Brand role or higher
            if($securityContext->isGranted('ROLE_BRAND')) {
             $builder ->add('locked', 'checkbox', array(
                'required' => FALSE
              ))
                ->add('hidden', 'checkbox', array(
                  'required' => FALSE
                ));
                }
            $builder
              ->add('layoutType', 'entity', array(
                'class' => 'ContentBlocksBundle:LayoutType',
                'data_class' => 'TUI\Toolkit\ContentBlocksBundle\Entity\LayoutType',
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => false
            ))
            ->add('media', 'hidden', array(
                'required' => false,
//                'data_class' => 'TUI\Toolkit\MediaBundle\Entity\Media',
                'attr' => array(
                    'class' => 'media-placeholder',
//                    'multiple' => true
                )
            ))
            ->add('doubleWidth', 'checkbox', array(
                'required' => false
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
