<?php
/**
 * Created by PhpStorm.
 * User: minh.truong
 * Date: 3/27/18
 * Time: 4:37 PM
 */

namespace Packages\Core\Sources\Sidebar;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Sidebar;
use Packages\Core\Sources\Services\CoreServices;

class CoreSidebar implements Sidebar
{

    /**
     * @var Menu
     */
    protected $menu;

    /**
     * @param Menu $menu
     */
    public function __construct(Menu $menu) {
        $this->menu = $menu;
    }

    /**
     * Build your sidebar implementation here
     */
    public function build()
    {
        $coreServices = app()->make(CoreServices::class);
        $packages = $coreServices->listPackages();
        foreach($packages as $module){
            $sidebarExtender = 'Packages\\'. $module. '\\Sources\\Sidebar\\SidebarExtender';
            if(class_exists($sidebarExtender)){
                $extender = new $sidebarExtender();
                $this->menu->add($extender->extendWith($this->menu));
            }
        }
    }

    /**
     * @return Menu
     */
    public function getMenu() {
        $this->build();
        return $this->menu;
    }
}