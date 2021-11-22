<?php

use Illuminate\Support\Facades\Route;
use tmartone\LaravelGoogleCalendar\Http\Controllers\OAuthController;

Route::get('oauth', [OAuthController::class, 'index']);
Route::get('oauth2callback', [OAuthController::class, 'index']);
Route::middleware(['google_consent'])->group(function () {
Route::get('events', [OAuthController::class, 'events' ]);


});
