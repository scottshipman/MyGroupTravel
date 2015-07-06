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
    $menu->addChild('Quotes', array('route' => '_manage_quote'));
    $menu->addChild('Tours', array('route' => '_manage_tour_home'));
    $menu->addChild('Users', array('route' => '_tui_toolkit_user_list'));

    $menu->addChild('Reports', array('route' => '_tui_toolkit_reports'))
          ->setAttribute('dropdown', true);
    $menu['Reports']->addChild('Modification Report', array('route' => '_tui_toolkit_reports_modification'));
    $menu['Reports']->addChild('Opportunity Report', array('route' => '_tui_toolkit_reports_opportunity'));


    $menu->addChild('Company', array('route' => '_tui_toolkit_manage_company'))
          ->setAttribute('dropdown', true);
    // Dont think Recipients is necessary anymore
    // $menu['Company']->addChild('Recipients', array('route' => '_tui_toolkit_manage_company_recipients'));
    // Dont think Division is necessary anymore
    // $menu['Company']->addChild('Divisions', array('route' => '_tui_toolkit_manage_company_divisions'));
    $menu['Company']->addChild('Brands', array('route' => '_manage_brand'));
    $menu['Company']->addChild('Board Basis', array('route' => '_manage_boardbasis'));
    $menu['Company']->addChild('Transport Type', array('route' => '_tui_toolkit_manage_company_transport'));
    $menu['Company']->addChild('Currency', array('route' => '_tui_toolkit_manage_company_currency'));
    // Dont think Opportunities is necessary anymore
    // $menu['Company']->addChild('Opportunities', array('route' => '_tui_toolkit_manage_company_opportunity'));
    $menu['Company']->addChild('Institutions', array('route' => '_tui_toolkit_manage_company_institution'));


    $menu->addChild('Tools', array('route' => '_tui_toolkit_tools'));

    // ... add more children

    return $menu;
  }
}
