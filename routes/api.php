<?php

use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\RazaController;
use App\Http\Controllers\PotreroController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\PesajeController;
use App\Http\Controllers\TratamientoController;
use App\Http\Controllers\TratamientoAnimalController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





//rutas para las validaciones
Route::apiResource('categorias', CategoriaController::class)->parameters(['categorias'=>'categoria']);
Route::apiResource('unidad-medida', UnidadMedidaController::class)->parameters(['unidad-medida'=>'unidad_medida']);
Route::apiResource('razas', RazaController::class)->parameters(['razas'=>'raza']);
Route::apiResource('potreros', PotreroController::class)->parameters(['potreros'=>'potrero']);
Route::apiResource('insumos', InsumoController::class)->parameters(['insumos'=>'insumo']);
Route::apiResource('animales', AnimalController::class)->parameters(['animales'=>'animal']);
Route::apiResource('pesajes', PesajeController::class)->parameters(['pesajes'=>'pesaje']);
Route::apiResource('tratamientos', TratamientoController::class)->parameters(['tratamientos'=>'tratamiento']);
Route::apiResource('tratamiento-animal', TratamientoAnimalController::class)->parameters(['tratamiento-animal'=>'tratamiento_animal']);

