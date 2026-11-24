<?php

use App\Http\Controllers\Api\V1\ApoyoController;
use App\Http\Controllers\Api\V1\CitaController;
use App\Http\Controllers\Api\V1\CodigoPostalController;
use App\Http\Controllers\Api\V1\ComedorController;
use App\Http\Controllers\Api\V1\EstadisticaController;
use App\Http\Controllers\Api\V1\QuejaController;
use App\Http\Controllers\Api\V1\SeguimientoController;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

/*
| API v1. Lo público solo expone información que ya es pública en el sitio; todo lo que
| contiene datos personales requiere un token Bearer de empleado (Sanctum).
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('token', [TokenController::class, 'store'])->middleware('throttle:5,1')->name('token.store');
    Route::post('seguimiento', [SeguimientoController::class, 'consultar'])->middleware('throttle:10,1')->name('seguimiento');

    Route::middleware('throttle:60,1')->group(function () {
        Route::get('apoyos', [ApoyoController::class, 'index'])->name('apoyos.index');
        Route::get('comedor/eventos', [ComedorController::class, 'index'])->name('comedor.index');
        Route::get('codigos-postales/{cp}', [CodigoPostalController::class, 'show'])->where('cp', '[0-9]{5}')->name('cp.show');
        Route::get('citas/disponibilidad/{fecha}', [CitaController::class, 'disponibilidad'])->where('fecha', '[0-9]{4}-[0-9]{2}-[0-9]{2}')->name('citas.disponibilidad');
    });

    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::get('me', [TokenController::class, 'me'])->name('me');
        Route::delete('token', [TokenController::class, 'destroy'])->name('token.destroy');

        Route::get('citas', [CitaController::class, 'index'])->name('citas.index');
        Route::get('citas/{cita}', [CitaController::class, 'show'])->whereNumber('cita')->name('citas.show');
        Route::patch('citas/{cita}/estatus', [CitaController::class, 'actualizarEstatus'])->whereNumber('cita')->name('citas.estatus');

        Route::get('quejas', [QuejaController::class, 'index'])->name('quejas.index');
        Route::get('quejas/{queja}', [QuejaController::class, 'show'])->whereNumber('queja')->name('quejas.show');
        Route::patch('quejas/{queja}/estatus', [QuejaController::class, 'actualizarEstatus'])->whereNumber('queja')->name('quejas.estatus');

        Route::get('registros-apoyo', [ApoyoController::class, 'registros'])->name('apoyos.registros');
        Route::patch('registros-apoyo/{registro}/estatus', [ApoyoController::class, 'actualizarEstatus'])->whereNumber('registro')->name('apoyos.estatus');

        Route::get('comedor/registros', [ComedorController::class, 'registros'])->name('comedor.registros');
        Route::get('estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas');
    });
});
