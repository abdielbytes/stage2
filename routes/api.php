<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganisationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('auth/register', [RegisterController::class, 'store']);
Route::post('auth/login', [SessionsController::class, 'store']);



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users/{userId}', [UserController::class, 'show']);
    Route::get('/organisations', [OrganisationController::class, 'index']);
    Route::get('/organisations/{orgId}', [OrganisationController::class, 'show']);
    Route::post('/organisations', [OrganisationController::class, 'store']);
    Route::post('/organisations/{orgId}/users', [OrganisationController::class, 'addUser']);
});
