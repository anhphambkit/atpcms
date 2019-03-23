<?php
namespace Packages\Core\Sources\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;
class CoreServiceProvider extends ServiceProvider
{
	/**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * The event facades mappings for the application.
     *
     * @var array
     */
    protected $facade = [];

    /**
     * The components mappings for the application.
     *
     * @var array
     */
    protected $component = [];

    /**
     * Auto Register Events
     */
	protected function registerEvents()
	{
		foreach ($this->listens() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
	}

	/**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }

    /**
     * Auto Register Facades
     */
    protected function registerFacades()
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        foreach ($this->facades() as $facade) {
            foreach ($facade as $key => $value) {
                $instance = new $value();
                if($instance instanceof \Illuminate\Support\Facades\Facade)
                    /* binding alias */
                    $loader->alias($key,$value);
                else
                    /* binding facade */
                    $this->app->bind($key, $value);
            }
        }
    }

    /**
     * Get the facadés and alias.
     *
     * @return array
     */
    public function facades()
    {
        return $this->facade;
    }


    /**
     * Register alias components
     * @return type
     */
    protected function registerComponents()
    {
        foreach ($this->components() as $component => $alias)
        {
            Blade::component($component,$alias);
        }
            
    }

    /**
     * Get the components and alias.
     *
     * @return array
     */
    public function components()
    {
        return $this->component;
    }
}