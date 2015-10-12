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
    // look for data-oembed-url attribute on divs and save strings for replacement
    $new_data = $data;
    preg_match_all('/<div[^>]+>/i',$new_data, $match);
    foreach($match[0] as $key=>$value){
      if (strpos($value, 'data-oembed-url') !== FALSE){
        // div with attribute we want to store it and generate placeholder
        $new_data = str_replace($value, "<div>[EMBED-TOKEN-$key]", $new_data);
      }
    }
    $purified = $this->getPurifier()->purify($new_data);

    // now put back the special attributes into out placeholders
    $result = $purified;
    foreach($match[0] as $key=>$value) {
      if (strpos($purified, "<div>[EMBED-TOKEN-$key]") !== FALSE){
        // one of our placeholders, so replace it
        $result = str_replace("<div>[EMBED-TOKEN-$key]", $value, $result);
      }
    }



    return $result;

  }

  /**
   * @return \HTMLPurifier
   */
  protected function getPurifier()
  {
    if (null === $this->purifier) {

      // tweak the config
      $config = \HTMLPurifier_Config::createDefault();
      //$config->set('HTML.DefinitionID', 'tui html purifier');
      //$config->set('HTML.DefinitionRev', 1);
      $config->set('CSS.Trusted', true);
      $config->set('URI.AllowedSchemes', array('http' => true, 'https' => true, 'mailto' => true,  'data' => true));
      foreach($this->config as $key => $value){
        $config->set($key, $value);
      }
      if ($def = $config->getHTMLDefinition(true)) {
        // our custom add-ons will go here
        $anon = $def->getAnonymousModule();
        $anon->attr_collections = array(
          'Core' => array(
            'data-oembed-url' => 'CDATA',
          )
        );
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $def->addAttribute('div', 'style', 'CDATA' );
        $def->addAttribute('div', 'data-oembed-url', 'CDATA' );


      }


      $this->purifier = new \HTMLPurifier($config);

    }
    return $this->purifier;
  }
}