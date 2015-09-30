<?php

namespace AppBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

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
      $this->purifier = new \HTMLPurifier($this->config);
    }
    return $this->purifier;
  }
}