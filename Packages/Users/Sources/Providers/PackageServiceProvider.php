<?php
/**
 * Created by PhpStorm.
 * User: AnhPham
 * Date: 2019-03-24
 * Time: 01:11
 */
namespace Packages\Users\Sources\Providers;

use Packages\Core\Sources\Providers\CoreServiceProvider;

class PackageServiceProvider extends CoreServiceProvider
{
    /**
     * Bootstrap any application services.
     * Register package sidebar.
     */
    public function boot(){
        $this->addBoot();
    }



    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->addRegister();
    }

    /**
     * Add more hook for custom package can implement and do more
     */
    protected function addBoot(){}
    protected function addRegister(){}
}