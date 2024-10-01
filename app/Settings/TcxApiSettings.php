<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TcxApiSettings extends Settings
{

    public static function group(): string
    {
        return 'default';
    }
}