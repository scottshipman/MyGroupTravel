<?php

namespace TUI\Toolkit\QuoteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('QuoteBundle:Default:index.html.twig', array('name' => $name));
    }
}
