<?php

use App\Http\Controllers\ApiAtkController;
use App\Http\Controllers\ApiBidangController;
use App\Http\Controllers\ApiReagenController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\LaporanPermintaanController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenerimaanAtkController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\PermintaanListAtkController;
use App\Http\Controllers\PermintaanReagenController;
use App\Http\Resources\UserResource;
use App\Models\ApiUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        return new UserResource(ApiUser::with('bidang')->find($request->user()->id));
    });

    // PENGGUNA
    Route::apiResource('users', ApiUserController::class);
    Route::patch('reset-password/{user}', [ApiUserController::class, 'resetPassword']);

    // BIDANG
    Route::get('bidang/getAll', [ApiBidangController::class, 'getAll']);
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

    Route::post('acc-permintaan/{permintaan}', [PermintaanReagenController::class, 'accPermintaan']);

    // BARANG UNTUK REACT SELECT-OPTION (harus di atas routes barang)
    Route::get('barang-reagen/getAll', [ApiReagenController::class, 'getAll']);
    Route::get('barang-atk/getAll', [ApiAtkController::class, 'getAll']);

    Route::apiResource('barang-reagen', ApiReagenController::class);
    Route::apiResource('barang-atk', ApiAtkController::class);

    // LAPORAN PERMINTAAN
    Route::get('laporan-permintaan', [LaporanPermintaanController::class, 'index']);
    Route::get('laporan-permintaan-atk', [LaporanPermintaanController::class, 'permintaanAtk']);

    // DOWNLOAD LAPORAN
    Route::get('download-laporan-permintaan', [LaporanPermintaanController::class, 'downloadLaporanPermintaanReagen']);
    Route::get('download-laporan-permintaan-atk', [LaporanPermintaanController::class, 'downloadLaporanPermintaanAtk']);

    Route::patch('profile/update-password', [ApiUserController::class, 'updatePassword']);
    Route::patch('profile/{user}', [ApiUserController::class, 'updateProfile']);
});

// DI LUAR AUTH UNTUK DATA BARANG DI HOMEPAGE
Route::get('barang/reagen', [BarangController::class, 'getDataReagen']);
Route::get('barang/atk', [AtkController::class, 'getDataAtk']);
