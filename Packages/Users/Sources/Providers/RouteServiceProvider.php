<?php

namespace Packages\Users\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Packages\Core\Services\CoreServices;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespaceGeneral = 'General';
    protected $namespaceAdmin = 'Admin';
    protected $namespaceClient = 'Client';

    private $defaultWebRouteFile = 'web.php';
    private $defaultApiRouteFile = 'api.php';
    private $defaultAjaxRouteFile = 'ajax.php';

    private $defaultWebControllerClass = 'Web';
    private $defaultApiControllerClass = 'Api';
    private $defaultAjaxControllerClass = 'Ajax';

    private $defaultWebMiddlewareClass = 'WebMiddleware';
    private $defaultApiMiddlewareClass = 'ApiMiddleware';
    private $defaultAjaxMiddlewareClass = 'AjaxMiddleware';


    /**
     * The filters base class name.
     *
     * @var array
     */
    protected $middleware = [

    ];


    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function map()
    {
        // Api route that always use with ACCESS TOKEN or API KEY to access the feature
        // Web route that use in Web UI, always need _nonce or _token for every request
        // Ajax route that use in Web UI, no need _nonce or _token but only use on this domain for every request
        $this->registerMiddleware($this->app['router']);
        $coreServices = app()->make(CoreServices::class);
        $packages = $coreServices->listPackages();
        foreach($packages as $module){
            $this->mapApiRoutes($module);
            $this->mapAjaxRoutes($module);
            $this->mapWebRoutes($module);
        }
    }

    /**
     * Register the filters.
     * @param Router $router
     */
    public function registerMiddleware(Router $router)
    {
        foreach ($this->middleware as $module => $middlewares) {
            foreach ($middlewares as $name => $middleware) {
                $class = "Packages\\{$module}\\Middleware\\{$middleware}";
                $router->aliasMiddleware($name, $class);
            }
        }
    }

    /**
     * Description
     * @param type $moduleName 
     * @return type
     */
    public function mapAdminRoutes($moduleName)
    {
        // $domain = 'admin.'.config("agoyu.domain-bigin");
        // $route = Route::domain($domain);
        $route = Route::prefix(config("agoyu.prefix-backend"));
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultWebMiddlewareClass, $this->defaultWebControllerClass, $this->defaultAdminRouteFile);
    }

    /**
     * Mapping Web route from each Package
     * @param $moduleName
     */
    protected function mapWebRoutes($moduleName)
    {
        $route = Route::prefix('');
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultWebMiddlewareClass, $this->defaultWebControllerClass, $this->defaultWebRouteFile);
    }

    /**
     * Mapping API route from each Package
     * API must have KEY to communicate with server, that key will be matched with API_KEY in .env
     *
     * @param $moduleName
     */
    protected function mapApiRoutes($moduleName)
    {
        $route = Route::prefix('api');
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultApiMiddlewareClass, $this->defaultApiControllerClass, $this->defaultApiRouteFile);
    }

    /**
     * Mapping Ajax route from each Package
     * AJAX must stay in the same domain request without KEY like API
     *
     * @param $moduleName
     */
    private function mapAjaxRoutes($moduleName)
    {
        $route = Route::prefix(config('eden.ajax_prefix_route'));
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultAjaxMiddlewareClass, $this->defaultAjaxControllerClass, $this->defaultAjaxRouteFile);
    }

    /**
     * Auto mapping all middleware and default controller class to Route
     * @param $route
     * @param $moduleName
     * @param $middlewareClass
     * @param $controllerNamespace
     * @param $defaultRouteFile
     * @throws \Exception when not found middleware or controller
     */
    private function mapMiddlewareAndNamespace(&$route, $moduleName, $middlewareClass, $controllerNamespace, $defaultRouteFile){
        $routeFile = base_path('Packages/'. $moduleName. '/Routes/'. $defaultRouteFile);
        $customRouteFile = base_path('Packages/'. $moduleName. '/Custom/Routes/'. $defaultRouteFile);

        if($defaultRouteFile == $this->defaultAdminRouteFile && (!file_exists($customRouteFile) || !file_exists($routeFile)))
            return;

        $route->middleware('Packages\\Core\\Custom\\Middleware\\'. $middlewareClass);
        /**
         * Register route with Custom Middleware (which extend from original middleware of package).
         * Middleware is required
         */
        $middleware = 'Packages\\' . $moduleName . '\\Custom\\Middleware\\' . $middlewareClass;
        if(!class_exists($middleware)){
            throw new \Exception('Middleware ['. $middlewareClass. '] is required in '. $middleware);
        }

        // Validate route file
        if(file_exists($routeFile)) {
            $controllerDirectory = 'Packages\\' . $moduleName . '\\Controllers\\' . $controllerNamespace;
        } else {
            throw new \Exception('Not found route '. $routeFile);
        }

        if(file_exists($customRouteFile)) {
            $customControllerDirectory = 'Packages\\' . $moduleName . '\\Custom\\Controllers\\' . $controllerNamespace;
            
        } else {
            throw new \Exception('Not found custom route '. $customRouteFile);
        }

        // Validate Controller Directory
        if(!is_dir(base_path('Packages/'. $moduleName. '/Controllers/'. $controllerNamespace))) {
            throw new \Exception('Not found controller directory '. $controllerDirectory);
        }

        if(!is_dir(base_path('Packages/'. $moduleName. '/Custom/Controllers/'. $controllerNamespace))) {
            throw new \Exception('Not found custom controller directory '. $customControllerDirectory);
        }

        // Merge middleware
        $middleware = [$middleware];
        if($defaultRouteFile === $this->defaultAjaxRouteFile){
            array_unshift($middleware, 'web', \Packages\Core\Custom\Middleware\AjaxMiddleware::class);
        } elseif($defaultRouteFile === $this->defaultWebRouteFile){
            // Merge group middleware web
            array_unshift($middleware, 'web', \Maatwebsite\Sidebar\Middleware\ResolveSidebars::class);
        } elseif($defaultRouteFile === $this->defaultApiRouteFile){
            array_unshift($middleware, 'api', \Packages\Core\Custom\Middleware\ApiMiddleware::class);
        }
        elseif($defaultRouteFile === $this->defaultAdminRouteFile){
            array_unshift($middleware, 'web');
        }

        if(!is_dir( str_replace('\\', '/', base_path($controllerDirectory)) )){
            throw new \Exception("Not found controller directory [". $controllerDirectory ."] in module ". $moduleName);
        }

        $route->middleware($middleware)->namespace($customControllerDirectory)->group($routeFile);
        $route->middleware($middleware)->namespace($customControllerDirectory)->group($customRouteFile);
    }

}
