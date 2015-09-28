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

    $menu->addChild('Home', array('route' => '_homepage'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu->addChild('Quotes', array('route' => 'manage_quote'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu->addChild('Tours', array('route' => 'manage_tour'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu->addChild('Users', array('route' => 'user'))
          ->setLinkAttribute('class', 'mdl-navigation__link');

    $menu->addChild('Reports')
          ->setAttribute('dropdown', true)
          ->setLabelAttribute('class', 'mdl-navigation__link');
    $menu['Reports']->addChild('Modification Report', array('route' => '_tui_toolkit_reports_modification'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu['Reports']->addChild('Opportunity Report', array('route' => '_tui_toolkit_reports_opportunity'))
          ->setLinkAttribute('class', 'mdl-navigation__link');


    $menu->addChild('Company')
          ->setAttribute('dropdown', true)
          ->setLabelAttribute('class', 'mdl-navigation__link');
    $menu['Company']->addChild('Brands', array('route' => '_manage_brand'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu['Company']->addChild('Institutions', array('route' => 'manage_institution'))
          ->setLinkAttribute('class', 'mdl-navigation__link');


    $menu->addChild('Tools')
          ->setAttribute('dropdown', true)
          ->setLabelAttribute('class', 'mdl-navigation__link');
    /* $menu['Tools']->addChild('Content Blocks', array('route' => 'manage_contentblocks'))
          ->setLinkAttribute('class', 'mdl-navigation__link'); */
    $menu['Tools']->addChild('Currency', array('route' => 'manage_currency'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu['Tools']->addChild('Board Basis', array('route' => '_manage_boardbasis'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu['Tools']->addChild('Transport Type', array('route' => 'manage_transport'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu['Tools']->addChild('Layout Types', array('route' => 'manage_layouttype'))
          ->setLinkAttribute('class', 'mdl-navigation__link');
    $menu['Tools']->addChild('Trip Status', array('route' => 'manage_tripstatus'))
          ->setLinkAttribute('class', 'mdl-navigation__link');

    // ... add more children

    return $menu;
  }
}
