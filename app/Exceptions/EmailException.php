<?php

namespace App\Exceptions;
use Exception;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\UploadedFile;
use App\Events\SendExceptionEmail;

class EmailException
{
    /**
     * @param $content
     * @param $title
     * @param $args
     * @param array $attachments
     * @throws Exception
     */
    public function send($content, $title, $args, array $attachments = [])
    {
        try {
            event(new SendExceptionEmail($content, $title, $args, $attachments));
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Setup params to send mail.
     * @param Exception $exception 
     * @param string|null $title 
     * @return array
     */
    protected function buildReportEmail(Exception $exception) : array {

        $results     = array();
        $attachments = array();
        $ex = FlattenException::create($exception);
        $handler = new SymfonyExceptionHandler();

        $this->detachRequest(request()->all(), $results, $attachments);

        $params = [
            'message'     => $exception->getMessage(),
            'line'        => $exception->getLine(),
            'file'        => $exception->getFile(),
            'user'        => auth()->user()->email ?? '',
            'requests'    => $results,
            'attachments' => $attachments,
            'content'     => $handler->getContent($ex), 
            'url'         => URL::full()
        ];

        if(!empty($title))
            $params['title'] = $title;

        return $params;
    }


    /**
     * Detach request params
     * @param type $request
     * @return mixed
     */
    protected function detachRequest($request, &$results = [], &$attachments = [], $key = '')
    {
        if(is_array($request))
        {
            foreach ($request as $keyRequest => $params) 
            {
                $index = implode("-",[
                    $key, $keyRequest
                ]);
                $this->detachRequest($params, $results, $attachments, $index);
            }
        }else
        {
            if(is_string($request))
                $results[$key] = $request;

            if($request instanceof UploadedFile)
            {
                $attachments[] = [
                    'path' => $request->getRealPath(),
                    'options' => [
                        'as' => $request->getClientOriginalName(),
                        'mime' => $request->getClientMimeType(),
                    ]
                ];
            }
        }
    }

    /**
     * Sends an email to the developer about the exception.
     * @param Exception $exception
     * @param string $title
     * @throws \Throwable
     */
    public function sendErrorException(Exception $exception, string $title = '')
    {
        try {
            $datas = $this->buildReportEmail($exception);
            EmailException::send(view('emails.error-reporting', compact('datas'))->render(), __('Error Report'),
                [
                    'to'   => config('atp-cms-settings.emails.admin'),
                    'name' => 'Error Exception',
                ],
                $datas['attachments']
            );
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}