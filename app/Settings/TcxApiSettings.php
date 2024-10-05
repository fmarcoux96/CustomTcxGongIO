<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TcxApiSettings extends Settings
{
    public bool $connected;

    public string $hostname;

    public int $port;

    public string $username;

    public string $password;

    public string $api_key;

    public array $status;

    public function valid(): bool
    {
        return !empty($this->hostname) && !empty($this->port) && !empty($this->username) && !empty($this->password);
    }

    public static function group(): string
    {
        return 'tcx';
    }

    public static function encrypted(): array
    {
        return ['username', 'password', 'api_key'];
    }
}
