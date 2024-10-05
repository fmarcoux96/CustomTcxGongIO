<?php

namespace App\Services;

use App\Models\LoggedCall;
use App\Settings\GongApiSettings;

class GongApiClient
{
    public function __construct(private readonly GongApiSettings $settings)
    {
    }

    public function test(): bool
    {
        $this->api()->get('/users')->throw();

        return true;
    }

    public function uploadCall(LoggedCall $call)
    {
        $agentId = $this->getUser($call->agent_email)['id'] ?? null;

        if ($this->settings->enable_crm_data) {
            $crmData = [
                [
                    'system' => 'Generic',
                    'objects' => [
                        [
                            'objectType' => match ($call->entity_type) {
                                'Leads' => 'Lead',
                                'Contacts' => 'Contact',
                                default => 'Account',
                            },
                            'objectId' => $call->entity_id,
                        ],
                    ],
                ],
            ];
        } else {
            $crmData = null;
        }

        $data = [
            'clientUniqueId' => $call->tcx_call_id,
            'primaryUser' => $agentId,
            'downloadMediaUrl' => $call->getTcxUrl(),
            'context' => [
                'system' => 'Generic',
            ],
            'direction' => ucfirst(strtolower($call->call_direction)),
            'duration' => $call->call_duration,
            'actualStart' => $call->call_start->toIso8601ZuluString(),
            'scheduledStart' => $call->call_start->toIso8601ZuluString(),
            'scheduledEnd' => $call->call_end->toIso8601ZuluString(),
            'parties' => [
                [
                    'phoneNumber' => $call->caller_number,
                    'context' => $crmData,
                ],
                [
                    'userId' => $agentId,
                ],
            ],
            'disposition' => 'Completed',
            'title' => $call->call_text,
            'purpose' => null,
        ];

        return $this->api()->post('/calls', $data)->throw()->json('callId');
    }

    public function getUser(string $email)
    {
        return cache()->remember('gong:users', now()->addHour(), function () use ($email) {
            $users = $this->api()->get('/users')->throw()->json();

            return collect($users['users'])->first(fn ($user) => $user['emailAddress'] === $email);
        });
    }

    private function api()
    {
        return \Http::timeout(30)
            ->retry(3, 100)
            ->withBasicAuth($this->settings->access_key, $this->settings->access_secret)
            ->baseUrl('https://api.gong.io/v2');
    }
}
