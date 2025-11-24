<?php

use App\Http\Controllers\TroubleshootController;
use Illuminate\Support\Facades\Route;

Route::prefix('troubleshoot')->group(
    function () {
        Route::get('auth-header', [
            TroubleshootController::class,
            'checkAuthHeader'
        ]);

        Route::put('put', [
            TroubleshootController::class,
            'showSuccessIfReachable'
        ]);

        Route::delete('delete', [
            TroubleshootController::class,
            'showSuccessIfReachable'
        ]);
    }
);
