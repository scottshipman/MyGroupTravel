<?php

namespace TUI\Toolkit\BoardBasisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BoardBasisBundle:Default:index.html.twig', array('name' => $name));
    }
}
