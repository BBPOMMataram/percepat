<?php

use App\Events\TestEvent;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangLabController;
use App\Http\Controllers\BidangController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\PermintaanListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Models\Barang;
use App\Models\Bidang;
use App\Models\Pembelian;
use App\Models\Permintaan;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\Type\ObjectType;

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

Route::get('/send-msg', function () {
    TestEvent::dispatch('hii from controller');

    // event(new TestEvent); //alternative
    return 'event sent';
});

Route::get('/storagelink', function () {
    return Artisan::call('storage:link');
});

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('barang.tanpalogin');
})->name('home');

Route::get('dtbarangtanpalogin', [BarangController::class, 'dt_barang_tanpalogin'])->name('dt_barang_tanpalogin');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $data = (object)[];

        $jmlpembelian = Pembelian::count();
        $jmlpermintaan = Permintaan::count();
        $jmlbarang = Barang::count();
        $jmluser = User::count() - 1;

        $data->jmlpembelian = $jmlpembelian;
        $data->jmlpermintaan = $jmlpermintaan;
        $data->jmlbarang = $jmlbarang;
        $data->jmluser = $jmluser;

        $recentpermintaan = Permintaan::with('status', 'bidang.user')->orderBy('created_at', 'desc')->limit(10)->get();

        return view('dashboard', compact('data', 'recentpermintaan'));
    })->name('dashboard');

    Route::resource('users', UserController::class);
    Route::get('dtusers', [UserController::class, 'dt_users'])->name('dt_users');

    Route::resource('profile', ProfileController::class);

    Route::prefix('barang')->group(function () {
        Route::resource('reagen', BarangController::class);
        Route::get('dtbarang', [BarangController::class, 'dt_barang'])->name('dt_barang');

        Route::resource('atk', AtkController::class);
        Route::get('dtbarang-atk', [AtkController::class, 'dt_barang_atk'])->name('dt_barang_atk');
    });

    Route::resource('pembelian', PembelianController::class);
    Route::get('dtpembelian', [PembelianController::class, 'dt_pembelian'])->name('dt_pembelian');

    Route::resource('permintaan', PermintaanController::class);
    Route::get('dtpermintaan', [PermintaanController::class, 'dt_permintaan'])->name('dt_permintaan');
    Route::get('print-permintaan/{idpermintaan}', [PermintaanController::class, 'print_permintaan'])->name('print_permintaan');
    Route::patch('changetglpermintaan', [PermintaanController::class, 'changetglpermintaan'])->name('changetglpermintaan');

    Route::patch('kabidaccpermintaan/{idpermintaan}', [PermintaanController::class, 'kabid_accpermintaan'])->name('kabid_accpermintaan');
    Route::patch('penyerahaccpermintaan/{idpermintaan}', [PermintaanController::class, 'penyerah_accpermintaan'])->name('penyerah_accpermintaan');
    Route::patch('kasubbagumumaccpermintaan/{idpermintaan}', [PermintaanController::class, 'kasubbagumum_accpermintaan'])->name('kasubbagumum_accpermintaan');

    Route::get('permintaanlistdone/{idpermintaan}', [PermintaanController::class, 'permintaanlist_done'])->name('permintaanlist.done');
    Route::get('permintaanlist/{idpermintaan}', [PermintaanController::class, 'permintaanlist_index'])->name('permintaanlist.index');
    Route::get('permintaanlist/create/{idpermintaan}', [PermintaanController::class, 'permintaanlist_create'])->name('permintaanlist.create');
    Route::put('permintaanlist/{idpermintaan}/{idbarang}', [PermintaanController::class, 'permintaanlist_update'])->name('permintaanlist.update');
    Route::get('permintaanlist/{idpermintaan}/{idbarang}/edit', [PermintaanController::class, 'permintaanlist_edit'])->name('permintaanlist.edit');
    Route::post('permintaanlist/{idpermintaan}', [PermintaanController::class, 'permintaanlist_store'])->name('permintaanlist.store');
    Route::delete('permintaanlist/{idpermintaan}/{idbarang}', [PermintaanController::class, 'permintaanlist_destroy'])->name('permintaanlist.destroy');
    Route::get('dt_permintaanlist/{idpermintaan}', [PermintaanController::class, 'dt_permintaanlist'])->name('dt_permintaanlist');
    Route::get('print-laporan/{id?}', [PermintaanController::class, 'print_laporan'])->name('print_laporan');

    Route::get('laporan', [PermintaanController::class, 'laporan'])->name('laporan');
    Route::get('dt_laporan/{id?}', [PermintaanController::class, 'dt_laporan'])->name('dt_laporan');

    Route::resource('bidang', BidangController::class);
    Route::get('dtbidang', [BidangController::class, 'dt_bidang'])->name('dt_bidang');

    // tambahan data barang
    // Route::resource('baranglab', BarangLabController::class);
    // Route::get('dtbaranglab', [BarangLabController::class, 'dt_baranglab'])->name('dt_baranglab');
});

Route::view('login', 'login')->name('login');
Route::post('login', [LoginController::class, 'authenticate']);

Route::get('logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
