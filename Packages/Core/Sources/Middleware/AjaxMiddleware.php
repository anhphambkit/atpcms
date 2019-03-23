<?php

/**
 * Created by PhpStorm.
 * User: minh.truong
 * Date: 3/23/18
 * Time: 1:57 PM
 */
namespace Packages\Core\Sources\Middleware;

use Illuminate\Http\Request;

class AjaxMiddleware
{
    public function handle(Request $request, \Closure $next){
        return $next($request);
    }
}