<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\InstitutionBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TUI\Toolkit\institutionBundle\Entity\Institution;

class CatholicSchool implements FixtureInterface, ContainerAwareInterface
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
    $institution->setName('Sister Mary Elizabeth of the Broken Souls');
    $institution->setAddress1('21666 Christ Saves Drive');
    $institution->setAddress2('Floor 6');
    $institution->setCity('Branson');
    $institution->setCounty('Branson');
    $institution->setState('MO');
    $institution->setPostCode('53143');
    $institution->setCountry('US');

    $institutionManager->persist($institution);
    $institutionManager->flush();
  }
} 