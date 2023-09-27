<?php

use App\Http\Controllers\ApiAtkController;
use App\Http\Controllers\ApiBidangController;
use App\Http\Controllers\ApiReagenController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenerimaanAtkController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\PermintaanListAtkController;
use App\Http\Controllers\PermintaanReagenController;
use App\Http\Resources\UserResource;
use App\Models\ApiUser;
use App\Models\Atk;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource(ApiUser::with('bidang')->find($request->user()->id));
});

// Route::middleware(['auth:sanctum'])->group(function () {

// PENGGUNA
Route::apiResource('users', ApiUserController::class);
Route::patch('reset-password/{user}', [ApiUserController::class, 'resetPassword']);

// BIDANG
Route::apiResource('bidang', ApiBidangController::class);

// PENERIMAAN
Route::apiResource('penerimaan-reagen', PenerimaanController::class);
Route::apiResource('penerimaan-atk', PenerimaanAtkController::class);

// PERMINTAAN
Route::apiResource('permintaan-reagen', PermintaanReagenController::class);
Route::apiResource('permintaan-atk', PermintaanListAtkController::class);
// DOWNLOAD PERMINTAAN
Route::get('download-permintaan-reagen/{id_permintaan}', [PermintaanReagenController::class, 'downloadPermintaanReagen']);
Route::get('download-permintaan-atk/{id_permintaan}', [PermintaanListAtkController::class, 'downloadPermintaanAtk']);

Route::get('list-permintaan-reagen/{permintaan}', [PermintaanReagenController::class, 'listPermintaanReagen']);
Route::post('list-permintaan-reagen/{permintaan}', [PermintaanReagenController::class, 'addListPermintaanReagen']);
Route::delete('list-permintaan-reagen/{permintaan}/{barang}', [PermintaanReagenController::class, 'removeListPermintaanReagen']);

Route::get('list-permintaan-atk/{permintaan}', [PermintaanListAtkController::class, 'listPermintaanAtk']);
Route::post('list-permintaan-atk/{permintaan}', [PermintaanListAtkController::class, 'addListPermintaanAtk']);
Route::delete('list-permintaan-atk/{permintaan}/{atk}', [PermintaanListAtkController::class, 'removeListPermintaanAtk']);

// BARANG

// BARANG UNTUK REACT SELECT-OPTION
Route::get('barang-reagen/getAll', [ApiReagenController::class, 'getAll']);
Route::get('barang-atk/getAll', [ApiAtkController::class, 'getAll']);

Route::apiResource('barang-reagen', ApiReagenController::class);
Route::apiResource('barang-atk', ApiAtkController::class);

// });

// DI LUAR AUTH UNTUK DATA BARANG DI HOMEPAGE
Route::get('barang/reagen', [BarangController::class, 'getDataReagen']);
Route::get('barang/atk', [AtkController::class, 'getDataAtk']);
