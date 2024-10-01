<?php

namespace App\Services;

use App\Models\LoggedCall;
use App\Models\TcxInstance;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;

/**
 *
 */
readonly class TcxXapiClient implements TcxApiClientInterface
{
    public function __construct(private TcxInstance $instance)
    {

    }

    /**
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function status(): array
    {
        return $this->api()->get('/SystemStatus')->throw()->json();
    }

    public function testConnection(): bool
    {
        try {
            $this->status();

            return true;
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    public function version(): string
    {
        return $this->status()['Version'];
    }

    /**
     * @param string $dateTime
     * @return int|null
     */
    public function findRecordingForCallByDate(string $dateTime): ?int
    {
        $date = Carbon::parse($dateTime);

        $start = (clone $date)->setTime(0,0,0);
        $end = (clone $date)->setTime(23,59,59);

        $logs = $this->getCallLogs($start, $end);

        $entry = collect($logs)->where('StartTime', $date->toIso8601ZuluString())->first();
        if (!$entry) {
            return null;
        }

        $callEntry = $this->searchCallLog($logs, $entry['CallId']);
        if (!$callEntry) {
            return null;
        }

        return $callEntry['DstRecId'] ?? $callEntry['SrcRecId'] ?? null; // RecordingUrl or DstRecId
    }

    /**
     * @param LoggedCall $call
     * @return int|null
     */
    public function findRecordingForCall(LoggedCall $call): ?int
    {
        $logs = $this->getCallLogs($call->call_start, $call->call_end);

        $start = $call->call_start->clone()->setSeconds($call->call_start->second - 5);
        $end = $call->call_start->clone()->setSeconds($call->call_start->second + 5);

        $entry = null;
        foreach ($logs as $log) {
            if (Carbon::parse($log['StartTime'], 'UTC')->between($start, $end)
                && (str_ends_with($log['DestinationCallerId'], $call->caller_number) || str_ends_with($log['SourceCallerId'], $call->caller_number))
            ) {
                $entry = $log;
                break;
            }
        }
        if (!$entry) {
            return null;
        }

        $call->update(['tcx_call_id' => $entry['CallId']]);

        $callEntry = $this->searchCallLog($logs, $entry['CallId']);
        if (!$callEntry) {
            return null;
        }

        return $callEntry['DstRecId'] ?? $callEntry['SrcRecId'] ?? null;
    }

    /**
     * @param int $recordingId
     * @return string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function downloadRecordingForCall(int $recordingId, string $filename = null): string
    {
        $file = storage_path('app/tmp/'.$filename);

        $this->api()
            ->sink($file)
            ->get("/Recordings/Pbx.DownloadRecording(recId={$recordingId})")
            ->throw();

        return $file;
    }

    /**
     * @param Carbon $start
     * @param Carbon|null $end
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getCallLogs(Carbon $start, ?Carbon $end = null): array
    {
        $start = $start->setTime(0,0,0);
        $end = $end ? $end->setTime(23,59,59) : $start->setTime(23,59,59);

        $query = [
            'periodFrom='.$start->toIso8601ZuluString(),
            'periodTo='.$end->toIso8601ZuluString(),
            'sourceType=0',
            'sourceFilter=\'\'',
            'destinationType=0',
            'destinationFilter=\'\'',
            'callsType=0',
            'callTimeFilterType=0',
            'callTimeFilterFrom=\'0:00:0\'',
            'callTimeFilterTo=\'0:00:0\'',
            'hidePcalls=true',
        ];

        $params = implode(',', $query);

        return $this->api()->get('/ReportCallLogData/Pbx.GetCallLogData('.$params.')?$top=100&$skip=0&$orderby=SegmentId desc')->throw()->json('value');
    }

    /**
     * @param array $logs
     * @param int $callId
     * @return array|null
     */
    private function searchCallLog(array $logs, int $callId): ?array
    {
        $entries = collect($logs);

        $allCallEntries = $entries->where('CallId', $callId);

        if ($allCallEntries->count() > 1) {
            $allCallEntries = $allCallEntries
                ->sortByDesc('SegmentId')
                ->values()
                ->all();
        }

        foreach ($allCallEntries as $callEntry) {
            if (!empty($callEntry['DstRecId']) || !empty($callEntry['SrcRecId'])) {
                return $callEntry;
            }
        }

        return null;
    }

    public function getDidNumbers()
    {
        return cache()->remember("tcx:did:{$this->instance->id}", now()->addHour(), function () {
            return $this->api()->get('/DidNumbers?$select=Number,RoutingRule&$expand=RoutingRule($select=RuleName)&$orderby=Number&$top=100')->throw()->json('value');
        });
    }

    public function getGroups()
    {
        return cache()->remember("tcx:groups:{$this->instance->id}", now()->addHour(), function () {
            return $this->api()->get('/Groups?$filter=not startsWith(Name, \'___FAVORITES___\')&$orderBy=Name&$select=Id,Name')->throw()->json('value');
        });
    }

    public function getGroup(int|string $id)
    {
        return cache()->remember("tcx:group:{$this->instance->id}:{$id}", now()->addHour(), function () use ($id) {
            return $this->api()->get("/Groups({$id})?\$select=Id,Name")->throw()->json();
        });
    }

    public function getAgentGroupNames(int|string $number)
    {
        return cache()->remember("tcx:user:group:{$this->instance->id}:{$number}", now()->addHour(), function () use ($number) {
            return collect($this->api()
                ->get('/Users?$filter=Number eq \''.$number.'\'&$select=Id,Number,DisplayName&$expand=Groups($select=GroupId,Name;$filter=not startsWith(Name,\'___FAVORITES___\'))')
                ->throw()
                ->json('value.0.Groups'))
                ->pluck('Name')
                ->toArray();
        });
    }

    /**
     * @return string
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function accessToken(): string
    {
        return cache()->remember("tcx:token:{$this->instance->id}", 3400, function () {
            return \Http::baseUrl("https://{$this->instance->hostname}:{$this->instance->port}")
                ->post('/webclient/api/Login/GetAccessToken', [
                    'username' => $this->instance->username,
                    'password' => $this->instance->password,
                ])
                ->throw()
                ->json('Token.access_token');
        });
    }

    /**
     * @return PendingRequest
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function api(): PendingRequest
    {
        $retries = app()->environment('local') ? 0 : 3;

        return \Http::timeout(30)
            ->retry($retries, 300)
            ->withToken($this->accessToken())
            ->baseUrl("https://{$this->instance->hostname}:{$this->instance->port}/xapi/v1");
    }

}
