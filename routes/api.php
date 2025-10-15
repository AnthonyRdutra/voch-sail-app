<?php

use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\{BandeiraController, ColaboradorController, GrupoEconomicoController, UnidadeController}; 


Route::apiResource('grupo-economico', GrupoEconomicoController::class); 
Route::apiResource('bandeiras', BandeiraController::class); 
Route::apiResource('unidades', UnidadeController::class); 
Route::apiResource('colaboradores', ColaboradorController::class);