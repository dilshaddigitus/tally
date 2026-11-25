<?php

use App\Http\Controllers\Api\V1\Connector\CompaniesController;
use App\Http\Controllers\Api\V1\Connector\HeartbeatController;
use App\Http\Controllers\Api\V1\Connector\JobController;
use App\Http\Controllers\Api\V1\Connector\PairingController;
use App\Http\Controllers\Api\V1\Connector\StatusController;
use App\Http\Controllers\Api\V1\Connector\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/connector')->name('api.v1.connector.')->group(function () {
    // Unauthenticated: a device has no token yet, or its access token just expired.
    Route::post('pair', [PairingController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('pair');

    Route::post('refresh-token', [TokenController::class, 'refresh'])
        ->middleware('throttle:20,1')
        ->name('refresh-token');

    // Authenticated: a live, non-revoked device access token.
    Route::middleware('connector.auth')->group(function () {
        Route::post('revoke', [TokenController::class, 'revoke'])->name('revoke');
        Route::post('heartbeat', [HeartbeatController::class, 'store'])->name('heartbeat');
        Route::post('status', [StatusController::class, 'store'])->name('status');
        Route::post('companies', [CompaniesController::class, 'store'])->name('companies');

        Route::get('jobs/pending', [JobController::class, 'pending'])->name('jobs.pending');
        Route::post('jobs/{job}/start', [JobController::class, 'start'])->name('jobs.start');
        Route::post('jobs/{job}/complete', [JobController::class, 'complete'])->name('jobs.complete');
        Route::post('jobs/{job}/fail', [JobController::class, 'fail'])->name('jobs.fail');
    });
});
