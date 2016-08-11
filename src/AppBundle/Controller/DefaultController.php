<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

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


    public function getErrorMessages(Form $form) {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
        return $errors;
    }

    public function getNestedErrorMessages(Form $form){
        $formattedErrors = NULL;
        $errors = $form->getErrors(true);
        foreach($errors as $error){
            $cause = $error->getCause();
            if ($cause) {
                $path = $cause->getPropertyPath();
                $path = str_replace(']', '', $path);
                $path = str_replace('children[', '', $path);
                $path = explode('.', $path);


                // children[passengers].children[0].children[dateOfBirth]
                // children[passengers].children[0].children[fName].data
                // tui_toolkit_passengerbundle_tourpassenger_passengers_0_dateOfBirth
                //$path[0] = str_replace('children[', '', $path[0]);
                if(isset($path[1])) {
                    if(!isset($path[2])){
                        unset($path[1]);
                    } else {
                        $path[1] = str_replace('[', '', $path[1]);
                        unset($path[3]);
                    }

                    if (isset($path[1])) {
                        $path[1] = str_replace('data', '', $path[1]);
                    }

                    //$path[1] = str_replace('data', '', $path[1]);
                }
                $field = implode($path, '_');
                $formattedErrors[$field] = $error->getMessage();
            }

        }
        return $formattedErrors;
    }
}