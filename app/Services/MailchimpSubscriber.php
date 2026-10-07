<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Підписка email на розсилку MailChimp.
 * Ключ і ID списку беруться з .env (MAILCHIMP_API_KEY, MAILCHIMP_LIST_ID).
 */
class MailchimpSubscriber
{
    /**
     * @return int HTTP-код відповіді MailChimp (0 — якщо не налаштовано або помилка з'єднання)
     */
    public static function subscribe(string $email): int
    {
        $apiKey = config('services.mailchimp.key');
        $listId = config('services.mailchimp.list_id');

        if (!$apiKey || !$listId || strpos($apiKey, '-') === false) {
            Log::warning('MailChimp не налаштовано: задайте MAILCHIMP_API_KEY і MAILCHIMP_LIST_ID у .env');
            return 0;
        }

        $memberId = md5(strtolower($email));
        $dataCenter = substr($apiKey, strpos($apiKey, '-') + 1);
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

        $json = json_encode([
            'email_address' => $email,
            'status'        => 'subscribed',
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode;
    }
}
