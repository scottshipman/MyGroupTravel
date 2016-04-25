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
     * TWIG usage Ex: {% set role = 'tour' | checkUserPermissions(tour.id, ["parent", "organizer", "assistant"]) %}
     *            Ex2: {% if 'passenger' | checkUserPermissions(passenger.id, ["parent", "organizer", "assistant"]) %}
     *
     * @param $class
     * @param $roles
     * @return mixed
     */
    public function checkUserPermissions($class, $objectId = NULL, $roles = NULL) {
        if (is_string($roles)) {
          $roles = array($roles);
        }

        $user = $this->container->get('security.context')->getToken()->getUser();

        if (!is_null($objectId)) {
          $user_roles = $this->container->get("permission.set_permission")->getPermission($objectId, $class, $user);
        }
        else {
          $user_roles = $this->container->get("permission.set_permission")->getAllPermissions($class, $user);
        }

        if (!empty($user_roles)) {
          if (!empty($roles)) {
            $matched_roles = array_intersect($roles, $user_roles);

            if (!empty($matched_roles)) {
              return TRUE;
            }
          }
          else {
            return TRUE;
          }
        }

        return FALSE;
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