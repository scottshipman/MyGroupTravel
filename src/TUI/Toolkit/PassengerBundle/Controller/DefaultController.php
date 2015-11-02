<?php

namespace TUI\Toolkit\PassengerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PassengerBundle:Default:index.html.twig', array('name' => $name));
    }
}
