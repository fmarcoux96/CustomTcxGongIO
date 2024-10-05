<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoggedCall;
use App\Services\GongApiClient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function __construct(private readonly GongApiClient $apiClient)
    {

    }

    public function logCall(Request $request)
    {
        $call = $this->requestToCall($request->all());

        $id = $this->apiClient->uploadCall($call);

        return response()->json(['status' => 'success', 'call_id' => $id]);
    }

    private function requestToCall(array $data): LoggedCall
    {
        return LoggedCall::create([
            'agent_extension' => $data['agent_extension'],
            'agent_email' => $data['agent_email'],
            'agent_name' => $data['agent_name'],
            'queue_extension' => $data['queue_extension'],
            'caller_name' => $data['name'],
            'caller_number' => $data['number'],
            'call_text' => $data['call_text'],
            'call_direction' => $data['call_direction'],
            'call_duration' => $data['call_duration'],
            'call_end' => Carbon::parse($data['call_end'], 'UTC'),
            'call_start' => Carbon::parse($data['call_start'], 'UTC'),
            'entity_id' => $data['entity_id'],
            'entity_type' => $data['entity_type'],
            'call_type' => $data['call_type'],
            'call_status' => 'new',
        ]);
    }
}
