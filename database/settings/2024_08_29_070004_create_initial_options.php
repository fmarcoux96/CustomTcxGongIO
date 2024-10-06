<?php

use Illuminate\Support\Str;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('tcx.hostname', 'fqdn.3cx.us');
        $this->migrator->add('tcx.port', 5001);
        $this->migrator->addEncrypted('tcx.username', 'admin@example.com');
        $this->migrator->addEncrypted('tcx.password', 'password');
        $this->migrator->addEncrypted('tcx.api_key', Str::random(32));
        $this->migrator->add('tcx.connected', false);
        $this->migrator->add('tcx.status', []);

        $this->migrator->add('gong.enable_crm_data', false);
        $this->migrator->addEncrypted('gong.access_key', '');
        $this->migrator->addEncrypted('gong.access_secret', '');
    }
};
