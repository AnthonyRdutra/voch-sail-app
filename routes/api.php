<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, BandeiraController, ColaboradorController, GrupoEconomicoController, UnidadeController};
use Illuminate\Container\Attributes\Auth;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

});

