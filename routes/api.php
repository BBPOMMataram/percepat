<?php

use App\Http\Controllers\AtkController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenerimaanAtkController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PermintaanListAtkController;
use App\Http\Controllers\PermintaanReagenController;
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
    return $request->user();
});
// Route::middleware(['auth:sanctum'])->group(function () {
// PENERIMAAN
Route::apiResource('penerimaan-reagen', PenerimaanController::class);
Route::apiResource('penerimaan-atk', PenerimaanAtkController::class);
// PERMINTAAN
Route::apiResource('permintaan-reagen', PermintaanReagenController::class);
Route::apiResource('permintaan-atk', PermintaanListAtkController::class);

// BARANG
Route::get('barang-reagen/getAll', function(Request $request){ //ntar handle di route barang pake controller, route ini untuk 'select options'
    $name_query = $request->query('name');

    $responseReagen = Barang::where('name', 'like', '%'.$name_query.'%')->get();
    return response()->json($responseReagen);
});

Route::get('barang-atk/getAll', function(Request $request){ //ntar handle di route barang pake controller, route ini untuk 'select options'
    $name_query = $request->query('name');

    $responseReagen = Atk::where('name', 'like', '%'.$name_query.'%')->get();
    return response()->json($responseReagen);
});
// });

Route::get('barang/reagen', [BarangController::class, 'getDataReagen']);
Route::get('barang/atk', [AtkController::class, 'getDataAtk']);
