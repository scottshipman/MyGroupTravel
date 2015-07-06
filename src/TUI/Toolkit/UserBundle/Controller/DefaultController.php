<?php

namespace TUI\Toolkit\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Model\User;

class DefaultController extends Controller
{
 /*
 * Display user profile for passed in username
 */
  public function indexAction($id)
    {
      if(!empty($id)){
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->find('TUI\Toolkit\UserBundle\Entity\User', $id);

        return $this->render('TUIToolkitUserBundle:Default:index.html.twig', array('user' => $user));
      }

    }

/*
 * Get a list of all Users
 */
  public function listAction() {
    //access user manager services

    $userManager = $this->get('fos_user.user_manager');
    $users = $userManager->findUsers();

    return $this->render('TUIToolkitUserBundle:Default:list.html.twig', array('users' =>   $users));
  }
}
