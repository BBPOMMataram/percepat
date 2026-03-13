<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\PerlengkapanKebersihan;
use Illuminate\Http\Request;

class PerlengkapanKebersihanAdminController extends Controller
{
    // UNTUK crud ADMIN PERCEPAT
    function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');

        $query = PerlengkapanKebersihan::where('name', 'like', '%' . $name_query . '%')
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
        ]);

        PerlengkapanKebersihan::create($request->only('name', 'stock', 'satuan'));
        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, PerlengkapanKebersihan $perlengkapanKebersihan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'satuan' => 'required|string|max:50',
        ]);

        $perlengkapanKebersihan->update($request->only('name', 'stock', 'satuan'));
        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function destroy(PerlengkapanKebersihan $perlengkapanKebersihan)
    {
        $perlengkapanKebersihan->delete();
        return response()->json(['message' => 'Data berhasil dihapus!'], 204);
    }
}
