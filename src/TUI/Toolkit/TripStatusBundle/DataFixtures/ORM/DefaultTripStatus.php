<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\TripStatusBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TUI\Toolkit\TripStatusBundle\Entity\TripStatus;


class DefaultTripStatus implements FixtureInterface
{
  /**
   * {@inheritDoc}
   */

  public function load(ObjectManager $manager)
  {
    // load default Transport Types in the database.
    $typesvisible=array('Accepted', 'Open');
    $typeshidden=array('Rejected - other operator', 'Rejected - too expensive', 'Superceded');

    foreach ($typesvisible as $type) {
      $status = new TripStatus();
      $status->setName($type);
      $status->setVisible(TRUE);
      $manager->persist($status);
    }

    foreach ($typeshidden as $type) {
      $status = new TripStatus();
      $status->setName($type);
      $status->setVisible(FALSE);
      $manager->persist($status);
    }

    $manager->flush();
  }
} 