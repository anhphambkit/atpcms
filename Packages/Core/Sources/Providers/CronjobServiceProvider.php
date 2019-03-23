<?php

namespace Packages\Core\Sources\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Packages\Core\Sources\Commands\CleanProject;

class CronjobServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(){
        $this->app->booted(function (){
            $schedule = $this->app->make(Schedule::class);
            $schedule->command(CleanProject::class)->weekly()->fridays()->at('07:00');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
