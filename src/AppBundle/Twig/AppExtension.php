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
        );
    }


    public function paxLabel($status) {
        if ($this->container->getParameter($status . "_label")) {
            return ucfirst($this->container->getParameter($status . "_label"));
        } else {
            return ucfirst($status);
        }
    }

    public function getName()
    {
        return 'app_extension';
    }
}