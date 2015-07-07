<?php

namespace TUI\Toolkit\InstitutionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('InstitutionBundle:Default:index.html.twig', array('name' => $name));
    }
}
