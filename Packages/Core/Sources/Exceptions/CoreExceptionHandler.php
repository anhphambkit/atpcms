<?php
namespace Packages\Core\Sources\Exceptions;

use Exception;
use App\Exceptions\EmailException;
use Packages\Core\Sources\Response\Response;

class CoreExceptionHandler extends Exception {
    
    /**
     * config content send exception throw email.
     * @var null|string 
     */
    protected $messageExceptionEmail = null;

    /**
     * config if you want send report exception through email.
     * @var bool
     */
    protected $sendReportEmail = false;

    /**
     * config if you want send report exception through email.
     * @var bool
     */
    protected $customReportEmail = true;

    /**
     * [response response json for exception]
     * @param  [type]  $data    [description]
     * @param  integer $status  [description]
     * @param  [type]  $message [description]
     * @return [type]           [description]
     */
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

    /**
     * Get value of variable $customReportEmail
     * @return bool
     */
    public function getCustomReportEmail()
    {
        return $this->customReportEmail;
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        if($this->sendReportEmail){
//            EmailException::send($this, $this->messageExceptionEmail);
        }
    }  
}