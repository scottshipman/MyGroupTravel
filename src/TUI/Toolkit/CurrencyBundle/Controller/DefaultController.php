<?php

namespace TUI\Toolkit\CurrencyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CurrencyBundle:Default:index.html.twig', array('name' => $name));
    }
}
