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
            'barangId' => 'required',
            'jumlah' => 'required|integer',
            'vendor' => 'required|string|max:255',
            'tglTerima' => 'required|date',
            'expired' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {
            $barang = Barang::where('name', $request->barangNama)
                ->where('expired', $request->expired)
                ->first();

            if ($barang) {
                $barang->increment('stock', $request->jumlah);
            } else {
                $barang = Barang::create([
                    'name' => $request->barangNama,
                    'satuan' => $request->barangsatuan,
                    'stock' => $request->jumlah,
                    'expired' => $request->expired
                ]);
            }

            Pembelian::create([
                'barangs_id' => $barang->id,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, ApiReagen $reagen)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'satuan' => 'required|string|max:50',
            'expired' => 'nullable|date',
        ]);

        $reagen->update($request->only('name', 'stock', 'satuan', 'expired'));
        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function destroy(Pembelian $penerimaan_reagen)
    {
        $penerimaan_reagen->barang()->decrement('stock', (int) $penerimaan_reagen->jumlah);
        $penerimaan_reagen->delete();

        return response()->json(['message' => 'Data berhasil dihapus!']);
    }
}
