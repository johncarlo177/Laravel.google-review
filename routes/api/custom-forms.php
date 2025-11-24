<?php

use App\Http\Controllers\CustomFormController;
use App\Http\Controllers\CustomFormResponseController;
use Illuminate\Support\Facades\Route;

class CustomFormsRouter
{
    static function protectedRoutes()
    {
        Route::prefix('custom-forms')->group(function () {
            Route::post('', [CustomFormController::class, 'store']);
            Route::put('{customForm}', [CustomFormController::class, 'update']);

            Route::post(
                '{customForm}/save-settings',
                [CustomFormController::class, 'saveSettings']
            );

            Route::get('{customForm}/responses', [CustomFormResponseController::class, 'showAutomaticPopupResponses']);

            Route::get('response/{customFormResponse}', [CustomFormResponseController::class, 'show']);

            Route::delete(
                'response/{customFormResponse}',
                [
                    CustomFormResponseController::class,
                    'deleteResponse'
                ]
            );

            Route::put(
                'response/{response}',
                [
                    CustomFormResponseController::class,
                    'updateResponse'
                ]
            );
        });
    }

    static function publicRoutes()
    {
        Route::prefix('custom-forms')->group(function () {

            Route::get('{customForm}', [CustomFormController::class, 'show']);

            Route::post(
                '{customForm}/response',
                [CustomFormResponseController::class, 'saveResponse']
            );
        });
    }
}
