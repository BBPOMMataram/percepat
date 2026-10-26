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
                    'lokasi' => null,
                    'expired' => $item->expired ? Carbon::parse($item->expired)->format('Y-m-d') : null,
                    'stock' => (int) $item->stock,
                ];
            });

            $results = array_merge($results, $rList->toArray());
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
                'tanggal_opname' => $request->tanggal_opname ? Carbon::parse($request->tanggal_opname) : now(),
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
     * Bulk Import Data Barang Masuk ke Master (dari file Excel / Spreadsheet hasil parse)
     */
    public function importMaster(Request $request)
    {
        $request->validate([
            'barang_type' => 'required|string|in:reagen,baku-pembanding',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.stock' => 'nullable|numeric',
            'items.*.satuan' => 'nullable|string',
            'items.*.expired' => 'nullable|string',
        ]);

        $barangType = $request->barang_type;
        $items = $request->items;
        $stockMode = $request->input('stock_mode', 'add'); // 'add' (tambah stok) or 'replace' (timpa stok)
        $createdCount = 0;
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($items as $row) {
                $name = trim($row['name']);
                if (!$name) continue;

                $stock = isset($row['stock']) ? (int) $row['stock'] : 0;
                $satuan = !empty($row['satuan']) ? trim($row['satuan']) : ($barangType === 'baku-pembanding' ? 'Vial' : 'Botol');
                $expired = !empty($row['expired']) ? Carbon::parse($row['expired'])->format('Y-m-d') : null;
                $kodeBaru = !empty($row['kode_baru']) ? trim($row['kode_baru']) : null;
                $code = !empty($row['code']) ? trim($row['code']) : (!empty($row['no_kontrol']) ? trim($row['no_kontrol']) : null);
                $letak = !empty($row['letak']) ? trim($row['letak']) : (!empty($row['lokasi']) ? trim($row['lokasi']) : null);

                if ($barangType === 'baku-pembanding') {
                    // Cari jika ada kode_baru atau nama sama
                    $existing = null;
                    if ($kodeBaru) {
                        $existing = BakuPembanding::where('kode_baru', $kodeBaru)->first();
                    }
                    if (!$existing) {
                        $existing = BakuPembanding::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();
                    }

                    if ($existing) {
                        if ($stockMode === 'replace') {
                            $existing->stock = $stock;
                        } else {
                            $existing->stock += $stock;
                        }
                        if ($satuan) $existing->satuan = $satuan;
                        if ($expired) $existing->expired = $expired;
                        if ($letak) $existing->letak = $letak;
                        if ($kodeBaru) $existing->kode_baru = $kodeBaru;
                        if ($code) $existing->code = $code;
                        $existing->save();
                        $updatedCount++;
                    } else {
                        BakuPembanding::create([
                            'name' => $name,
                            'code' => $code,
                            'kode_baru' => $kodeBaru,
                            'stock' => $stock,
                            'satuan' => $satuan,
                            'expired' => $expired,
                            'letak' => $letak,
                        ]);
                        $createdCount++;
                    }
                } elseif ($barangType === 'reagen') {
                    $existing = ApiReagen::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->first();

                    if ($existing) {
                        if ($stockMode === 'replace') {
                            $existing->stock = $stock;
                        } else {
                            $existing->stock += $stock;
                        }
                        if ($satuan) $existing->satuan = $satuan;
                        if ($expired) $existing->expired = $expired;
                        if ($code) $existing->code = $code;
                        $existing->save();
                        $updatedCount++;
                    } else {
                        ApiReagen::create([
                            'name' => $name,
                            'code' => $code,
                            'stock' => $stock,
                            'satuan' => $satuan,
                            'expired' => $expired,
                        ]);
                        $createdCount++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import berhasil: {$createdCount} barang baru ditambahkan, {$updatedCount} barang diperbarui stoknya.",
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
}
