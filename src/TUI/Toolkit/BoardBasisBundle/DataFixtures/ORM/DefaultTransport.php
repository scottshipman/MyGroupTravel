<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\BoardBasisBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TUI\Toolkit\BoardBasisBundle\Entity\BoardBasis;


class DefaultBoardBasis implements FixtureInterface
{
  /**
   * {@inheritDoc}
   */

  public function load(ObjectManager $manager)
  {
    // load default BoardBasis Types in the database.
    $types=array('All Inclusive', 'Bed & Breakfast', 'Full Board', 'Half Board', 'Mixed', 'Room only', 'Self catering');

    foreach ($types as $type) {
      $BoardBasis = new BoardBasis();
      $BoardBasis->setName($type);
      $manager->persist($BoardBasis);
    }
    $manager->flush();
  }
} 