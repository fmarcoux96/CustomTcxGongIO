<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GongApiSettings extends Settings
{
    public bool $enable_crm_data;

    public string $access_key;

    public string $access_secret;

    public static function group(): string
    {
        return 'gong';
    }

    public static function encrypted(): array
    {
        return ['access_key', 'access_secret'];
    }
}
