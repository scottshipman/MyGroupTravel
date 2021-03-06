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
    $brand->setPrimaryColor('rgb(96, 125, 139)');
    $brand->setSecondaryColor('rgb(33, 150, 243)');
    $brand->setTertiaryColor('rgb(96, 125, 139)');
    $brand->setFooterBody('');

    $manager->persist($brand);
    $manager->flush();
  }
}