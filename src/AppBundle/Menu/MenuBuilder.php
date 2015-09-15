<?php
/**
 * Created by scottshipman
 * Date: 6/29/15
 */

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuBuilder
{
  private $factory;

  /**
   * @param FactoryInterface $factory
   */
  public function __construct(FactoryInterface $factory)
  {
    $this->factory = $factory;
  }

  public function createAdminMenu(RequestStack $requestStack)
  {
    $menu = $this->factory->createItem('root');

    $menu->addChild('Home', array('route' => '_homepage'));
    $menu->addChild('Quotes', array('route' => 'manage_quote'));
    $menu->addChild('Tours', array('route' => 'manage_tour'));
    $menu->addChild('Users', array('route' => 'user'));

    $menu->addChild('Reports')
          ->setAttribute('dropdown', true);
    $menu['Reports']->addChild('Modification Report', array('route' => '_tui_toolkit_reports_modification'));
    $menu['Reports']->addChild('Opportunity Report', array('route' => '_tui_toolkit_reports_opportunity'));


    $menu->addChild('Company')
          ->setAttribute('dropdown', true);
    $menu['Company']->addChild('Brands', array('route' => '_manage_brand'));
    $menu['Company']->addChild('Institutions', array('route' => 'manage_institution'));


    $menu->addChild('Tools')
      ->setAttribute('dropdown', true);
    //$menu['Tools']->addChild('Content Blocks', array('route' => 'manage_contentblocks'));
    $menu['Tools']->addChild('Currency', array('route' => 'manage_currency'));
    $menu['Tools']->addChild('Board Basis', array('route' => '_manage_boardbasis'));
    $menu['Tools']->addChild('Transport Type', array('route' => 'manage_transport'));
    $menu['Tools']->addChild('Layout Types', array('route' => 'manage_layouttype'));
    $menu['Tools']->addChild('Trip Status', array('route' => 'manage_tripstatus'));

    // ... add more children

    return $menu;
  }
}
