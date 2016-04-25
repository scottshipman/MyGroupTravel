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
            new \Twig_SimpleFilter('checkUserPermissions', array($this, 'checkUserPermissions')),
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

    /**
     * Check user permissions.
     *
     * TWIG usage Ex: {% set role = 'tour' | checkUserPermissions(tour.id, ["parent", "organizer", "assistant"], "ROLE_BRAND") %}
     *            Ex2: {% if 'passenger' | checkUserPermissions(passenger.id, ["parent", "organizer", "assistant"], "ROLE_BRAND") %}
     *
     * @param $class
     * @param $object
     * @param $grants
     * @param $role_override
     * @return mixed
     */
    public function checkUserPermissions($class, $object = NULL, $grants = NULL, $role_override = NULL) {
        return $this->container->get("permission.set_permission")->checkUserPermissions($class, $object, $grants, $role_override);
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