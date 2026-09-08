<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\SukuCadang;
use Illuminate\Http\Request;

class SukuCadangAdminController extends Controller
{
    // UNTUK crud ADMIN PERCEPAT
    function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');

        $query = SukuCadang::where('name', 'like', '%' . $name_query . '%')
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

        SukuCadang::create($request->only('name', 'stock', 'satuan'));
        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, SukuCadang $sukuCadang)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer',
            'satuan' => 'required|string|max:50',
        ]);

        $sukuCadang->update($request->only('name', 'stock', 'satuan'));
        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function destroy(SukuCadang $sukuCadang)
    {
        $sukuCadang->delete();
        return response()->json(['message' => 'Data berhasil dihapus!'], 204);
    }
}