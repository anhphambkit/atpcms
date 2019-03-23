<?php

namespace Packages\Core\Sources\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Packages\Core\Sources\Services\CoreRoleServices;
use Packages\Core\Sources\Services\CoreServices;
use Packages\Core\Sources\Services\Eloquent\EloquentCoreRoleServices;
use Packages\Core\Sources\Services\Eloquent\EloquentCoreServices;
use Packages\Core\Sources\Services\Eloquent\EloquentUtilServices;
use Packages\Core\Sources\Services\UtilServices;

use Packages\Core\Sources\Foundation\Asset\Manager\AssetManager;
use Packages\Core\Sources\Foundation\Asset\Pipeline\AssetPipeline;
use Packages\Core\Sources\Foundation\Asset\Manager\BiginAssetManager;
use Packages\Core\Sources\Foundation\Asset\Pipeline\BiginAssetPipeline;

class AppServiceProvider extends ServiceProvider
{
    private $coreServices;
    /**
     * AppServiceProvider constructor.
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(){
        if(env('FORCE_HTTPS') && !app()->runningInConsole() && !app()->runningUnitTests()){
            URL::forceSchema('https');
        }
        $this->bindAssetClasses();
        $this->bindClass();
        $this->mapProviders();
//        $this->mapConfigs();
        $this->loadCommands();
        $this->loadTranslation();
        $this->registerPublish();
    }

    /**
     * Register publish
     */
    protected function registerPublish(){
        $this->publishes([ base_path('Packages/Core/Database/Migrations') => database_path('migrations')], 'core-migrate');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CoreServices::class, EloquentCoreServices::class);
        $this->coreServices = $this->app->make(CoreServices::class);
        $this->app->singleton(CoreRoleServices::class, EloquentCoreRoleServices::class);
        $this->app->singleton(UtilServices::class, EloquentUtilServices::class);

        // Bind for Facade
        $this->app->singleton('UtilFacade', EloquentUtilServices::class);
    }

    /**
     * Register all core providers and module providers
     */
    private function mapProviders(){
        $this->app->register(ViewServiceProvider::class);
        $this->app->register('Maatwebsite\Sidebar\SidebarServiceProvider');
        $this->app->register('Packages\\Core\\Sources\\Providers\\CronjobServiceProvider');

        if(env('APP_DEBUG')){
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }

        $packages = $this->coreServices->listPackages();
        foreach($packages as $module){
            $this->app->register('Packages\\'. $module. '\\Sources\\Providers\\PackageServiceProvider');
        }
    }

    /**
     * Register all translation each module
     */
    private function loadTranslation(){
        $packages = $this->coreServices->listPackages();
        foreach($packages as $module){
            $this->loadJsonTranslationsFrom($this->coreServices->packagePath($module).'/Resources/lang', mb_strtolower($module));
        }
    }

    /**
     * Register all config each module
     */
    private function mapConfigs(){
        $packages = $this->coreServices->listPackages();
        foreach($packages as $module){
            $configPath = $this->coreServices->packagePath($module). '/Config/'. mb_strtolower($module). '.php';
            if(file_exists($configPath)){
                $this->mergeConfigFrom($configPath, mb_strtolower($module));
            }
        }
    }

    /**
     * Binding default Service and Repository class
     */
    private function bindClass(){
        $packages = $this->coreServices->listPackages(true);
        foreach($packages as $module){
            $packageServicesInterface = 'Packages\\'. ucwords($module). '\\Sources\\Services\\'. ucwords($module). 'Services';
            $packageServicesImplement = 'Packages\\'. ucwords($module). '\\Sources\\Services\\Execute\\Execute'. ucwords($module). 'Services';
            if(interface_exists($packageServicesInterface) && class_exists($packageServicesImplement)){
                $this->app->singleton($packageServicesInterface, $packageServicesImplement);
            }

            $packageRepositoriesInterface = 'Packages\\'. ucwords($module). '\\Sources\\Repositories\\'. ucwords($module). 'Repositories';
            $packageRepositoriesImplement = 'Packages\\'. ucwords($module). '\\Sources\\Repositories\\Eloquent\\Eloquent'. ucwords($module). 'Repositories';
            if(interface_exists($packageRepositoriesInterface) && class_exists($packageRepositoriesImplement)){
                $this->app->singleton($packageRepositoriesInterface, $packageRepositoriesImplement);
            }
        }
    }

    /**
     * Load all commands each packages
     */
    private function loadCommands(){
        $packages = $this->coreServices->listPackages(false);
        $commands = collect();
        foreach($packages as $package){
            $customClasses = collect(); // it will be like: "Publish", "CleanProject",...

            if (file_exists($dir=base_path('Packages/'.ucwords($package).'/Sources/Commands')) ){
                $pkgCmd = collect(scandir($dir))->filter(function($file) use ($package, $customClasses){
                    $withoutExtension = substr($file, 0, -4);
                    $extension = substr($file, -4);
                    return $extension === '.php' && !$customClasses->contains($withoutExtension);
                })->map(function($file) use($package) {
                    return  'Packages\\'.ucwords($package).'\\Sources\\Commands\\'.substr($file, 0, -4);
                });
                $commands = $commands->merge($pkgCmd);
            }
        }
        $this->commands($commands->toArray());
    }

    /**
     * Bind classes related to assets
     */
    private function bindAssetClasses()
    {
        $this->app->singleton(AssetManager::class, function () {
            return new BiginAssetManager();
        });

        $this->app->singleton(AssetPipeline::class, function ($app) {
            return new BiginAssetPipeline($app[AssetManager::class]);
        });
    }

}
