<?php

namespace TUI\Toolkit\ContentBlocksBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ContentBlocksBundle:Default:index.html.twig', array('name' => $name));
    }
}
