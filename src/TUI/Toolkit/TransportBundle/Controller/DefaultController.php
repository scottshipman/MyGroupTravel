<?php

namespace TUI\Toolkit\TransportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TransportBundle:Default:index.html.twig', array('name' => $name));
    }
}
