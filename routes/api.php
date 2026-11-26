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
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\PesajeController;
use App\Http\Controllers\TratamientoController;
use App\Http\Controllers\TratamientoAnimalController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RondaController;
use App\Http\Controllers\UserController;

Route::get('/user', fn (Request $req) => new \App\Http\Resources\UserResource($req->user()))
    ->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    foreach (['categoria', 'unidad_medida', 'raza', 'potrero', 'insumo', 'animal', 'pesaje', 'tratamiento', 'tratamiento_animal', 'ronda'] as $parametro) {
        Route::pattern($parametro, '[0-9]+');
    }

    Route::post('auth/login', LoginController::class);
    Route::post('auth/logout', LogoutController::class)->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/register', RegisterController::class);
    Route::get('users', [UserController::class, 'index'])->name('users.index');

    Route::apiResource('categorias', CategoriaController::class)->parameters(['categorias'=>'categoria']);
    Route::apiResource('unidades-medida', UnidadMedidaController::class)->parameters(['unidades-medida'=>'unidad_medida']);
    Route::apiResource('razas', RazaController::class)->parameters(['razas'=>'raza']);
    Route::apiResource('potreros', PotreroController::class)->parameters(['potreros'=>'potrero']);
    Route::apiResource('insumos', InsumoController::class)->parameters(['insumos'=>'insumo']);

    Route::get('animales/qr/{codigo_qr}', [AnimalController::class, 'porQr'])->name('animales.por_qr');
    Route::apiResource('animales', AnimalController::class)->parameters(['animales'=>'animal']);
    Route::apiResource('pesajes', PesajeController::class)->parameters(['pesajes'=>'pesaje']);
    Route::apiResource('tratamientos', TratamientoController::class)->parameters(['tratamientos'=>'tratamiento']);
    Route::apiResource('tratamientos-aplicaciones', TratamientoAnimalController::class)->parameters(['tratamientos-aplicaciones'=>'tratamiento_animal']);

    Route::get('animales/{animal}/pesajes', [PesajeController::class, 'indexPorAnimal'])
        ->whereNumber('animal')->name('animales.pesajes.index');
    Route::post('animales/{animal}/pesajes', [PesajeController::class, 'storePorAnimal'])
        ->whereNumber('animal')->name('animales.pesajes.store');
    Route::get('animales/{animal}/tratamientos', [TratamientoAnimalController::class, 'indexPorAnimal'])
        ->whereNumber('animal')->name('animales.tratamientos.index');

    Route::get('potreros/{potrero}/animales', [AnimalController::class, 'indexPorPotrero'])
        ->whereNumber('potrero')->name('potreros.animales.index');
    Route::get('razas/{raza}/animales', [AnimalController::class, 'indexPorRaza'])
        ->whereNumber('raza')->name('razas.animales.index');
    Route::get('categorias/{categoria}/insumos', [InsumoController::class, 'indexPorCategoria'])
        ->whereNumber('categoria')->name('categorias.insumos.index');

    Route::get('rondas', [RondaController::class, 'index'])->name('rondas.index');
    Route::post('rondas', [RondaController::class, 'store'])->name('rondas.store');
    Route::get('rondas/{ronda}', [RondaController::class, 'show'])->name('rondas.show');
    Route::post('rondas/{ronda}/puntos', [RondaController::class, 'registrarPunto'])->name('rondas.puntos.store');
    Route::patch('rondas/{ronda}/finalizar', [RondaController::class, 'finalizar'])->name('rondas.finalizar');
    });
});