<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\ApiReagen;
use App\Models\Atk;
use App\Models\BakuPembanding;
use App\Models\PerlengkapanKebersihan;
use App\Models\PersediaanKeluar;
use App\Models\SukuCadang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersediaanKeluarController extends Controller
{
    /**
     * Daftar riwayat persediaan keluar (Barang Usang / Rusak)
     */
    public function index(Request $request)
    {
        \App\Services\KartuStokService::ensureTablesExist();
        $perPage = (int) $request->query('per_page', 15);
        $type = $request->query('type');
        $kategoriKeluar = $request->query('kategori_keluar');
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = PersediaanKeluar::query();

        if ($type && $type !== 'all') {
            $query->where('barang_type', $type);
        }

        if ($kategoriKeluar && $kategoriKeluar !== 'all') {
            $query->where('kategori_keluar', $kategoriKeluar);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('petugas_name', 'like', "%{$search}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_keluar', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $query->whereDate('tanggal_keluar', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('tanggal_keluar', '<=', $endDate);
        }

        $data = $query->orderBy('tanggal_keluar', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($data);
    }

    /**
     * Simpan transaksi persediaan keluar (Usang / Rusak) & kurangi stok Master
     */
    public function store(Request $request)
    {
        \App\Services\KartuStokService::ensureTablesExist();
        $request->validate([
            'barang_type' => 'required|string|in:reagen,baku-pembanding,atk,perlengkapan,suku-cadang',
            'barang_id' => 'required|integer',
            'kategori_keluar' => 'required|string|in:usang,rusak',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:500',
            'petugas_name' => 'nullable|string|max:255',
            'tanggal_keluar' => 'nullable|date',
        ]);

        $barangType = $request->barang_type;
        $barangId = (int) $request->barang_id;
        $jumlah = (int) $request->jumlah;
        $kategoriKeluar = strtolower($request->kategori_keluar);

        [$targetItem, $namaBarang, $kodeBarang, $satuan, $lokasi] = $this->resolveTargetItem($barangType, $barangId);

        $stokAwal = (int) ($targetItem->stock ?? 0);
        if ($jumlah > $stokAwal) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah barang keluar ({$jumlah} {$satuan}) melebihi stok master yang tersedia ({$stokAwal} {$satuan}).",
            ], 422);
        }

        $stokSisa = max(0, $stokAwal - $jumlah);
        $tanggalKeluar = $request->tanggal_keluar
            ? Carbon::parse($request->tanggal_keluar)->setTimeFrom(now())
            : now();

        DB::beginTransaction();
        try {
            $targetItem->stock = $stokSisa;
            $targetItem->save();

            $defaultKeterangan = $kategoriKeluar === 'usang'
                ? 'Barang Usang / Kedaluwarsa'
                : 'Barang Rusak / Tidak Layak Pakai';

            $record = PersediaanKeluar::create([
                'barang_type' => $barangType,
                'barang_id' => $barangId,
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'satuan' => $satuan,
                'lokasi' => $lokasi,
                'kategori_keluar' => $kategoriKeluar,
                'jumlah' => $jumlah,
                'stok_awal' => $stokAwal,
                'stok_sisa' => $stokSisa,
                'keterangan' => $request->keterangan ?: $defaultKeterangan,
                'petugas_name' => $request->petugas_name ?: 'Admin Gudang BPOM',
                'tanggal_keluar' => $tanggalKeluar,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Persediaan keluar ({$namaBarang}) berhasil dicatat sebagai barang " . strtoupper($kategoriKeluar) . " dan stok master telah dikurangi.",
                'data' => $record,
                'updated_item' => $targetItem,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan persediaan keluar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update data riwayat persediaan keluar & sesuaikan stok Master
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori_keluar' => 'nullable|string|in:usang,rusak',
            'jumlah' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string|max:500',
            'tanggal_keluar' => 'nullable|date',
        ]);

        $record = PersediaanKeluar::findOrFail($id);

        DB::beginTransaction();
        try {
            [$targetItem, , , $satuan] = $this->resolveTargetItem($record->barang_type, $record->barang_id, false);

            if ($request->has('jumlah')) {
                $newJumlah = (int) $request->jumlah;
                $oldJumlah = (int) $record->jumlah;
                $diff = $newJumlah - $oldJumlah;

                if ($targetItem) {
                    $availableWithOld = (int) $targetItem->stock + $oldJumlah;
                    if ($newJumlah > $availableWithOld) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Jumlah baru ({$newJumlah} {$satuan}) melebihi total stok tersedia ({$availableWithOld} {$satuan}).",
                        ], 422);
                    }
                    $targetItem->stock = max(0, (int) $targetItem->stock - $diff);
                    $targetItem->save();
                    $record->stok_sisa = (int) $targetItem->stock;
                }

                $record->jumlah = $newJumlah;
            }

            if ($request->has('kategori_keluar')) {
                $record->kategori_keluar = strtolower($request->kategori_keluar);
            }

            if ($request->has('keterangan')) {
                $record->keterangan = $request->keterangan;
            }

            if ($request->has('tanggal_keluar') && $request->tanggal_keluar) {
                $record->tanggal_keluar = Carbon::parse($request->tanggal_keluar)->setTimeFrom($record->tanggal_keluar ?? now());
            }

            $record->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data persediaan keluar berhasil diperbarui.',
                'data' => $record,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus riwayat persediaan keluar & kembalikan stok ke Master
     */
    public function destroy($id)
    {
        $record = PersediaanKeluar::findOrFail($id);

        DB::beginTransaction();
        try {
            [$targetItem] = $this->resolveTargetItem($record->barang_type, $record->barang_id, false);
            if ($targetItem) {
                $targetItem->stock = (int) $targetItem->stock + (int) $record->jumlah;
                $targetItem->save();
            }

            $record->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Catatan persediaan keluar berhasil dihapus dan stok dikembalikan ke master.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus catatan: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function resolveTargetItem(string $barangType, int $barangId, bool $failIfNotFound = true): array
    {
        $targetItem = null;
        $namaBarang = '';
        $kodeBarang = null;
        $satuan = null;
        $lokasi = null;

        if ($barangType === 'baku-pembanding') {
            $targetItem = $failIfNotFound ? BakuPembanding::findOrFail($barangId) : BakuPembanding::find($barangId);
            if ($targetItem) {
                $namaBarang = $targetItem->name;
                $kodeBarang = $targetItem->kode_baru ?: $targetItem->code;
                $satuan = $targetItem->satuan;
                $lokasi = $targetItem->letak;
            }
        } elseif ($barangType === 'reagen') {
            $targetItem = $failIfNotFound ? ApiReagen::findOrFail($barangId) : ApiReagen::find($barangId);
            if ($targetItem) {
                $namaBarang = $targetItem->name;
                $kodeBarang = $targetItem->code ?? null;
                $satuan = $targetItem->satuan;
                $lokasi = $targetItem->lokasi ?? null;
            }
        } elseif ($barangType === 'atk') {
            $targetItem = $failIfNotFound ? Atk::findOrFail($barangId) : Atk::find($barangId);
            if ($targetItem) {
                $namaBarang = $targetItem->name;
                $kodeBarang = $targetItem->code ?? "ATK-{$targetItem->id}";
                $satuan = $targetItem->satuan;
            }
        } elseif ($barangType === 'perlengkapan') {
            $targetItem = $failIfNotFound ? PerlengkapanKebersihan::findOrFail($barangId) : PerlengkapanKebersihan::find($barangId);
            if ($targetItem) {
                $namaBarang = $targetItem->name;
                $kodeBarang = "PK-{$targetItem->id}";
                $satuan = $targetItem->satuan;
            }
        } elseif ($barangType === 'suku-cadang') {
            $targetItem = $failIfNotFound ? SukuCadang::findOrFail($barangId) : SukuCadang::find($barangId);
            if ($targetItem) {
                $namaBarang = $targetItem->name;
                $kodeBarang = $targetItem->code ?? "SC-{$targetItem->id}";
                $satuan = $targetItem->satuan;
            }
        }

        return [$targetItem, $namaBarang, $kodeBarang, $satuan, $lokasi];
    }
}
