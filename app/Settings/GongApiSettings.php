<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GongApiSettings extends Settings
{

    public static function group(): string
    {
        return 'default';
    }
}