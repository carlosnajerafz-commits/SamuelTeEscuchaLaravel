<?php

use App\Http\Controllers\Admin\BloqueoCitaController;
use App\Http\Controllers\Admin\CatalogoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CodigoPostalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::view('/aviso-de-privacidad', 'legal.privacy')->name('privacy');
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
Route::post('/chatbot/mensaje', [ChatbotController::class, 'mensaje'])->middleware('throttle:30,1')->name('chatbot.mensaje');
Route::post('/chatbot/reiniciar', [ChatbotController::class, 'reiniciar'])->middleware('throttle:30,1')->name('chatbot.reiniciar');
Route::get('/buscar-cp', [CodigoPostalController::class, 'lookup'])->middleware('throttle:60,1')->name('cp.lookup');
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('/admin/codigos-postales', CodigoPostalController::class)->except('show');
});
Route::get('/citas/disponibilidad', [SolicitudController::class, 'availability'])->middleware('throttle:60,1')->name('citas.availability');
Route::get('/solicitudes/{module}', [SolicitudController::class, 'create'])->whereIn('module', ['quejas', 'citas', 'apoyos', 'comedor'])->name('solicitudes.create');
Route::post('/solicitudes/{module}', [SolicitudController::class, 'store'])->whereIn('module', ['quejas', 'citas', 'apoyos', 'comedor'])->name('solicitudes.store');
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/solicitudes/{module}', [App\Http\Controllers\Admin\SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/{module}/exportar', [App\Http\Controllers\Admin\SolicitudController::class, 'export'])->name('solicitudes.export');
    Route::get('/solicitudes/{module}/{id}', [App\Http\Controllers\Admin\SolicitudController::class, 'show'])->whereNumber('id')->name('solicitudes.show');
    Route::get('/solicitudes/{module}/{id}/evidencia', [App\Http\Controllers\Admin\SolicitudController::class, 'evidence'])->whereNumber('id')->name('solicitudes.evidence');
    Route::patch('/solicitudes/{module}/{id}', [App\Http\Controllers\Admin\SolicitudController::class, 'update'])->whereNumber('id')->name('solicitudes.update');
    Route::delete('/solicitudes/{module}/{id}', [App\Http\Controllers\Admin\SolicitudController::class, 'destroy'])->whereNumber('id')->name('solicitudes.destroy');
    Route::get('/catalogos/{catalog}', [CatalogoController::class, 'index'])->name('catalogos.index');
    Route::get('/catalogos/{catalog}/crear', [CatalogoController::class, 'form'])->name('catalogos.create');
    Route::get('/catalogos/{catalog}/{id}/editar', [CatalogoController::class, 'form'])->whereNumber('id')->name('catalogos.edit');
    Route::post('/catalogos/{catalog}', [CatalogoController::class, 'save'])->name('catalogos.store');
    Route::put('/catalogos/{catalog}/{id}', [CatalogoController::class, 'save'])->whereNumber('id')->name('catalogos.update');
    Route::get('/bloqueos', [BloqueoCitaController::class, 'index'])->name('bloqueos.index');
    Route::post('/bloqueos', [BloqueoCitaController::class, 'store'])->name('bloqueos.store');
    Route::delete('/bloqueos/{id}', [BloqueoCitaController::class, 'destroy'])->whereNumber('id')->name('bloqueos.destroy');
});
