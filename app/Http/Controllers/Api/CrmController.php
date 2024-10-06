<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoggedCall;
use App\Services\GongApiClient;
use App\Services\TcxXapiClient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function __construct(
        private readonly GongApiClient $apiClient,
        private readonly TcxXapiClient $tcxClient
    ) {

    }

    public function logCall(Request $request)
    {
        $call = $this->requestToCall($request->all());

        $this->tcxClient->findRecordingForCall($call);

        $call->refresh();

        $id = $this->apiClient->uploadCall($call);

        return response()->json(['status' => 'success', 'call_id' => $id]);
    }

    private function requestToCall(array $data): LoggedCall
    {
        return LoggedCall::create([
            'agent_extension' => $data['agent_extension'] ?? null,
            'agent_email' => $data['agent_email'] ?? null,
            'agent_name' => $data['agent_name'] ?? null,
            'queue_extension' => $data['queue_extension'] ?? null,
            'caller_name' => $data['name'] ?? null,
            'caller_number' => $data['number'] ?? null,
            'call_text' => $data['body'] ?? null,
            'call_direction' => $data['call_direction'] ?? null,
            'call_duration' => $data['call_duration'] ?? null,
            'call_end' => Carbon::parse($data['call_end'], 'UTC'),
            'call_start' => Carbon::parse($data['call_start'], 'UTC'),
            'entity_id' => $data['entity_id'] ?? null,
            'entity_type' => $data['entity_type'] ?? null,
            'call_type' => $data['call_type'] ?? null,
            'call_status' => $data['call_status'] ?? null,
        ]);
    }
}
