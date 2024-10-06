<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('gong.api_base_url', 'https://');
        $this->migrator->add('gong.auth_type', 'basic');
    }
};
