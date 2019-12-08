<?php

namespace App\Mail;

use App\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdDetails extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->replyTo($data['email']);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data['ad_name'] = $this->data['ad_name'];

        $data['email'] = $this->data['email'];
        $data['name'] = $this->data['name'];
        $data['comment'] = $this->data['message'];




        return $this->view('mail.ad_details')->with($data);
    }
}
