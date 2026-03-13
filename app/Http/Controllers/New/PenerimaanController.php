<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\ApiReagen;
use App\Models\Barang;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    // UNTUK crud ADMIN PERCEPAT
    function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');

        $query = Pembelian::with('barang')->whereHas(
            'barang',
            function ($query) use ($name_query) {
                $query->where('name', 'like', '%' . $name_query . '%');
            }
        )->latest();

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            // 'kode_or_name' => $kode_or_name
        ]);

        return response()->json($data);
    }

    function store(Request $request)
    {
        $request->validate([
            'barangNama' => 'required',
            'jumlah' => 'required|integer',
            'vendor' => 'required|string|max:255',
            'tglTerima' => 'required|date',
            'tglExpired' => 'nullable|date',
        ]);

        // return $request->all();
        DB::transaction(function () use ($request) {
            $barangWithNameAndExpiredSameSelected = Barang::where('name', $request->barangNama)
                ->where('expired', $request->tglExpired)->first();

            // INI UNTUK AMBIL NAMA DAN SATUAN UNTUK DATA BARANG BARU JIKA TIDAK ADA NAMA & EXPIRED YG SAMA DI MASTER BARANG
            $barangSelected = Barang::where('name', $request->barangNama)->first();

            // JIKA ADA BARANG YG SAMA NAMA DAN EXPIRED MAKA HANYA UPDATE STOCK
            if ($barangWithNameAndExpiredSameSelected) {
                $barangWithNameAndExpiredSameSelected->increment('stock', $request->jumlah);
            } else {
                // JIKA TIDAK MAKA BUAT BARANG BARU
                $barangNew = Barang::create([
                    'name' => $barangSelected['name'],
                    'satuan' => $barangSelected['satuan'],
                    'stock' => $request->jumlah,
                    'expired' => $request->tglExpired
                ]);
            }

            Pembelian::create([
                'barangs_id' => $barangWithNameAndExpiredSameSelected ? $barangWithNameAndExpiredSameSelected->id : $barangNew->id,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, Pembelian $penerimaan_reagen)
    {
        $request->validate([
            'barangNama' => 'required',
            'jumlah' => 'required|integer',
            'vendor' => 'required|string|max:255',
            'tglTerima' => 'required|date',
            'tglExpired' => 'nullable|date',
        ]);

        return [$request->all(), $penerimaan_reagen];
        DB::transaction(function () use ($request, $penerimaan_reagen) {
            // Calculate stock delta, jumlah nya jadi jumlah dikurangi jumlah lama itu yg ditambahkan ke stock
            $oldJumlah = $penerimaan_reagen->jumlah ?? 0;
            $delta = $request->jumlah - $oldJumlah;

            // JIKA BARANG NYA TIDAK DIGANTI MAKA HANYA UPDATE STOCK DAN SESUAIKAN DATA PEMBELIANNYA
            if ($request->barangId == $penerimaan_reagen->barangs_id) {
                $barang = Barang::where('name', $request->barangNama)
                    ->where('expired', $request->expired)
                    ->first();

                if (!$barang) {
                    $barang = Barang::create([
                        'name' => $request->barangNama,
                        'satuan' => $request->barangsatuan ?? $penerimaan_reagen->barang->satuan,
                        'stock' => $request->jumlah,
                        'expired' => $request->expired
                    ]);
                    // If new Barang, stock already set
                } else {
                    $barang->increment('stock', $delta);
                }
                $penerimaan_reagen->barangs_id = $barang->id;
            }
            // Update Pembelian
            $penerimaan_reagen->jumlah = $request->jumlah;
            $penerimaan_reagen->vendor = $request->vendor;
            $penerimaan_reagen->received_at = $request->tglTerima; // Assume received_at column or map to updated_at
            $penerimaan_reagen->save();

            $penerimaan_reagen->load('barang'); // Ensure relation for delta
        });

        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function destroy(Pembelian $penerimaan_reagen)
    {
        $penerimaan_reagen->barang()->decrement('stock', (int) $penerimaan_reagen->jumlah);
        $penerimaan_reagen->delete();

        return response()->json(['message' => 'Data berhasil dihapus!']);
    }
}
