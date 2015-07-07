<?php

namespace TUI\Toolkit\TripStatusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TripStatusBundle:Default:index.html.twig', array('name' => $name));
    }
}
