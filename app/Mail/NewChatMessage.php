<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Лист "Вам написали в чаті" — надсилається ChatController-ом з
 * захистом від спаму (не частіше ніж раз на 30 хв на діалог і не тоді,
 * коли одержувач саме зараз дивиться на цей діалог).
 *
 * Параметри — прості рядки, а не Eloquent-моделі, щоб лист не залежав
 * від того, чи існують ще ці записи на момент відправки.
 */
class NewChatMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $senderName;
    public $preview;
    public $conversationUrl;
    public $adTitle;

    public function __construct(string $senderName, string $preview, string $conversationUrl, ?string $adTitle = null)
    {
        $this->senderName = $senderName;
        $this->preview = $preview;
        $this->conversationUrl = $conversationUrl;
        $this->adTitle = $adTitle;
    }

    public function build()
    {
        return $this->subject('Нове повідомлення від ' . $this->senderName . ' — addnew.biz')
            ->view('mail.chat-new-message');
    }
}
