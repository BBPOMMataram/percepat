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
                $newBarangId = $barangWithNameAndExpiredSameSelected->id;
            } else {
                // JIKA TIDAK MAKA BUAT BARANG BARU
                $barangNew = Barang::create([
                    'name' => $barangSelected['name'],
                    'satuan' => $barangSelected['satuan'],
                    'stock' => $request->jumlah,
                    'expired' => $request->tglExpired
                ]);
                $newBarangId = $barangNew->id;
            }

            Pembelian::create([
                'barangs_id' => $newBarangId,
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

        DB::transaction(function () use ($request, $penerimaan_reagen) {
            // First, reverse the old stock addition (like destroy)
            $oldBarang = $penerimaan_reagen->barang;
            if ($oldBarang) {
                $oldBarang->decrement('stock', $penerimaan_reagen->jumlah);
            }

            // Now handle new barang like in store
            $barangWithNameAndExpiredSameSelected = Barang::where('name', $request->barangNama)
                ->where('expired', $request->tglExpired)->first();

            $barangSelected = Barang::where('name', $request->barangNama)->first();

            if ($barangWithNameAndExpiredSameSelected) {
                // Update stock of existing matching barang
                $barangWithNameAndExpiredSameSelected->increment('stock', $request->jumlah);
                $newBarangId = $barangWithNameAndExpiredSameSelected->id;
            } else {
                // Create new barang
                $barangNew = Barang::create([
                    'name' => $barangSelected->name,
                    'satuan' => $barangSelected->satuan,
                    'stock' => $request->jumlah,
                    'expired' => $request->tglExpired
                ]);
                $newBarangId = $barangNew->id;
            }

            // Update the Pembelian record
            $penerimaan_reagen->update([
                'barangs_id' => $newBarangId,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function destroy(Pembelian $penerimaan_reagen)
    {
        DB::transaction(function () use ($penerimaan_reagen) {
            if ($penerimaan_reagen->barang) {
                $penerimaan_reagen->barang->decrement('stock', $penerimaan_reagen->jumlah);
            }
            $penerimaan_reagen->delete();
        });

        return response()->json(['message' => 'Data berhasil dihapus!']);
    }
}
