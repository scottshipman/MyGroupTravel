<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="_homepage")
     */
    public function indexAction()
    {
      // if not authenticated, load only the login page
      $securityContext = $this->container->get('security.authorization_checker');
      // NOTE security.context is depricated, use security.authorzation_checker or security.token.storage instead
      if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        // authenticated REMEMBERED, FULLY will imply REMEMBERED (NON anonymous)

        // get User ID
        $user = $this->get('security.context')->getToken()->getUser();
        $userId = $user->getId();
        // check ROLE for Brand vs Customer

        if ($securityContext->isGranted(
            'ROLE_BRAND'
          )) {
          // user is Brand or higher, redirect to Brand default Dashboard
          //return $this->redirect($this->generateUrl('user_show', array('id'=>$userId)));
          return $this->redirect($this->generateUrl('fos_user_profile_show'));

        } else {
          // user is Customer (organizer, assistant, parent, passenger etc), redirect to Customer Dashboard
          //return $this->redirect($this->generateUrl('user_show', array('id'=>$userId)));
          return $this->redirect($this->generateUrl('fos_user_profile_show'));
        }
      }

    //  user is anonymous, show login form only
      return $this->render('default/index.html.twig');
    }
}
