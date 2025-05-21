<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataHarianController;
use App\Http\Controllers\KomoditasController;
use App\Http\Controllers\RespondenController;
use App\Http\Controllers\UserController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);
Route::get('/data-harian/{dataHarian}/cetak-pdf', [DataHarianController::class, 'cetakPdf'])
    ->name('data-harian.cetak-pdf');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'me']);

    Route::get('data-harian', [DataHarianController::class, 'getDataHarian']);
    Route::put('/data-harian/{id}', [DataHarianController::class, 'editDataHarian']);
    Route::post('/data-harian', [DataHarianController::class, 'addDataHarian']);

    Route::get('komoditas', [KomoditasController::class, 'getAllKomoditas']);
    Route::get('komoditas/{id}', [KomoditasController::class, 'getKomoditasById']);

    // Respondens Routes
    Route::get('respondens', [RespondenController::class, 'getAllRespondens']);
    Route::get('respondens/{id}', [RespondenController::class, 'getRespondensById']);

    Route::post('/logout', [AuthController::class, 'logout']);

});
