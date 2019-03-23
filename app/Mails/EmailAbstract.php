<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailAbstract extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var array
     */
    public $data;

    /**
     * Create a new message instance.
     *
     * @param $content
     * @param $subject
     * @param array $data
     * @author TrinhLe
     */
    public function __construct($content, $subject, $data = [])
    {
        $this->content = $content;
        $this->subject = $subject;
        $this->data    = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     * @author TrinhLe
     */
    public function build()
    {
        $email = $this->from(config('atp-cms-settings.emails.system'))
            ->subject($this->subject)
            ->view('emails.email')
            ->with(['content' => $this->content]);

        $attachments = array_get($this->data, 'attachments');
        if (!empty($attachments)) {
            if (!is_array($attachments)) {
                $attachments = [$attachments];
            }
            foreach($attachments as $file) {
                $email->attach($file['path'], $file['options']);
            }
        }

        return $email;
    }
}
