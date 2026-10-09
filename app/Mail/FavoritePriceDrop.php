<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FavoritePriceDrop extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array Подешевші товари: name, url, image, seller, old, new, percent */
    public $drops;
    public $unsubscribeUrl;

    public function __construct(array $drops, string $unsubscribeUrl)
    {
        $this->drops = array_map(function ($d) {
            unset($d['favorite']);
            return $d;
        }, $drops);
        $this->unsubscribeUrl = $unsubscribeUrl;
    }

    public function build()
    {
        $count = count($this->drops);
        $subject = $count === 1
            ? 'Ціна знизилась: ' . mb_strimwidth($this->drops[0]['name'], 0, 70, '…')
            : "Знизилась ціна на товари з вашого обраного ({$count})";

        return $this->subject($subject)
            ->view('mail.favorite-price-drop');
    }
}
