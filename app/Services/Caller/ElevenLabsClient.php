<?php

namespace App\Services\Caller;

use App\Models\Caller\Lead;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ElevenLabsClient
{
    /** @var Client */
    private $http;

    public function __construct(Client $http = null)
    {
        $this->http = $http ?: new Client(['timeout' => 30]);
    }

    public static function isConfigured(): bool
    {
        $c = config('caller.elevenlabs');
        return !empty($c['api_key']) && !empty($c['agent_id']) && !empty($c['phone_number_id']);
    }

    /**
     * Вихідний дзвінок через SIP-транк: POST /v1/convai/sip-trunk/outbound-call.
     * Повертає ['success', 'message', 'conversation_id', 'sip_call_id'].
     */
    public function outboundCall(Lead $lead, int $ringingTimeout = 30): array
    {
        $c = config('caller.elevenlabs');
        try {
            $res = $this->http->post(rtrim($c['base_url'], '/') . '/v1/convai/sip-trunk/outbound-call', [
                'headers' => ['xi-api-key' => $c['api_key'], 'Content-Type' => 'application/json'],
                'json' => [
                    'agent_id' => $c['agent_id'],
                    'agent_phone_number_id' => $c['phone_number_id'],
                    'to_number' => $lead->phone,
                    'conversation_initiation_client_data' => [
                        'user_id' => $lead->phone,
                        'dynamic_variables' => [
                            'store_name' => (string) $lead->store_name,
                            'store_site' => (string) $lead->store_site,
                            'contact_name' => (string) $lead->contact_name,
                            'category' => (string) $lead->category,
                        ],
                    ],
                    'telephony_call_config' => ['ringing_timeout_secs' => $ringingTimeout],
                ],
            ]);
        } catch (RequestException $e) {
            $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();
            throw new \RuntimeException('ElevenLabs: ' . mb_substr($body, 0, 500));
        }

        $data = json_decode((string) $res->getBody(), true) ?: [];
        if (empty($data['success'])) {
            throw new \RuntimeException('ElevenLabs: ' . ($data['message'] ?? 'дзвінок не ініційовано'));
        }
        return $data;
    }
}
