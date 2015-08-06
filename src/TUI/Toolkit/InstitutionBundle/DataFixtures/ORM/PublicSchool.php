<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\institutionBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TUI\Toolkit\institutionBundle\Entity\Institution;


class PublicSchool implements FixtureInterface, ContainerAwareInterface
{
  private $container;

  public function setContainer(ContainerInterface $container = null)
  {
    $this->container = $container;
  }

  public function load(ObjectManager $institutionManager)
  {

    // Create our institution and set details
    $institution = new Institution();
    $institution->setName('Public School Test School');
    $institution->setAddress1('22546 Educational Bliss Way');
    $institution->setAdddress2('Suite 100');
    $institution->setCity('Springfield');
    $institution->setCounty('Smart Township');
    $institution->setState('FL');
    $institution->setPostCode('34698');
    $institution->setCountry('US');

    $institutionManager->persist($institution);
    $institutionManager->flush();
  }
} 