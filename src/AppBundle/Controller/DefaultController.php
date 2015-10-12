<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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

    public function ckeditorUploadAction(){
// http://www.paulfp.net/blog/2010/10/how-to-add-and-upload-an-image-using-ckeditor/
      $upload_dir = "static/uploads/media/ckeditor/";
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir);
      }

      $url = $upload_dir . time()."_".$_FILES['upload']['name'];

       //extensive suitability check before doing anything with the file…
          if (($_FILES['upload'] == "none") OR (empty($_FILES['upload']['name'])) )
          {
            $message = $this->get('translator')->trans('app.ckeditor-upload.empty-file');;
          }
          else if ($_FILES['upload']["size"] == 0)
          {
            $message = $this->get('translator')->trans('app.ckeditor-upload.zero-file');
          }
          else if (($_FILES['upload']["type"] != "image/gif") AND ($_FILES['upload']["type"] != "image/pjpeg") AND ($_FILES['upload']["type"] != "image/jpeg") AND ($_FILES['upload']["type"] != "image/png"))
          {
            $message = $this->get('translator')->trans('app.ckeditor-upload.wrong-file');
          }
          else if (!is_uploaded_file($_FILES['upload']["tmp_name"]))
          {
            $message = $this->get('translator')->trans('no-file');
          }
          else {
        $message = "";
            $move = @ move_uploaded_file($_FILES['upload']['tmp_name'], $url);
            if(!$move)
            {
              $message = $this->get('translator')->trans('app.ckeditor-upload.move-error');
            }
            $url = "/" . $url;
          }
      $funcNum = $_GET['CKEditorFuncNum'] ;

      $response = new Response("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>");
      return $response;
    }
}
