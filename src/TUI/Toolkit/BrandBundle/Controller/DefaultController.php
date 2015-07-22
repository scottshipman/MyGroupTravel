<?php

namespace TUI\Toolkit\BrandBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{

    public function indexAction()
    {
      $em = $this->getDoctrine()->getManager();
      // Get the default/hardcoded Brand
      $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

      // look for a configured brand
      if($brand_id = $this->container->getParameter('brand_id')){
        $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
      }

      if(!$brand){
        $brand = $default_brand;
      }
      return $this->render('BrandBundle:Default:index.html.twig', array('brand' => $brand));
    }

    public function colorsAction()
    {
      $em = $this->getDoctrine()->getManager();
      // Get the default/hardcoded Brand
      $default_brand = $em->getRepository('BrandBundle:Brand')->findOneByName('ToolkitDefaultBrand');

      // look for a configured brand
      if($brand_id = $this->container->getParameter('brand_id')){
        $brand = $em->getRepository('BrandBundle:Brand')->find($brand_id);
      }

      if(!$brand){
        $brand = $default_brand;
      }
      return $this->render('BrandBundle:Default:colors.html.twig', array('brand' => $brand));
    }
}
