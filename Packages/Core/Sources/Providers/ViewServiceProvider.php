<?php
/**
 * Created by PhpStorm.
 * User: minh.truong
 * Date: 3/27/18
 * Time: 3:29 PM
 */

namespace Packages\Core\Sources\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Sidebar\SidebarManager;
use Packages\Core\Sources\Compose\CoreSidebarCompose;
use Packages\Core\Sources\Compose\CorePortalSidebarCompose;
use Packages\Core\Sources\Services\CoreServices;
use Packages\Core\Sources\Sidebar\CoreSidebar;
use Packages\Core\Sources\Sidebar\CorePortalSidebar;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        // Map view with module name. Like user::welcome => User/Resources/views/welcome.blade.php
        // Map sidebar each module to sidebar Admin console
        $this->mapViews();
        $this->mapSidebars();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
    }

    /**
     * Mapping view namespace each Package
     */
    private function mapViews(){
        $coreServices = app()->make(CoreServices::class);
        $packages = $coreServices->listPackages();
        foreach($packages as $module){
            $this->loadViewsFrom($coreServices->packagePath($module). '/Resources/views', mb_strtolower($module));
        }
    }

    /**
     * Mapping sidebar each package to sidebar Admin console
     */
    private function mapSidebars(){
        
        /* register menu package theme*/
        $manager = app()->make(SidebarManager::class);
        $manager->register(CoreSidebar::class);
        View::composer(
            'theme::layouts.sidebar', CoreSidebarCompose::class
        );
    }
}