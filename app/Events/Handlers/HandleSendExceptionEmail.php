<?php
namespace App\Events\Handlers;
use Illuminate\Contracts\Mail\Mailer;
use App\Events\SendExceptionEmail;
use App\Mails\EmailAbstract;

class HandleSendExceptionEmail
{
	/**
     * @var Mailer
     */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
    	$this->mailer = $mailer;
    }

    /**
     * Description
     * @param SendExceptionEmail $event 
     * @return type
     */
    public function handle(SendExceptionEmail $event)
    {
        try {
            $mail = $this->mailer->to($event->args['to'], $event->args['name']);

            if(config('atp-cms-settings.emails.refs')){
                $mail->cc(explode(',', config('atp-cms-settings.emails.refs')));
            }
            $mail->send(new EmailAbstract($event->content, $event->title, [
                'attachments' => $event->attachments
            ]));

            \Log::info('Sent mail to ' . $event->args['to'] . ' successfully!');
        } catch (\Exception $ex) {
            \Log::error($ex->getMessage());
        }
    }
}