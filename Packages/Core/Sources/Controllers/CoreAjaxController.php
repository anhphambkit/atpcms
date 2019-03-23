<?php
namespace Packages\Core\Sources\Controllers;

use Illuminate\Routing\Controller;
use Packages\Core\Sources\Response\Response;

class CoreAjaxController extends Controller
{
    protected function removeNullParams(){
        $all = request()->all();
        foreach($all as $param => $value){
            if(empty($value)){
                unset(request()[$param]);
            }
        }

    }

    protected function response($data, $status = 0, $message = null){
        $httpCode = 200;
        if($status == Response::STATUS_SUCCESS){
            $message = 'Success';
        } elseif ($status == Response::STATUS_VALIDATION_ERROR){
            $message = 'Validation error';
            $httpCode = 433;
        }  elseif ($status == Response::STATUS_NOT_FOUND_ERROR){
            $message = 'Not found';
            $httpCode = 403;
        } elseif ($status == Response::STATUS_UNEXPECTED_ERROR){
            $message = 'Unexpected error';
            $httpCode = 500;
        }
        return response()->json([
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        ], $httpCode)->send();
    }
}

//->header('Cache-Control', 'no-cache, private')