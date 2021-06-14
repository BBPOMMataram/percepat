<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);
    Route::get('dtusers', [UserController::class, 'dt_users'])->name('dt_users');

    Route::resource('profile', ProfileController::class);

    Route::get('/test', function(){
        dd(auth()->user());
    });

    Route::resource('barang', BarangController::class);
    Route::get('dtbarang', [BarangController::class, 'dt_barang'])->name('dt_barang');

    Route::resource('pembelian', PembelianController::class);
    Route::get('dtpembelian', [PembelianController::class, 'dt_pembelian'])->name('dt_pembelian');

    Route::resource('permintaan', PermintaanController::class);
    Route::get('dtpermintaan', [PermintaanController::class, 'dt_permintaan'])->name('dt_permintaan');
});

Route::view('login', 'login')->name('login');
Route::post('login', [LoginController::class, 'authenticate']);

Route::get('logout', function(){
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
