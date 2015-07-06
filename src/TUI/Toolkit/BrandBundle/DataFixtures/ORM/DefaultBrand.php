<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\BrandBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TUI\Toolkit\BrandBundle\Entity\Brand;


class DefaultBrand implements FixtureInterface
{
  /**
   * {@inheritDoc}
   */

  public function load(ObjectManager $manager)
  {
    // load a default Brand Object in the database.
    $brand = new Brand();
    $brand->setName('ToolkitDefaultBrand');
    $brand->setDivision('ToolkitDefaultDivision');
    $brand->setPrimaryColor('#4a494a');
    $brand->setHoverColor('#f9f9fa');
    $brand->setButtonColor('#4a494a');

    $manager->persist($brand);
    $manager->flush();
  }
} 