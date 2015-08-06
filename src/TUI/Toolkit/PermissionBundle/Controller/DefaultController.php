<?php

namespace TUI\Toolkit\PermissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PermissionBundle:Default:index.html.twig', array('name' => $name));
    }
}
