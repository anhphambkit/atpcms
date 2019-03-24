<?php

namespace Packages\Core\Sources\Providers;

use Illuminate\Support\ServiceProvider;
use Packages\Core\Sources\Compose\AssetsViewComposer;
use Packages\Core\Sources\Compose\UserViewComposer;
class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->registerAllThemes();
        $this->setActiveTheme();
    }

    /**
     * Set the active theme based on the settings
     */
    private function setActiveTheme()
    {
        if ($this->app->runningInConsole()) {
            return;
        }
        
        if ($this->inAdministration()) {
            $themeName = config('atp-cms-settings.admin-theme');
            $this->app['stylist']->activate($themeName, true);
            return $this->bindingAssets();
        }
    }

    /**
     * Check if we are in the administration
     * @param int $segment
     * @return bool
     */
    private function inAdministration(int $segment = 1)
    {
        return $this->app['request']->segment($segment) === config('core.atp-cms-settings.prefix-backend');
    }

    /**
     * Register all themes with activating them
     */
    private function registerAllThemes()
    {
        $directories = $this->app['files']->directories(config('atp-cms-settings.themes.paths', [base_path('/Themes')])[0]);
        foreach ($directories as $directory) {
            $this->app['stylist']->registerPath($directory);
        }
    }

    private function bindingAssets()
    {
        view()->composer('layouts.master', AssetsViewComposer::class);
        view()->composer('layouts.master', UserViewComposer::class);
    }
}
