<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\ApiReagen;
use Illuminate\Http\Request;

class ReagenAdminController extends Controller
{
    // UNTUK crud ADMIN PERCEPAT
    function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');

        $query = ApiReagen::where('name', 'like', '%' . $name_query . '%')
            ->orderBy('name', 'asc');

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            'name' => $name_query
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
            'lokasi' => 'nullable|string|max:255',
        ]);

        ApiReagen::create($request->only('name', 'stock', 'satuan', 'expired', 'lokasi'));
        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, ApiReagen $reagen)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'satuan' => 'required|string|max:50',
            'expired' => 'nullable|date',
            'lokasi' => 'nullable|string|max:255',
        ]);

        $reagen->update($request->only('name', 'stock', 'satuan', 'expired', 'lokasi'));
        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function show($id)
    {
        $reagen = ApiReagen::findOrFail($id);
        $latestPermintaanList = \App\Models\PermintaanList::with(['permintaan.peminta', 'permintaan.bidang'])
            ->where('barang_id', $id)
            ->whereHas('permintaan', function ($q) {
                $q->where('status_id', 4);
            })
            ->latest('id')
            ->first();

        if (!$latestPermintaanList) {
            $latestPermintaanList = \App\Models\PermintaanList::with(['permintaan.peminta', 'permintaan.bidang'])
                ->where('barang_id', $id)
                ->latest('id')
                ->first();
        }

        $permintaan = $latestPermintaanList?->permintaan;
        $diterimaTanggal = $permintaan?->tgl_penyerahan 
            ?? $permintaan?->updated_at 
            ?? $permintaan?->tgl_permintaan 
            ?? $reagen->created_at;
        $reagen->diterima_tanggal = $diterimaTanggal ? \Carbon\Carbon::parse($diterimaTanggal)->format('Y-m-d') : null;
        $reagen->diterima_penerima = $permintaan?->peminta?->name ?? 'Petugas Laboratorium';
        $reagen->diterima_lab = $permintaan?->bidang?->name ?? $permintaan?->bidang_name_auth_external ?? null;
        return response()->json($reagen);
    }

    function destroy(ApiReagen $reagen)
    {
        $reagen->delete();
        return response()->json(['message' => 'Data berhasil dihapus!'], 204);
    }
}
