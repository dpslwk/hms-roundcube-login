<?php

use App\Http\Controllers\AuthCallbackController;
use App\Http\Controllers\LoginTeamController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::post('/login-team', LoginTeamController::class);
Route::get('/auth/callback', AuthCallbackController::class);
