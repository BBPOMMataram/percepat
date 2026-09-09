<?php

namespace App\Http\Controllers;

use App\Models\PermintaanListSukuCadang;
use App\Models\SukuCadang;
use Illuminate\Http\Request;

class PermintaanSukuCadangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $name = $request->query('name');

        $query = PermintaanListSukuCadang::with(['sukuCadang', 'permintaan']);

        if ($name) {
            $query->whereHas('sukuCadang', function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
        }

        $data = $query->latest()->paginate($perPage);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $data = new PermintaanListSukuCadang();
        $data->permintaan_id = $request->permintaan_id;
        $data->suku_cadang_id = $request->suku_cadang_id;
        $data->jumlah = $request->jumlah;
        $data->save();

        return response()->json(['status' => 1, 'data' => $data]);
    }

    public function destroy($id)
    {
        PermintaanListSukuCadang::destroy($id);
        return response()->json(['status' => 1]);
    }

    public function download_permintaan_suku_cadang($permintaan)
    {
        $data = PermintaanListSukuCadang::with(['sukuCadang', 'permintaan'])->where('permintaan_id', $permintaan)->get();
        return response()->json($data);
    }
}
