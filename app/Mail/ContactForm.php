<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactForm extends Mailable
{
    use Queueable, SerializesModels;

    protected $_name;
    protected $_email;
    protected $_subject;
    protected $_description;
    protected $_images;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $subject, $description, $images = null)
    {
        $this->_name = $name;
        $this->_email = $email;
        $this->_subject = $subject;
        $this->_description = $description;
        $this->_images = $images;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->to('a.nechuhaev@gmail.com')
            ->replyTo($this->_email, $this->_name)
            ->subject($this->_subject);
        if ($this->_images) {
            foreach ($this->_images as $image) {
                $this->attach($image->getRealPath(), [
                    'as' => $image->getClientOriginalName(),
                    'mime' => $image->getMimeType()
                ]);
            }
        }
        return $this->view('mail.contact')->with([
            'name' => $this->_name,
            'description' => $this->_description,
            'email' => $this->_email,
        ]);
    }
}
