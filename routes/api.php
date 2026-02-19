<?php

use App\Http\Controllers\ApiAtkController;
use App\Http\Controllers\ApiBidangController;
use App\Http\Controllers\ApiReagenController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\LaporanPermintaanController;
use App\Http\Controllers\New\PerlengkapanKebersihanAdminController;
use App\Http\Controllers\New\PerlengkapanKebersihanController;
use App\Http\Controllers\New\PermintaanListPerlengkapanKebersihanController;
use App\Http\Controllers\New\PermintaanReagenController as NewPermintaanReagenController;
use App\Http\Controllers\PenerimaanAtkController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PermintaanListAtkController;
use App\Http\Controllers\PermintaanReagenController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SurveyPelananPublicController;
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
    Route::get('barang-reagen-expired', [ApiReagenController::class, 'reagenExpired']);
    Route::get('barang-reagen-expired-count', [ApiReagenController::class, 'reagenExpiredCount']);

    // LAPORAN PERMINTAAN
    Route::get('laporan-permintaan', [LaporanPermintaanController::class, 'index']);
    Route::get('laporan-permintaan-atk', [LaporanPermintaanController::class, 'permintaanAtk']);

    // DOWNLOAD LAPORAN
    Route::get('download-laporan-permintaan', [LaporanPermintaanController::class, 'downloadLaporanPermintaanReagen']);
    Route::get('download-laporan-permintaan-atk', [LaporanPermintaanController::class, 'downloadLaporanPermintaanAtk']);

    Route::patch('profile/update-password', [ApiUserController::class, 'updatePassword']);
    Route::patch('profile/{user}', [ApiUserController::class, 'updateProfile']);

    // DOWNLOAD REAGEN
    Route::get('download-reagen', [ApiReagenController::class, 'downloadReagen']);
    // DOWNLOAD ATK
    Route::get('download-atk', [ApiAtkController::class, 'downloadAtk']);
});

Route::get('download-penerimaan-reagen', [PenerimaanController::class, 'downloadPenerimaanReagen']);
Route::get('download-penerimaan-atk', [PenerimaanAtkController::class, 'downloadPenerimaanAtk']);

// DI LUAR AUTH UNTUK DATA BARANG DI HOMEPAGE
Route::get('barang/reagen', [BarangController::class, 'getDataReagen']);
Route::get('barang/atk', [AtkController::class, 'getDataAtk']);

Route::get('barang', [ChartController::class, 'barang']);
Route::get('reagen/permintaan', [ChartController::class, 'permintaan']);
Route::get('reagen-ed', [ChartController::class, 'reagen_ed']);

Route::get('bidang-count', [ApiBidangController::class, 'bidang_count']);
Route::get('users-count', [ApiUserController::class, 'users_count']);

// DATA WEBISTE DI HOMEPAGE
Route::get('site', [SiteController::class, 'getSites']);

// DATA SURVEY PELAYANAN PUBLIC
Route::post('spp', [SurveyPelananPublicController::class, 'store']);






// NEW ROUTES
Route::middleware(['jwt'])->prefix('v1')->group(function () {
    // BARANG UNTUK REACT SELECT-OPTION (harus di atas routes barang)
    Route::get('barang-reagen-all', [ApiReagenController::class, 'getAll']);
    Route::get('barang-atk-all', [ApiAtkController::class, 'getAll']);
    Route::get('barang-perlengkapan-kebersihan-all', [PerlengkapanKebersihanController::class, 'getAll']);
    // tambah untuk baku pembanding dan suku cadang nanti

    // PERMINTAAN
    Route::apiResource('permintaan-reagen', NewPermintaanReagenController::class);
    Route::apiResource('permintaan-perlengkapan-kebersihan', PerlengkapanKebersihanController::class)->only(['index', 'store']);
    Route::apiResource('permintaan-atk', PermintaanListAtkController::class);

    // DATA LIST PERMINTAAN
    Route::get('list-permintaan-perlengkapan-kebersihan/{permintaan}', [PermintaanListPerlengkapanKebersihanController::class, 'list_permintaan_perlengkapan_kebersihan']);
    Route::get('download-permintaan-perlengkapan/{permintaan}', [PermintaanListPerlengkapanKebersihanController::class, 'download_permintaan_perlengkapan']);

    // DATA MASTER
    Route::apiResource('perlengkapan-kebersihan', PerlengkapanKebersihanAdminController::class);
});
