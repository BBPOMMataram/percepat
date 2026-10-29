<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\ApiReagen;
use App\Models\BakuPembanding;
use App\Models\Atk;
use App\Models\PerlengkapanKebersihan;
use App\Models\SukuCadang;
use App\Models\StokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StokOpnameController extends Controller
{
    /**
     * Menampilkan riwayat hasil Stok Opname
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);
        $search = $request->query('search');
        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = StokOpname::query();

        if ($type && $type !== 'all') {
            $query->where('barang_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('petugas_name', 'like', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->whereDate('tanggal_opname', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal_opname', '<=', $endDate);
        }

        $query->orderBy('tanggal_opname', 'desc')
              ->orderBy('id', 'desc');

        $data = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($data);
    }

    /**
     * Pencarian / scan barang untuk Stok Opname
     * Mendukung pemindaian QR code text, ID, code, kode_baru, atau nama
     */
    public function lookup(Request $request)
    {
        $queryText = trim($request->query('query', ''));
        $type = $request->query('type', 'all');

        if (!$queryText) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter pencarian tidak boleh kosong',
                'data' => []
            ], 400);
        }

        // Coba cek jika text yang discan adalah format QR kita:
        // Contoh format QR:
        // 1) "BPOM-PERCEPAT:baku-pembanding:12"
        // 2) URL: "http://.../percepat-new/baku-pembanding/12"
        // 3) atau JSON string
        // 4) atau ID langsung: 12
        // 5) atau kode_baru: "BP-001"
        $parsedType = null;
        $parsedId = null;

        if (preg_match('#percepat-new/(reagen|baku-pembanding)/(\d+)#i', $queryText, $matches)) {
            $parsedType = strtolower($matches[1]);
            $parsedId = (int) $matches[2];
        } elseif (str_starts_with($queryText, 'BPOM-PERCEPAT:')) {
            $parts = explode(':', $queryText);
            if (count($parts) >= 3) {
                $parsedType = $parts[1];
                $parsedId = (int) $parts[2];
            }
        } elseif (str_starts_with($queryText, '{') && str_ends_with($queryText, '}')) {
            $json = json_decode($queryText, true);
            if ($json && isset($json['id'])) {
                $parsedId = (int) $json['id'];
                $parsedType = $json['type'] ?? $json['category'] ?? null;
            }
        }

        $results = [];

        // 1. Cari Baku Pembanding
        if (($type === 'all' || $type === 'baku-pembanding') && (!$parsedType || $parsedType === 'baku-pembanding')) {
            $bpQuery = BakuPembanding::query();
            if ($parsedId && ($parsedType === 'baku-pembanding' || !$parsedType)) {
                $bpQuery->where('id', $parsedId);
            } else {
                $bpQuery->where(function ($q) use ($queryText) {
                    $q->where('name', 'like', "%{$queryText}%")
                      ->orWhere('kode_baru', 'like', "%{$queryText}%")
                      ->orWhere('code', 'like', "%{$queryText}%")
                      ->orWhere('letak', 'like', "%{$queryText}%");
                    if (is_numeric($queryText)) {
                        $q->orWhere('id', (int) $queryText);
                    }
                });
            }

            $bpList = $bpQuery->limit(10)->get()->map(function ($item) {
                return [
                    'barang_type' => 'baku-pembanding',
                    'barang_type_label' => 'Baku Pembanding',
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'kode_baru' => $item->kode_baru,
                    'satuan' => $item->satuan,
                    'lokasi' => $item->letak,
                    'expired' => $item->expired ? Carbon::parse($item->expired)->format('Y-m-d') : null,
                    'stock' => (int) $item->stock,
                ];
            });

            $results = array_merge($results, $bpList->toArray());
        }

        // 2. Cari Reagen
        if (($type === 'all' || $type === 'reagen') && (!$parsedType || $parsedType === 'reagen')) {
            $rQuery = ApiReagen::query();
            if ($parsedId && ($parsedType === 'reagen' || !$parsedType)) {
                $rQuery->where('id', $parsedId);
            } else {
                $rQuery->where(function ($q) use ($queryText) {
                    $q->where('name', 'like', "%{$queryText}%")
                      ->orWhere('satuan', 'like', "%{$queryText}%");
                    if (is_numeric($queryText)) {
                        $q->orWhere('id', (int) $queryText);
                    }
                });
            }

            $rList = $rQuery->limit(10)->get()->map(function ($item) {
                return [
                    'barang_type' => 'reagen',
                    'barang_type_label' => 'Reagen',
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code ?? null,
                    'kode_baru' => null,
                    'satuan' => $item->satuan,
                    'lokasi' => $item->lokasi ?? null,
                    'expired' => $item->expired ? Carbon::parse($item->expired)->format('Y-m-d') : null,
                    'stock' => (int) $item->stock,
                ];
            });

            $results = array_merge($results, $rList->toArray());
        }

        // 3. Cari ATK
        if (($type === 'all' || $type === 'atk') && (!$parsedType || $parsedType === 'atk')) {
            $atkQuery = Atk::query();
            if ($parsedId && ($parsedType === 'atk' || !$parsedType)) {
                $atkQuery->where('id', $parsedId);
            } else {
                $atkQuery->where(function ($q) use ($queryText) {
                    $q->where('name', 'like', "%{$queryText}%")
                      ->orWhere('satuan', 'like', "%{$queryText}%");
                    if (is_numeric($queryText)) {
                        $q->orWhere('id', (int) $queryText);
                    }
                });
            }

            $atkList = $atkQuery->limit(10)->get()->map(function ($item) {
                return [
                    'barang_type' => 'atk',
                    'barang_type_label' => 'ATK',
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => "ATK-{$item->id}",
                    'kode_baru' => null,
                    'satuan' => $item->satuan,
                    'lokasi' => null,
                    'expired' => null,
                    'stock' => (int) $item->stock,
                ];
            });

            $results = array_merge($results, $atkList->toArray());
        }

        // 4. Cari Perlengkapan Kebersihan
        if (($type === 'all' || $type === 'perlengkapan' || $type === 'perlengkapan-kebersihan') && (!$parsedType || $parsedType === 'perlengkapan' || $parsedType === 'perlengkapan-kebersihan')) {
            $pkQuery = PerlengkapanKebersihan::query();
            if ($parsedId && ($parsedType === 'perlengkapan' || $parsedType === 'perlengkapan-kebersihan' || !$parsedType)) {
                $pkQuery->where('id', $parsedId);
            } else {
                $pkQuery->where(function ($q) use ($queryText) {
                    $q->where('name', 'like', "%{$queryText}%")
                      ->orWhere('satuan', 'like', "%{$queryText}%");
                    if (is_numeric($queryText)) {
                        $q->orWhere('id', (int) $queryText);
                    }
                });
            }

            $pkList = $pkQuery->limit(10)->get()->map(function ($item) {
                return [
                    'barang_type' => 'perlengkapan',
                    'barang_type_label' => 'Perlengkapan Kebersihan',
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => "PK-{$item->id}",
                    'kode_baru' => null,
                    'satuan' => $item->satuan,
                    'lokasi' => null,
                    'expired' => null,
                    'stock' => (int) $item->stock,
                ];
            });

            $results = array_merge($results, $pkList->toArray());
        }

        // 5. Cari Suku Cadang
        if (($type === 'all' || $type === 'suku-cadang') && (!$parsedType || $parsedType === 'suku-cadang')) {
            $scQuery = SukuCadang::query();
            if ($parsedId && ($parsedType === 'suku-cadang' || !$parsedType)) {
                $scQuery->where('id', $parsedId);
            } else {
                $scQuery->where(function ($q) use ($queryText) {
                    $q->where('name', 'like', "%{$queryText}%")
                      ->orWhere('satuan', 'like', "%{$queryText}%")
                      ->orWhere('description', 'like', "%{$queryText}%");
                    if (is_numeric($queryText)) {
                        $q->orWhere('id', (int) $queryText);
                    }
                });
            }

            $scList = $scQuery->limit(10)->get()->map(function ($item) {
                return [
                    'barang_type' => 'suku-cadang',
                    'barang_type_label' => 'Suku Cadang',
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => "SC-{$item->id}",
                    'kode_baru' => null,
                    'satuan' => $item->satuan,
                    'lokasi' => null,
                    'expired' => null,
                    'stock' => (int) $item->stock,
                ];
            });

            $results = array_merge($results, $scList->toArray());
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Menyimpan hasil penyesuaian Stok Opname
     */
    public function store(Request $request)
    {
        $request->validate([
            'barang_type' => 'required|string|in:reagen,baku-pembanding,atk,perlengkapan,suku-cadang',
            'barang_id' => 'required|integer',
            'stok_fisik' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:500',
            'petugas_name' => 'nullable|string|max:255',
            'petugas_external_id' => 'nullable|string|max:255',
            'tanggal_opname' => 'nullable|date',
        ]);

        $barangType = $request->barang_type;
        $barangId = $request->barang_id;
        $stokFisik = (int) $request->stok_fisik;

        $targetItem = null;
        $namaBarang = '';
        $kodeBarang = null;
        $satuan = null;
        $lokasi = null;

        if ($barangType === 'baku-pembanding') {
            $targetItem = BakuPembanding::findOrFail($barangId);
            $namaBarang = $targetItem->name;
            $kodeBarang = $targetItem->kode_baru ?: $targetItem->code;
            $satuan = $targetItem->satuan;
            $lokasi = $targetItem->letak;
        } elseif ($barangType === 'reagen') {
            $targetItem = ApiReagen::findOrFail($barangId);
            $namaBarang = $targetItem->name;
            $kodeBarang = $targetItem->code ?? null;
            $satuan = $targetItem->satuan;
            $lokasi = $targetItem->lokasi ?? null;
        } elseif ($barangType === 'atk') {
            $targetItem = Atk::findOrFail($barangId);
            $namaBarang = $targetItem->name;
            $satuan = $targetItem->satuan;
        } elseif ($barangType === 'perlengkapan') {
            $targetItem = PerlengkapanKebersihan::findOrFail($barangId);
            $namaBarang = $targetItem->name;
            $satuan = $targetItem->satuan;
        } elseif ($barangType === 'suku-cadang') {
            $targetItem = SukuCadang::findOrFail($barangId);
            $namaBarang = $targetItem->name;
            $satuan = $targetItem->satuan;
        }

        $stokSistem = (int) ($targetItem->stock ?? 0);
        $selisih = $stokFisik - $stokSistem;

        DB::beginTransaction();
        try {
            // Update stok di master data barang
            $targetItem->stock = $stokFisik;
            $targetItem->save();

            // Catat log stok opname
            $opname = StokOpname::create([
                'barang_type' => $barangType,
                'barang_id' => $barangId,
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
                'satuan' => $satuan,
                'lokasi' => $lokasi,
                'stok_sistem' => $stokSistem,
                'stok_fisik' => $stokFisik,
                'selisih' => $selisih,
                'keterangan' => $request->keterangan ?: ($selisih === 0 ? 'Stok Sesuai' : ($selisih > 0 ? 'Stok Lebih' : 'Stok Kurang')),
                'petugas_name' => $request->petugas_name,
                'petugas_external_id' => $request->petugas_external_id,
                'tanggal_opname' => $this->parseSafeDate($request->tanggal_opname, now()),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stok Opname berhasil disimpan dan stok master telah diperbarui.',
                'data' => $opname,
                'updated_item' => $targetItem,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan stok opname: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk Import Data Barang Masuk ke Master (dari file Excel / PDF Berita Acara hasil parse)
     */
    public function importMaster(Request $request)
    {
        $request->validate([
            'barang_type' => 'nullable|string|in:all,reagen,baku-pembanding,atk,perlengkapan,perlengkapan-kebersihan,suku-cadang',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.stock' => 'nullable|numeric',
            'items.*.satuan' => 'nullable|string',
            'items.*.expired' => 'nullable|string',
            'items.*.lokasi' => 'nullable|string',
            'items.*.letak' => 'nullable|string',
            'items.*.barang_type' => 'nullable|string',
        ]);

        $defaultBarangType = $request->barang_type ?: 'reagen';
        $items = $request->items;
        $stockMode = $request->input('stock_mode', 'replace'); // default replace untuk Berita Acara Stok Opname
        $shouldLogOpname = $request->boolean('catat_opname', true);
        $tanggalOpname = $this->parseSafeDate($request->input('tanggal_opname'), now());
        $petugasName = $request->input('petugas_name', 'Admin');
        $keteranganUmum = $request->input('keterangan', 'Penyesuaian Berita Acara Stock Opname');

        $createdCount = 0;
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($items as $row) {
                $name = trim($row['name']);
                if (!$name) continue;

                $barangType = !empty($row['barang_type']) ? $row['barang_type'] : $defaultBarangType;
                if ($barangType === 'all') $barangType = 'reagen';

                $stock = isset($row['stock']) ? (int) $row['stock'] : 0;
                $satuan = !empty($row['satuan']) ? trim($row['satuan']) : ($barangType === 'baku-pembanding' ? 'Vial' : ($barangType === 'reagen' ? 'Botol' : 'Pcs'));
                $parsedExp = !empty($row['expired']) ? $this->parseSafeDate($row['expired']) : null;
                $expired = $parsedExp ? $parsedExp->format('Y-m-d') : null;
                $kodeBaru = !empty($row['kode_baru']) ? trim($row['kode_baru']) : null;
                $code = !empty($row['code']) ? trim($row['code']) : (!empty($row['no_kontrol']) ? trim($row['no_kontrol']) : null);
                $letak = !empty($row['letak']) ? trim($row['letak']) : (!empty($row['lokasi']) ? trim($row['lokasi']) : null);

                $targetItem = null;
                $stokSistemAwal = 0;

                if ($barangType === 'baku-pembanding') {
                    // 1. Prioritaskan pencocokan nama barang (zat kimia unik)
                    $existing = BakuPembanding::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();

                    // 2. Fallback kode_baru hanya jika bukan penomoran 6-digit BMN yang berulang
                    if (!$existing && $kodeBaru && !preg_match('/^\d{6}$/', $kodeBaru)) {
                        $existing = BakuPembanding::where('kode_baru', $kodeBaru)->first();
                    }

                    if ($existing) {
                        $stokSistemAwal = (int) $existing->stock;
                        $existing->stock = ($stockMode === 'add') ? ($existing->stock + $stock) : $stock;
                        if ($satuan) $existing->satuan = $satuan;
                        if ($expired) $existing->expired = $expired;
                        if ($letak) $existing->letak = $letak;
                        if ($kodeBaru) $existing->kode_baru = $kodeBaru;
                        if ($code) $existing->code = $code;
                        $existing->save();
                        $targetItem = $existing;
                        $updatedCount++;
                    } else {
                        $targetItem = BakuPembanding::create([
                            'name' => $name,
                            'code' => $code,
                            'kode_baru' => $kodeBaru,
                            'stock' => $stock,
                            'satuan' => $satuan,
                            'expired' => $expired,
                            'letak' => $letak,
                        ]);
                        $stokSistemAwal = 0;
                        $createdCount++;
                    }
                } elseif ($barangType === 'reagen') {
                    $existing = ApiReagen::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();

                    if ($existing) {
                        $stokSistemAwal = (int) $existing->stock;
                        $existing->stock = ($stockMode === 'add') ? ($existing->stock + $stock) : $stock;
                        if ($satuan) $existing->satuan = $satuan;
                        if ($expired) $existing->expired = $expired;
                        if ($code) $existing->code = $code;
                        if ($letak) $existing->lokasi = $letak;
                        $existing->save();
                        $targetItem = $existing;
                        $updatedCount++;
                    } else {
                        $targetItem = ApiReagen::create([
                            'name' => $name,
                            'code' => $code,
                            'stock' => $stock,
                            'satuan' => $satuan,
                            'expired' => $expired,
                            'lokasi' => $letak,
                        ]);
                        $stokSistemAwal = 0;
                        $createdCount++;
                    }
                } elseif ($barangType === 'atk') {
                    $existing = Atk::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();

                    if ($existing) {
                        $stokSistemAwal = (int) $existing->stock;
                        $existing->stock = ($stockMode === 'add') ? ($existing->stock + $stock) : $stock;
                        if ($satuan) $existing->satuan = $satuan;
                        $existing->save();
                        $targetItem = $existing;
                        $updatedCount++;
                    } else {
                        $targetItem = Atk::create([
                            'name' => $name,
                            'stock' => $stock,
                            'satuan' => $satuan ?: 'Pcs',
                        ]);
                        $stokSistemAwal = 0;
                        $createdCount++;
                    }
                } elseif ($barangType === 'perlengkapan' || $barangType === 'perlengkapan-kebersihan') {
                    $existing = PerlengkapanKebersihan::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();

                    if ($existing) {
                        $stokSistemAwal = (int) $existing->stock;
                        $existing->stock = ($stockMode === 'add') ? ($existing->stock + $stock) : $stock;
                        if ($satuan) $existing->satuan = $satuan;
                        $existing->save();
                        $targetItem = $existing;
                        $updatedCount++;
                    } else {
                        $targetItem = PerlengkapanKebersihan::create([
                            'name' => $name,
                            'stock' => $stock,
                            'satuan' => $satuan ?: 'Pcs',
                        ]);
                        $stokSistemAwal = 0;
                        $createdCount++;
                    }
                } elseif ($barangType === 'suku-cadang') {
                    $existing = SukuCadang::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();

                    if ($existing) {
                        $stokSistemAwal = (int) $existing->stock;
                        $existing->stock = ($stockMode === 'add') ? ($existing->stock + $stock) : $stock;
                        if ($satuan) $existing->satuan = $satuan;
                        $existing->save();
                        $targetItem = $existing;
                        $updatedCount++;
                    } else {
                        $targetItem = SukuCadang::create([
                            'name' => $name,
                            'stock' => $stock,
                            'satuan' => $satuan ?: 'Pcs',
                        ]);
                        $stokSistemAwal = 0;
                        $createdCount++;
                    }
                }

                // Catat otomatis ke log riwayat Stok Opname jika diminta
                if ($shouldLogOpname && $targetItem) {
                    $stokFisikAkhir = (int) $targetItem->stock;
                    $selisih = $stokFisikAkhir - $stokSistemAwal;
                    $kodeBarangLog = $kodeBaru ?: ($code ?: ($targetItem->code ?? "ID-{$targetItem->id}"));

                    StokOpname::create([
                        'barang_type' => $barangType,
                        'barang_id' => $targetItem->id,
                        'kode_barang' => $kodeBarangLog,
                        'nama_barang' => $targetItem->name,
                        'satuan' => $targetItem->satuan ?? $satuan,
                        'lokasi' => $letak ?: ($targetItem->lokasi ?? ($targetItem->letak ?? null)),
                        'stok_sistem' => $stokSistemAwal,
                        'stok_fisik' => $stokFisikAkhir,
                        'selisih' => $selisih,
                        'keterangan' => $row['keterangan'] ?? $keteranganUmum,
                        'petugas_name' => $petugasName,
                        'tanggal_opname' => $tanggalOpname,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import Berita Acara berhasil: {$createdCount} barang baru ditambahkan, {$updatedCount} barang diperbarui stoknya.",
                'data' => [
                    'created' => $createdCount,
                    'updated' => $updatedCount,
                    'total' => $createdCount + $updatedCount
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan import data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update catatan riwayat stok opname
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:500',
            'tanggal_opname' => 'nullable|date',
            'petugas_name' => 'nullable|string|max:255',
            'stok_fisik' => 'nullable|integer|min:0',
        ]);

        $opname = StokOpname::findOrFail($id);
        if ($request->has('keterangan')) {
            $opname->keterangan = $request->keterangan;
        }
        if ($request->has('tanggal_opname')) {
            $opname->tanggal_opname = $this->parseSafeDate($request->tanggal_opname, $opname->tanggal_opname);
        }
        if ($request->has('petugas_name')) {
            $opname->petugas_name = $request->petugas_name;
        }
        if ($request->has('stok_fisik')) {
            $stokFisikBaru = (int) $request->stok_fisik;
            $opname->stok_fisik = $stokFisikBaru;
            $opname->selisih = $stokFisikBaru - (int) $opname->stok_sistem;
        }
        $opname->save();

        return response()->json([
            'success' => true,
            'message' => 'Catatan riwayat stok opname berhasil diperbarui.',
            'data' => $opname
        ]);
    }

    /**
     * Hapus catatan riwayat stok opname
     */
    public function destroy($id)
    {
        $opname = StokOpname::findOrFail($id);
        $opname->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catatan riwayat stok opname berhasil dihapus.'
        ]);
    }

    /**
     * Daftar Dokumen PDF Berita Acara Stok Opname yang Tersimpan
     */
    public function listBeritaAcara(Request $request)
    {
        \App\Services\KartuStokService::ensureTablesExist();

        $items = \App\Models\StokOpnameBeritaAcara::orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Upload & Simpan Dokumen PDF Berita Acara Stok Opname
     */
    public function uploadBeritaAcara(Request $request)
    {
        \App\Services\KartuStokService::ensureTablesExist();

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:51200',
            'judul' => 'nullable|string|max:255',
            'nomor_ba' => 'nullable|string|max:150',
            'tanggal_ba' => 'nullable|date',
            'uploaded_by' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $safeFileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
        $storedPath = $file->storeAs('berita-acara-so', $safeFileName, 'public');

        $ba = \App\Models\StokOpnameBeritaAcara::create([
            'judul' => $request->input('judul') ?: pathinfo($originalName, PATHINFO_FILENAME),
            'nomor_ba' => $request->input('nomor_ba'),
            'tanggal_ba' => $request->input('tanggal_ba') ? $this->parseSafeDate($request->input('tanggal_ba'), now())->format('Y-m-d') : now()->format('Y-m-d'),
            'file_name' => $originalName,
            'file_path' => $storedPath,
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->input('uploaded_by') ?: 'Admin Gudang BPOM',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen PDF Berita Acara berhasil disimpan.',
            'data' => $ba,
        ], 201);
    }

    /**
     * Stream / Preview atau Download Dokumen PDF Berita Acara Stok Opname
     */
    public function streamBeritaAcara(Request $request, $id)
    {
        \App\Services\KartuStokService::ensureTablesExist();

        $ba = \App\Models\StokOpnameBeritaAcara::findOrFail($id);
        $fullPath = storage_path('app/public/' . $ba->file_path);

        if (!file_exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File PDF Berita Acara tidak ditemukan di server.',
            ], 404);
        }

        $isDownload = $request->boolean('download', false);
        $disposition = $isDownload ? 'attachment' : 'inline';
        $downloadName = $ba->file_name ?: ('Berita_Acara_Stok_Opname_' . $ba->id . '.pdf');

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . addslashes($downloadName) . '"',
        ]);
    }

    /**
     * Hapus Dokumen PDF Berita Acara dari daftar
     */
    public function deleteBeritaAcara($id)
    {
        \App\Services\KartuStokService::ensureTablesExist();

        $ba = \App\Models\StokOpnameBeritaAcara::findOrFail($id);
        $ba->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen Berita Acara berhasil dihapus dari daftar.',
        ]);
    }

    /**
     * Hapus Semua Data Master Barang (Pemusnahan Barang di BPOM)
     */
    public function deleteAllMaster(Request $request)
    {
        $request->validate([
            'category' => 'required|string|in:reagen,baku-pembanding,atk,perlengkapan,perlengkapan-kebersihan,suku-cadang',
        ]);

        $category = $request->input('category');
        $deletedCount = 0;
        $categoryLabel = '';

        DB::beginTransaction();
        try {
            switch ($category) {
                case 'reagen':
                    $categoryLabel = 'Reagen';
                    $deletedCount = ApiReagen::query()->delete();
                    break;
                case 'baku-pembanding':
                    $categoryLabel = 'Baku Pembanding';
                    $deletedCount = BakuPembanding::query()->delete();
                    break;
                case 'atk':
                    $categoryLabel = 'ATK';
                    $deletedCount = Atk::query()->delete();
                    break;
                case 'perlengkapan':
                case 'perlengkapan-kebersihan':
                    $categoryLabel = 'Perlengkapan Kebersihan';
                    $deletedCount = PerlengkapanKebersihan::query()->delete();
                    break;
                case 'suku-cadang':
                    $categoryLabel = 'Suku Cadang';
                    $deletedCount = SukuCadang::query()->delete();
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pemusnahan berhasil: seluruh data {$categoryLabel} ({$deletedCount} barang) telah dihapus dari sistem.",
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan pemusnahan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper untuk mem-parsing tanggal secara aman dari berbagai format (ISO '2025-12-31', Indonesia '31 Desember 2025', d/m/Y, dll)
     */
    private function parseSafeDate($dateStr, $default = null)
    {
        if (!$dateStr) {
            return $default;
        }

        if ($dateStr instanceof Carbon) {
            return $dateStr;
        }

        $dateStr = trim((string) $dateStr);

        // Jika sudah format YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $dateStr);
            } catch (\Throwable $e) {}
        }

        // Terjemahkan nama bulan Indonesia ke Inggris untuk Carbon
        $indoMonths = [
            'januari' => 'january', 'februari' => 'february', 'maret' => 'march',
            'april' => 'april', 'mei' => 'may', 'juni' => 'june',
            'juli' => 'july', 'agustus' => 'august', 'september' => 'september',
            'oktober' => 'october', 'november' => 'november', 'desember' => 'december',
            'agt' => 'aug', 'agu' => 'aug', 'okt' => 'oct', 'des' => 'dec'
        ];

        $lower = strtolower($dateStr);
        foreach ($indoMonths as $id => $en) {
            if (str_contains($lower, $id)) {
                $lower = str_replace($id, $en, $lower);
                break;
            }
        }

        try {
            return Carbon::parse($lower);
        } catch (\Throwable $e) {
            try {
                if (preg_match('#^\d{1,2}[/-]\d{1,2}[/-]\d{4}$#', $dateStr)) {
                    $sep = str_contains($dateStr, '/') ? '/' : '-';
                    return Carbon::createFromFormat("d{$sep}m{$sep}Y", $dateStr);
                }
            } catch (\Throwable $e2) {}

            return $default;
        }
    }
}
