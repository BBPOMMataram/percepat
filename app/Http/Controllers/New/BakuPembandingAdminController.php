<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\BakuPembanding;
use Illuminate\Http\Request;

class BakuPembandingAdminController extends Controller
{
    // CRUD ADMIN PERCEPAT UNTUK BAKU PEMBANDING
    function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');

        $query = BakuPembanding::query();

        if ($name_query) {
            $query->where(function ($q) use ($name_query) {
                $q->where('name', 'like', '%' . $name_query . '%')
                  ->orWhere('kode_baru', 'like', '%' . $name_query . '%')
                  ->orWhere('letak', 'like', '%' . $name_query . '%');
            });
        }

        $query->orderBy('name', 'asc');

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            'name' => $name_query
        ]);

        return response()->json($data);
    }

    function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kode_baru' => 'nullable|string|max:100',
            'letak' => 'nullable|string|max:255',
            'stock' => 'required|integer',
            'satuan' => 'required|string|max:50',
            'expired' => 'nullable|date',
        ]);

        BakuPembanding::create($request->only('code', 'kode_baru', 'name', 'letak', 'stock', 'satuan', 'expired'));
        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, BakuPembanding $baku_pembanding)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kode_baru' => 'nullable|string|max:100',
            'letak' => 'nullable|string|max:255',
            'stock' => 'required|integer',
            'satuan' => 'required|string|max:50',
            'expired' => 'nullable|date',
        ]);

        $baku_pembanding->update($request->only('code', 'kode_baru', 'name', 'letak', 'stock', 'satuan', 'expired'));
        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function show($id)
    {
        $baku_pembanding = BakuPembanding::findOrFail($id);
        $latestPermintaanList = \App\Models\PermintaanListBakuPembanding::with(['permintaan.peminta'])
            ->where('baku_pembanding_id', $id)
            ->whereHas('permintaan', function ($q) {
                $q->where('status_id', 4);
            })
            ->latest('id')
            ->first();

        if (!$latestPermintaanList) {
            $latestPermintaanList = \App\Models\PermintaanListBakuPembanding::with(['permintaan.peminta'])
                ->where('baku_pembanding_id', $id)
                ->latest('id')
                ->first();
        }

        $permintaan = $latestPermintaanList?->permintaan;
        $diterimaTanggal = $permintaan?->tgl_penyerahan 
            ?? $permintaan?->updated_at 
            ?? $permintaan?->tgl_permintaan 
            ?? $baku_pembanding->created_at;
        $baku_pembanding->diterima_tanggal = $diterimaTanggal ? \Carbon\Carbon::parse($diterimaTanggal)->format('Y-m-d') : null;
        $baku_pembanding->diterima_penerima = $permintaan?->peminta?->name ?? 'Petugas Laboratorium';
        return response()->json($baku_pembanding);
    }

    function destroy(BakuPembanding $baku_pembanding)
    {
        $baku_pembanding->delete();
        return response()->json(['message' => 'Data berhasil dihapus!'], 204);
    }
}
