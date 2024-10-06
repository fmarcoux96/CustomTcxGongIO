<?php

namespace App\Services;

use App\Models\LoggedCall;
use Carbon\Carbon;

interface TcxApiClientInterface
{
    public function status(): array;
}
