<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')
    ->controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login')->name('login');
        Route::post('forgot', 'forgot');
        Route::post('social/login', 'getSocialData');
    });

Route::prefix('auth')
    ->middleware("auth:api")
    ->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('verify/otp', 'verifyOtp');
            Route::get('resend/otp', 'resendOtp');
            Route::post('change/password', 'changePassword');
            Route::post('logout', 'logout');
            // Route::post('profile-setup', 'profileSetup');
        });
    });
