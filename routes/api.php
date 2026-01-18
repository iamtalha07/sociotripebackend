<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AmenityController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ContentController;

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

Route::prefix('provider')
    ->middleware("auth:api")
    ->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('profile-setup', 'profileSetupProvider');
        });

        Route::controller(ActivityController::class)->group(function () {
            Route::post('activities', 'store');
            Route::get('activities/get', 'getActivities');
        });

        Route::controller(ContentController::class)->group(function () {
            Route::post('post/add', 'createPost');
            Route::post('reel/add', 'createReel');
            Route::get('contents/get', 'getContents');
        });
    });

Route::get('categories', [CategoryController::class, 'index']);
Route::get('amenities', [AmenityController::class, 'index']);
