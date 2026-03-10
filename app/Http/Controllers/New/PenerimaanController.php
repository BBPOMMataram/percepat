<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\ApiReagen;
use App\Models\Pembelian;
use Illuminate\Http\Request;

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
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'satuan' => 'required|string|max:50',
            'expired' => 'nullable|date',
        ]);

        ApiReagen::create($request->only('name', 'stock', 'satuan', 'expired'));
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
