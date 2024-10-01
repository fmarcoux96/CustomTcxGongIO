<?php

namespace App\Services;

use App\Models\LoggedCall;
use Carbon\Carbon;

interface TcxApiClientInterface
{

    public function status(): array;

    public function findRecordingForCall(LoggedCall $call): ?int;

    public function downloadRecordingForCall(int $recordingId, string $filename = null): string;

    public function getCallLogs(Carbon $start, ?Carbon $end = null): array;

}
