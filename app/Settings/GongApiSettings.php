<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GongApiSettings extends Settings
{
    public bool $enable_crm_data;

    public string $api_base_url;

    public string $access_key;

    public string $access_secret;

    public string $auth_type;

    public ?string $fallback_user_id;

    public function valid(): bool
    {
        try {
            $api = new \App\Services\GongApiClient($this);

            return $api->test();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function group(): string
    {
        return 'gong';
    }

    public static function encrypted(): array
    {
        return ['access_key', 'access_secret'];
    }
}
