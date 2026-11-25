<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Лист з інструкцією для магазину після дзвінка AI-асистентки.
 * Тексти — у resources/views/mail/caller-instruction*.blade.php.
 */
class CallerInstruction extends Mailable
{
    use Queueable, SerializesModels;

    public $contactName;
    public $storeName;
    public $hasMerchantFeed;
    public $replyToAddress;

    /** @var array */
    private $settings;

    public function __construct(string $contactName, string $storeName, string $hasMerchantFeed, array $settings)
    {
        $this->contactName = trim($contactName);
        $this->storeName = $storeName;
        $this->hasMerchantFeed = $hasMerchantFeed;
        $this->replyToAddress = (string) ($settings['email_reply_to'] ?? '');
        $this->settings = $settings;
    }

    public function build()
    {
        $mail = $this->subject("Addnew.biz: як безкоштовно додати товари «{$this->storeName}»")
            ->view('mail.caller-instruction')
            ->text('mail.caller-instruction-text');

        $fromAddress = $this->settings['email_from_address'] ?? '';
        if ($fromAddress !== '') {
            $mail->from($fromAddress, $this->settings['email_from_name'] ?: null);
        } elseif (!empty($this->settings['email_from_name'])) {
            $mail->from(config('mail.from.address'), $this->settings['email_from_name']);
        }
        if ($this->replyToAddress !== '') {
            $mail->replyTo($this->replyToAddress);
        }
        return $mail;
    }
}
