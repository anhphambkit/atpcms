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

    protected $prefixWeb = '';
    protected $prefixApi = 'api';
    protected $prefixAjax = 'ajax';

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
            $this->registerRouteByNamespace($module, $this->namespaceAdmin, config('core.atp-cms-settings.prefix-backend'));
            $this->registerRouteByNamespace($module, $this->namespaceClient);
            $this->registerRouteByNamespace($module, $this->namespaceGeneral);
        }
    }

    /**
     * @param $module
     * @param $namespaceRoute
     * @param $prefix
     */
    public function registerRouteByNamespace($module, $namespaceRoute, $prefix = "") {
        $this->mapAjaxRoutes($module, $namespaceRoute, $prefix);
        $this->mapApiRoutes($module, $namespaceRoute, $prefix);
        $this->mapWebRoutes($module, $namespaceRoute, $prefix);
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
     * @param $prefixPortal
     * @param $prefixType
     * @return string
     */
    public function generatePrefixRoute($prefixPortal, $prefixType) {
        if (!empty($prefixPortal) && !empty($prefixType))
            $prefixRoute = "{$prefixPortal}/{$prefixType}";
        else if (!empty($prefixPortal))
            $prefixRoute = "{$prefixPortal}";
        else if (!empty($prefixType))
            $prefixRoute = "{$prefixPortal}";
        else
            $prefixRoute = "";
        return $prefixRoute;
    }

    /**
     * Mapping Web route from each Package
     * @param $moduleName
     * @param $namespace
     * @param string $prefix
     */
    protected function mapWebRoutes($moduleName, $namespace, $prefix = "")
    {
        $prefixRoute = $this->generatePrefixRoute($prefix, $this->prefixWeb);
        $route = Route::prefix("{$prefixRoute}");
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultWebMiddlewareClass, $namespace, $this->defaultWebControllerClass, $this->defaultWebRouteFile);
    }

    /**
     * Mapping API route from each Package
     * API must have KEY to communicate with server, that key will be matched with API_KEY in .env
     * @param $moduleName
     * @param $namespace
     * @param string $prefix
     */
    protected function mapApiRoutes($moduleName, $namespace, $prefix = "")
    {
        $prefixRoute = $this->generatePrefixRoute($prefix, $this->prefixApi);
        $route = Route::prefix("{$prefixRoute}");
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultApiMiddlewareClass, $namespace, $this->defaultApiControllerClass, $this->defaultApiRouteFile);
    }

    /**
     * Mapping Ajax route from each Package
     * AJAX must stay in the same domain request without KEY like API
     * @param $moduleName
     * @param $namespace
     * @param string $prefix
     */
    private function mapAjaxRoutes($moduleName, $namespace, $prefix = "")
    {
        $prefixRoute = $this->generatePrefixRoute($prefix, $this->prefixAjax);
        $route = Route::prefix("{$prefixRoute}");
        $this->mapMiddlewareAndNamespace($route, $moduleName, $this->defaultAjaxMiddlewareClass, $namespace, $this->defaultAjaxControllerClass, $this->defaultAjaxRouteFile);
    }

    /**
     * Auto mapping all middleware and default controller class to Route
     * @param $route
     * @param $moduleName
     * @param $middlewareClass
     * @param $namespaceRoute
     * @param $controllerNamespace
     * @param $defaultRouteFile
     */
    private function mapMiddlewareAndNamespace(&$route, $moduleName, $middlewareClass, $namespaceRoute, $controllerNamespace, $defaultRouteFile){
        $routeFile = base_path('Packages/'. $moduleName. '/Routes/'. $namespaceRoute . '/' . $defaultRouteFile);

        // Validate route file
        if(file_exists($routeFile)) {
            $this->registerRouteWithMiddleware($route, $moduleName, $middlewareClass, $namespaceRoute, $controllerNamespace, $defaultRouteFile, $routeFile);
        }

        unset($routeFile);
        unset($moduleName);
        unset($middlewareClass);
        unset($controllerNamespace);
        unset($defaultRouteFile);
        unset($namespaceRoute);
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
