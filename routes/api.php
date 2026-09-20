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
Route::prefix('v1')->group(function () {
// Los identificadores de todos los recursos son numéricos: un id como /animales/abc
// no coincide con ninguna ruta y responde 404 JSON (en lugar de un 500 por TypeError).
foreach (['categoria', 'unidad_medida', 'raza', 'potrero', 'insumo', 'animal', 'pesaje', 'tratamiento', 'tratamiento_animal'] as $parametro) {
    Route::pattern($parametro, '[0-9]+');
}

Route::apiResource('categorias', CategoriaController::class)->parameters(['categorias'=>'categoria']);
Route::apiResource('unidad-medida', UnidadMedidaController::class)->parameters(['unidad-medida'=>'unidad_medida']);
Route::apiResource('razas', RazaController::class)->parameters(['razas'=>'raza']);
Route::apiResource('potreros', PotreroController::class)->parameters(['potreros'=>'potrero']);
Route::apiResource('insumos', InsumoController::class)->parameters(['insumos'=>'insumo']);
Route::apiResource('animales', AnimalController::class)->parameters(['animales'=>'animal']);
Route::apiResource('pesajes', PesajeController::class)->parameters(['pesajes'=>'pesaje']);
Route::apiResource('tratamientos', TratamientoController::class)->parameters(['tratamientos'=>'tratamiento']);
Route::apiResource('tratamiento-animal', TratamientoAnimalController::class)->parameters(['tratamiento-animal'=>'tratamiento_animal']);

// Rutas anidadas del núcleo ganadero (Lab 5): producción y sanidad de un animal.
// whereNumber evita que un {animal} no numérico llegue a los controladores (responde 404).
Route::get('animales/{animal}/pesajes', [PesajeController::class, 'indexPorAnimal'])
    ->whereNumber('animal')->name('animales.pesajes.index');
Route::post('animales/{animal}/pesajes', [PesajeController::class, 'storePorAnimal'])
    ->whereNumber('animal')->name('animales.pesajes.store');
Route::get('animales/{animal}/tratamientos', [TratamientoAnimalController::class, 'indexPorAnimal'])
    ->whereNumber('animal')->name('animales.tratamientos.index');
});