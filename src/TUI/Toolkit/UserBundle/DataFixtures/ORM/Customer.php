<?php
/**
 * Created scottshipman
 * Date: 6/25/15
 */

namespace TUI\Toolkit\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TUI\Toolkit\UserBundle\Entity\User;
use FOS\UserBundle\Doctrine\UserManager;

class Customer implements FixtureInterface, ContainerAwareInterface
{
  private $container;

  public function setContainer(ContainerInterface $container = null)
  {
    $this->container = $container;
  }

  public function load(ObjectManager $userManager)
  {
    // Get our userManager, you must implement `ContainerAwareInterface`
    //$userManager = $this->container->get('fos_user.user_manager');

    // Create our user and set details
    $user = new User();
    $user->setUsername('joe.customer@email.com');
    $user->setEmail('joe.customer@email.com');
    $user->setPlainPassword('Zend1234!');
    $user->setEnabled(true);
    $user->setRoles(array('ROLE_CUSTOMER'));
    $user->setFirstName('Joe');
    $user->setLastName('Customer');
    $user->setDisplayName('');

    $userManager->persist($user);
    $userManager->flush();
  }
}