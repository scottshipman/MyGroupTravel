<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\quoteversionBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TUI\Toolkit\QuoteBundle\Entity\QuoteVersion;
use TUI\Toolkit\QuoteBundle\Entity\Quote;


class FirstQuoteVersion implements FixtureInterface, ContainerAwareInterface
{
  private $container;

  public function setContainer(ContainerInterface $container = null)
  {
    $this->container = $container;
  }

  public function load(ObjectManager $quoteversionManager)
  {
    // Get our quoteversionManager, you must implement `ContainerAwareInterface`
    //$quoteversionManager = $this->container->get('fos_quoteversion.quoteversion_manager');

    // Create our quote and set details
    $quote = new Quote();
    $quote->setName('Second Tour For This Location');
    $quote->setDestination('The Love Boat');

    $quoteversionManager->persist($quote);
    $quoteversionManager->flush();

    // Create our quoteversion and set details
    $quoteversion = new QuoteVersion();
    $quoteversion->setName('Option 2 - Mints on Pillows');
    $quoteversion->setQuoteNumber('secondquote');
    $quoteversion->setVersion(1);
    $quoteversion->setExpiryDate(new \DateTime('now + 30 days'));
    $quoteversion->setFreePlaces(5);
    $quoteversion->setPayingPlaces(25);
    $quoteversion->setDepartureDate(new \DateTime('now + 90 days'));
    $quoteversion->setReturnDate(new \DateTime('now + 97 days'));
    $quoteversion->setQuoteReference($quote);
    $quoteversion->setDuration('7 days and 6 nites with 5 hotel stays');
    $quoteversion->setDisplayName('');
    $quoteversion->setPricePerson(2599);

    $quoteversionManager->persist($quoteversion);
    $quoteversionManager->flush();
  }
}