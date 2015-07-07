<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\TransportBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TUI\Toolkit\TransportBundle\Entity\Transport;


class DefaultTransport implements FixtureInterface
{
  /**
   * {@inheritDoc}
   */

  public function load(ObjectManager $manager)
  {
    // load default Transport Types in the database.
    $types=array('Coach', 'Flight', 'Self Driven');

    foreach ($types as $type) {
      $transport = new Transport();
      $transport->setName($type);
      $manager->persist($transport);
    }
    $manager->flush();
  }
} 