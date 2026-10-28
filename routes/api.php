<?php

use App\Http\Controllers\Api\SinkronKehadiranController;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

// Endpoint sinkronisasi absensi kehadiran offline dari kiosk / client
Route::post('/sinkron-kehadiran', SinkronKehadiranController::class)
    ->name('api.sinkron-kehadiran')
    ->middleware([
        StartSession::class,
        'throttle:api-sinkron',
    ]);
