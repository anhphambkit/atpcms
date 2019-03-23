<?php
namespace Packages\Core\Sources\Facade;

use Illuminate\Support\Facades\Facade;

class UtilFacade extends Facade {

    protected static function getFacadeAccessor() {
        return 'UtilFacade';
    }
}