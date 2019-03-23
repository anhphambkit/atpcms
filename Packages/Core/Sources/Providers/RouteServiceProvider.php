<?php

namespace Packages\Core\Sources\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Packages\Core\Sources\Services\CoreServices;

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
     *
     * @param  Router $router
     * @return void
     */
    public function registerMiddleware(Router $router)
    {
        foreach ($this->middleware as $module => $middlewares) {
            foreach ($middlewares as $name => $middleware) {
                $class = "Packages\\{$module}\\Sources\\Middleware\\{$middleware}";
                $router->aliasMiddleware($name, $class);
            }
        }
    }

    /**
     * Mapping Web route from each Package
     * @param $moduleName
     * @throws \Exception
     */
    protected function mapWebRoutes($moduleName)
    {
        $route = Route::prefix('');
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultWebMiddlewareClass, $this->defaultWebControllerClass, $this->defaultWebRouteFile);
    }

    /**
     * Mapping API route from each Package
     * API must have KEY to communicate with server, that key will be matched with API_KEY in .env
     * @param $moduleName
     * @throws \Exception
     */
    protected function mapApiRoutes($moduleName)
    {
        $route = Route::prefix('api');
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultApiMiddlewareClass, $this->defaultApiControllerClass, $this->defaultApiRouteFile);
    }

    /**
     * Mapping Ajax route from each Package
     * AJAX must stay in the same domain request without KEY like API
     * @param $moduleName
     * @throws \Exception
     */
    private function mapAjaxRoutes($moduleName)
    {
        $route = Route::prefix(config('atp-cms-settings.ajax_prefix_route'));
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
        $generalRouteFile = base_path('Packages/'. $moduleName. '/Routes/'. $this->namespaceGeneral . '/' . $defaultRouteFile);
        $adminRouteFile = base_path('Packages/'. $moduleName. '/Routes/'. $this->namespaceAdmin . '/' . $defaultRouteFile);
        $clientRouteFile = base_path('Packages/'. $moduleName. '/Routes/'. $this->namespaceClient . '/' . $defaultRouteFile);

        // Validate general route file
        if(file_exists($generalRouteFile)) {
            $this->registerRouteWithMiddleware($route, $moduleName, $middlewareClass, $this->namespaceGeneral, $controllerNamespace, $defaultRouteFile, $generalRouteFile);
        }

        // Validate admin route file
        if(file_exists($adminRouteFile)) {
            $this->registerRouteWithMiddleware($route, $moduleName, $middlewareClass, $this->namespaceAdmin, $controllerNamespace, $defaultRouteFile, $adminRouteFile);
        }

        // Validate client route file
        if(file_exists($clientRouteFile)) {
            $this->registerRouteWithMiddleware($route, $moduleName, $middlewareClass, $this->namespaceClient, $controllerNamespace, $defaultRouteFile, $clientRouteFile);
        }

        unset($generalRouteFile);
        unset($adminRouteFile);
        unset($clientRouteFile);
        unset($moduleName);
        unset($middlewareClass);
        unset($controllerNamespace);
        unset($defaultRouteFile);
    }

    /**
     * @param $defaultRouteFile
     * @param array $middleware
     * @return array
     */
    private function mergeMiddleware($defaultRouteFile, $middleware = []) {
        // Merge middleware
        if($defaultRouteFile === $this->defaultAjaxRouteFile){
            array_unshift($middleware, 'web', \Packages\Core\Sources\Middleware\AjaxMiddleware::class);
        } elseif($defaultRouteFile === $this->defaultWebRouteFile){
            // Merge group middleware web
            array_unshift($middleware, 'web', \Maatwebsite\Sidebar\Middleware\ResolveSidebars::class);
        } elseif($defaultRouteFile === $this->defaultApiRouteFile){
            array_unshift($middleware, 'api', \Packages\Core\Sources\Middleware\ApiMiddleware::class);
        }
        unset($defaultRouteFile);
        return $middleware;
    }

    /**
     * @param $controllerDirectory
     * @param $moduleName
     * @throws \Exception
     */
    private function checkControllerDirectory($controllerDirectory, $moduleName) {
        if(!is_dir( str_replace('\\', '/', base_path($controllerDirectory)) )){
            throw new \Exception("Not found controller directory [". $controllerDirectory ."] in module ". $moduleName);
        }
    }

    private function registerRouteWithMiddleware(&$route, $moduleName, $middlewareClass, $namespace, $controllerNamespace, $defaultRouteFile, $routeFile) {
        $middleware = 'Packages\\' . $moduleName . '\\Sources\\Middleware\\' . $namespace . '/' .  $middlewareClass;

        $middlewareArray = [];
        if(class_exists($middleware)){
            $middlewareArray = [$middleware];
        }

        $middlewareRegister = $this->mergeMiddleware($defaultRouteFile, $middlewareArray);

        $controllerDirectory = 'Packages\\' . $moduleName . '\\Sources\\Controllers\\' . $namespace . '/' . $controllerNamespace;

        $this->checkControllerDirectory($controllerDirectory, $moduleName);

        $route->middleware($middlewareRegister)
            ->namespace($controllerDirectory)
            ->group($routeFile);

        unset($middleware);
        unset($middlewareArray);
        unset($middlewareRegister);
        unset($controllerDirectory);
        unset($namespace);
        unset($routeFile);
        unset($moduleName);
        unset($middlewareClass);
        unset($controllerNamespace);
        unset($defaultRouteFile);
    }
}
