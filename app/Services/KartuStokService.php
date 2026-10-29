<?php

namespace App\Services;

use App\Models\Pembelian;
use App\Models\PenerimaanAtk;
use App\Models\PenerimaanBakuPembanding;
use App\Models\PenerimaanPerlengkapanKebersihan;
use App\Models\PenerimaanSukuCadang;
use App\Models\PermintaanList;
use App\Models\PermintaanListAtk;
use App\Models\PermintaanListBakuPembanding;
use App\Models\PermintaanListPerlengkapanKebersihan;
use App\Models\PermintaanListSukuCadang;
use App\Models\PersediaanKeluar;
use App\Models\StokOpname;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class KartuStokService
{
    public static function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('persediaan_keluars')) {
                Schema::create('persediaan_keluars', function (Blueprint $table) {
                    $table->id();
                    $table->string('barang_type', 50)->index();
                    $table->unsignedBigInteger('barang_id')->index();
                    $table->string('kode_barang', 100)->nullable();
                    $table->string('nama_barang', 255);
                    $table->string('satuan', 50)->nullable();
                    $table->string('lokasi', 255)->nullable();
                    $table->string('kategori_keluar', 50)->index();
                    $table->integer('jumlah')->default(1);
                    $table->integer('stok_awal')->default(0);
                    $table->integer('stok_sisa')->default(0);
                    $table->text('keterangan')->nullable();
                    $table->string('petugas_name', 255)->nullable();
                    $table->dateTime('tanggal_keluar')->index();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('stok_opname_berita_acaras')) {
                Schema::create('stok_opname_berita_acaras', function (Blueprint $table) {
                    $table->id();
                    $table->string('judul', 255)->nullable();
                    $table->string('nomor_ba', 150)->nullable();
                    $table->date('tanggal_ba')->nullable();
                    $table->string('file_name', 255);
                    $table->string('file_path', 500);
                    $table->unsignedBigInteger('file_size')->nullable();
                    $table->string('uploaded_by', 255)->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignore schema creation errors if DB is read-only
        }
    }

    /**
     * Gabungkan seluruh transaksi (Pengadaan/Penerimaan, Permintaan, Persediaan Keluar Usang/Rusak, & Stok Opname)
     * untuk Kartu Stok.
     */
    public static function buildTransaksi(string $barangType, $barang): Collection
    {
        self::ensureTablesExist();

        $id = $barang->id;
        $penerimaan = collect();
        $permintaan = collect();

        if ($barangType === 'reagen') {
            $penerimaan = Pembelian::where('barangs_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => 'Pengadaan (' . ($item->vendor ?? '-') . ')',
                        'tipe' => 'masuk',
                        'jumlah' => (int) $item->jumlah,
                    ];
                });

            $permintaan = PermintaanList::where('barang_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => $item->keterangan ?? 'Permintaan',
                        'tipe' => 'keluar',
                        'jumlah' => (int) ($item->jumlahrealisasi ?? $item->jumlahpermintaan),
                    ];
                });
        } elseif ($barangType === 'baku-pembanding') {
            $penerimaan = PenerimaanBakuPembanding::where('baku_pembanding_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => 'Pengadaan (' . ($item->vendor ?? '-') . ')',
                        'tipe' => 'masuk',
                        'jumlah' => (int) $item->jumlah,
                    ];
                });

            $permintaan = PermintaanListBakuPembanding::where('baku_pembanding_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => $item->keterangan ?? 'Permintaan',
                        'tipe' => 'keluar',
                        'jumlah' => (int) ($item->jumlahrealisasi ?? $item->jumlahpermintaan),
                    ];
                });
        } elseif ($barangType === 'atk') {
            $penerimaan = PenerimaanAtk::where('atk_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => 'Pengadaan (' . ($item->vendor ?? '-') . ')',
                        'tipe' => 'masuk',
                        'jumlah' => (int) $item->jumlah,
                    ];
                });

            $permintaan = PermintaanListAtk::where('atk_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => $item->keterangan ?? 'Usulan',
                        'tipe' => 'keluar',
                        'jumlah' => (int) ($item->jumlahrealisasi ?? $item->jumlahpermintaan),
                    ];
                });
        } elseif ($barangType === 'perlengkapan') {
            $penerimaan = PenerimaanPerlengkapanKebersihan::where('perlengkapan_kebersihan_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => 'Pengadaan (' . ($item->vendor ?? '-') . ')',
                        'tipe' => 'masuk',
                        'jumlah' => (int) $item->jumlah,
                    ];
                });

            $permintaan = PermintaanListPerlengkapanKebersihan::where('perlengkapan_kebersihan_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => $item->keterangan ?? 'Permintaan',
                        'tipe' => 'keluar',
                        'jumlah' => (int) ($item->jumlahrealisasi ?? $item->jumlahpermintaan),
                    ];
                });
        } elseif ($barangType === 'suku-cadang') {
            $penerimaan = PenerimaanSukuCadang::where('suku_cadang_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => 'Pengadaan',
                        'tipe' => 'masuk',
                        'jumlah' => (int) $item->jumlah,
                    ];
                });

            $permintaan = PermintaanListSukuCadang::where('suku_cadang_id', $id)
                ->whereNotNull('created_at')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tanggal' => $item->created_at->toDateTimeString(),
                        'keterangan' => $item->keterangan ?? 'Permintaan',
                        'tipe' => 'keluar',
                        'jumlah' => (int) ($item->jumlahrealisasi ?? $item->jumlahpermintaan),
                    ];
                });
        }

        // Persediaan Keluar (Barang Usang / Rusak)
        $persediaanKeluar = collect();
        if (Schema::hasTable('persediaan_keluars')) {
            $typeVariants = $barangType === 'perlengkapan' ? ['perlengkapan', 'perlengkapan-kebersihan'] : [$barangType];
            $persediaanKeluar = PersediaanKeluar::whereIn('barang_type', $typeVariants)
                ->where('barang_id', $id)
                ->orderBy('tanggal_keluar', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($item) {
                    $labelKat = strtolower($item->kategori_keluar) === 'usang' ? 'Barang Usang' : 'Barang Rusak';
                    $ket = trim((string) ($item->keterangan ?? ''));
                    $ketText = ($ket !== '' && strtolower($ket) !== strtolower($labelKat))
                        ? "Persediaan Keluar - {$labelKat} ({$ket})"
                        : "Persediaan Keluar - {$labelKat}";

                    return [
                        'tanggal' => ($item->tanggal_keluar ?? $item->created_at ?? now())->toDateTimeString(),
                        'keterangan' => $ketText,
                        'tipe' => 'keluar',
                        'jumlah' => (int) $item->jumlah,
                        'stok_awal' => (int) $item->stok_awal,
                        'stok_akhir' => (int) $item->stok_sisa,
                    ];
                });
        }

        // Stok Opname
        $stokOpname = collect();
        if (Schema::hasTable('stok_opnames')) {
            $typeVariants = $barangType === 'perlengkapan' ? ['perlengkapan', 'perlengkapan-kebersihan'] : [$barangType];
            $stokOpname = StokOpname::whereIn('barang_type', $typeVariants)
                ->where('barang_id', $id)
                ->orderBy('tanggal_opname', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($item) {
                    $selisih = (int) $item->selisih;
                    $ket = trim((string) ($item->keterangan ?? ''));
                    if ($ket === '') {
                        $ket = $selisih === 0
                            ? 'Stok Opname (Stok Sesuai)'
                            : ($selisih > 0 ? 'Stok Opname (Penyesuaian Lebih)' : 'Stok Opname (Penyesuaian Kurang)');
                    } elseif (stripos($ket, 'opname') === false && stripos($ket, 'BA') === false) {
                        $ket = 'Stok Opname: ' . $ket;
                    }

                    return [
                        'tanggal' => ($item->tanggal_opname ?? $item->created_at ?? now())->toDateTimeString(),
                        'keterangan' => $ket,
                        'tipe' => $selisih > 0 ? 'masuk' : ($selisih < 0 ? 'keluar' : 'opname'),
                        'jumlah' => abs($selisih),
                        'stok_awal' => (int) $item->stok_sistem,
                        'stok_akhir' => (int) $item->stok_fisik,
                        'is_opname' => true,
                    ];
                });
        }

        $transaksi = $penerimaan
            ->concat($permintaan)
            ->concat($persediaanKeluar)
            ->concat($stokOpname)
            ->sortBy('tanggal')
            ->values();

        // Jika transaksi pertama adalah keluar atau opname dari barang yang sudah punya stok awal di master
        if ($transaksi->isNotEmpty()) {
            $first = $transaksi->first();
            if (isset($first['stok_awal']) && (int) $first['stok_awal'] > 0 && ($first['tipe'] === 'keluar' || $first['tipe'] === 'opname')) {
                $awalTanggal = $barang->created_at
                    ? $barang->created_at->toDateTimeString()
                    : $first['tanggal'];
                $transaksi->prepend([
                    'tanggal' => $awalTanggal,
                    'keterangan' => 'Stok Awal Master',
                    'tipe' => 'masuk',
                    'jumlah' => (int) $first['stok_awal'],
                ]);
            }
        } elseif ((int) ($barang->stock ?? 0) > 0) {
            // Jika belum ada catatan transaksi sama sekali namun stok master > 0
            $transaksi->push([
                'tanggal' => ($barang->created_at ?? now())->toDateTimeString(),
                'keterangan' => 'Stok Awal Master',
                'tipe' => 'masuk',
                'jumlah' => (int) $barang->stock,
            ]);
        }

        return $transaksi;
    }
}

