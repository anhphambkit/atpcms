<?php
namespace Packages\Core\Sources\Compose;

use Maatwebsite\Sidebar\Presentation\SidebarRenderer;
use Packages\Core\Sources\Sidebar\CorePortalSidebar;

class CorePortalSidebarCompose
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
     * @param CorePortalSidebar $sidebar
     * @param SidebarRenderer $renderer
     */
    public function __construct(CorePortalSidebar $sidebar, SidebarRenderer $renderer)
    {
        $this->sidebar  = $sidebar;
        $this->renderer = $renderer;
    }

    /**
     * @param $view
     */
    public function create($view)
    {
        $view->sidebar = $this->renderer->render($this->sidebar);
    }
}