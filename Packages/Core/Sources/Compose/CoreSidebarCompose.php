<?php
/**
 * Created by PhpStorm.
 * User: minh.truong
 * Date: 3/27/18
 * Time: 4:52 PM
 */

namespace Packages\Core\Sources\Compose;

use Illuminate\View\View;
use Maatwebsite\Sidebar\Presentation\SidebarRenderer;
use Packages\Core\Sources\Sidebar\CoreSidebar;

class CoreSidebarCompose
{
    /**
     * @var CoreSidebar
     */
    protected $sidebar;

    /**
     * @var SidebarRenderer
     */
    protected $renderer;

    /**
     * @param CoreSidebar $sidebar
     * @param SidebarRenderer $renderer
     */
    public function __construct(CoreSidebar $sidebar, SidebarRenderer $renderer)
    {
        $this->sidebar  = $sidebar;
        $this->renderer = $renderer;
    }

    /**
     * @param $view
     */
    public function compose(View $view)
    {
        $view->with('sidebar', $this->renderer->render($this->sidebar));
    }

}