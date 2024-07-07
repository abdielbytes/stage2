<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Middleware\JWTMiddleware;

Route::post('auth/register', [JwtAuthController::class, 'register'])->withoutMiddleware([JWTMiddleware::class]);
Route::post('auth/login', [JwtAuthController::class, 'login']);

Route::middleware(JWTMiddleware::class)->group(function () {
    Route::get('/auth/me', function (Request $request) {
        return $request->user();
    });

    Route::get('/users/{userId}', [UserController::class, 'show']);
    Route::get('/organisations', [OrganisationController::class, 'index']);
    Route::get('/organisations/{orgId}', [OrganisationController::class, 'show']);
    Route::post('/organisations', [OrganisationController::class, 'store']);
    Route::post('/organisations/{orgId}/users', [OrganisationController::class, 'addUser']);
});
