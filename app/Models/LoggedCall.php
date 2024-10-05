<?php

namespace App\Models;

use App\Services\TcxXapiClient;
use App\Settings\TcxApiSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class LoggedCall extends Model
{
    use HasTimestamps, Prunable;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'call_start',
        'call_end',
        'call_status',
        'call_direction',
        'call_type',
        'call_duration',
        'call_text',
        'caller_name',
        'caller_number',
        'agent_extension',
        'agent_name',
        'agent_email',
        'queue_extension',
        'tcx_call_id',
        'tcx_recording_id',
        'tcx_recording_filename',
        'zoho_call_id',
        'gong_call_id',
    ];

    public function prunable(): Builder
    {
        return static::query()
            ->where('created_at', '<=', now()->subMonth());
    }

    public function getTcxUrl()
    {
        $settings = app(TcxApiSettings::class);
        $apiClient = app(TcxXapiClient::class);

        $token = $apiClient->accessToken();

        return "https://{$settings->hostname}:{$settings->port}/xapi/v1/Recordings/Pbx.DownloadRecording(recId={$this->tcx_recording_id})?access_token={$token}";
    }
}
