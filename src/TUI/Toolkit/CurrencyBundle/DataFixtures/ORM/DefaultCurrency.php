<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\CurrencyBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TUI\Toolkit\CurrencyBundle\Entity\Currency;


class DefaultCurrency implements FixtureInterface
{
  /**
   * {@inheritDoc}
   */

  public function load(ObjectManager $manager)
  {
    // load default Transport Types in the database.
    $currency_array=array(
        '1'=>array( 'GBP', '&pound;', 'Pound sterling'),
        '2'=>array( 'USD', '$', 'US dollar'),
        '3'=>array( 'EUR', '&euro;', 'Euro'),
        '4'=>array( 'AUD', '$', 'Australian dollar'),
        '5'=>array( 'NZD', '$', 'New Zealand dollar')
    );


    foreach ($currency_array as $currency) {
      $entity = new Currency();
      $entity->setName($currency[2]);
      $entity->setCode($currency[0]);
      $entity->setHtmlSymbol($currency[1]);
      $manager->persist($entity);
    }

    $manager->flush();
  }
}