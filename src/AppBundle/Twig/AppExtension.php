<?php
/**
 * Created by scottshipman
 * add a Passenger Status Label twig extension
 */

// src/AppBundle/Twig/AppExtension.php
namespace AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;


class AppExtension extends \Twig_Extension {
    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('paxLabel', array($this, 'paxLabel')),
            new \Twig_SimpleFilter('getRoles', array($this, 'getRoles')),
            new \Twig_SimpleFilter('getClass', array($this, 'getClass')),
        );
    }

    /*
     * TWIG usage Ex: {{ 'waitlist' | paxLabel }}
     */
    public function paxLabel($status) {
        if ($this->container->getParameter($status . "_label")) {
            return ucfirst($this->container->getParameter($status . "_label"));
        } else {
            return ucfirst($status);
        }
    }

    /*
     * TWIG usage Ex: {% set role = 'tour' | getRoles(tour.id) %}
     *            Ex2: {% if 'passenger' | getRoles(passenger.id)=='parent' %}
     */
    public function getRoles($class, $objectId) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $roles = $this->container->get("permission.set_permission")->getPermission($objectId, $class, $user);
        return array_shift($roles);
    }

    public function getName()
    {
        return 'app_extension';
    }

    public function getClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}