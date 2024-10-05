<?php

use Illuminate\Support\Facades\Route;

Route::post('/api/call', [\App\Http\Controllers\Api\CrmController::class, 'logCall']);
