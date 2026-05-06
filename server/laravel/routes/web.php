<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-web-controller', [App\Http\Controllers\FriendshipController::class, 'search']);
Route::get('/test-web', function() { return 'web hit'; });
