<?php
namespace App\Events;
use Illuminate\Queue\SerializesModels;

class SendExceptionEmail
{
	use SerializesModels;

	/**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $title;

    /**
     * @var array
     */
    public $args;

	/**
	 * @var array
	 */
	public $attachments;

	/**
     * SendMailEvent constructor.
     * @param $content
     * @param $title
     * @param $args
     * @author TrinhLe
     */
    public function __construct($content, $title, $args, array $attachments = [])
    {
		$this->content     = $content;
		$this->title       = $title;
		$this->args        = $args;
		$this->attachments = $attachments;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     * @author TrinhLe
     */
    public function broadcastOn()
    {
        return [];
    }
}
