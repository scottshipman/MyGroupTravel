<?php

namespace AppBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TuiHTMLPurifier implements DataTransformerInterface
{
  /**
   * @var \HTMLPurifier
   */
  private $purifier;
  /**
   * @var array
   */
  private $config;

  /**
   * @param array $config
   */
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
    $parameter = $this->container->getParameter('app.html_purifier');
    $this->config = $parameter['config'];
  }

  /**
   * {@inheritDoc}
   */
  public function transform($data)
  {
    return $data;
  }

  /**
   * {@inheritDoc}
   */
  public function reverseTransform($data)
  {
    $standardPurifier = $this->getPurifier()->purify($data);

    return preg_replace('/<img[^>]+\>/is', '', $data);
  }

  /**
   * @return \HTMLPurifier
   */
  protected function getPurifier()
  {
    if (null === $this->purifier) {

      // tweak the config
      $config = \HTMLPurifier_Config::createDefault();
      $config->set('HTML.DefinitionID', 'tui html purifier');
      $config->set('HTML.DefinitionRev', 1);
      foreach($this->config as $key => $value){
        $config->set($key, $value);
      }
      if ($def = $config->maybeGetRawHTMLDefinition()) {
        // our custom add-ons will go here
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');

      }


      $this->purifier = new \HTMLPurifier($config);

    }
    return $this->purifier;
  }
}