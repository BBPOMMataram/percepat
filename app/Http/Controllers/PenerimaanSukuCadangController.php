<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanSukuCadang;
use App\Models\SukuCadang;
use Illuminate\Http\Request;

class PenerimaanSukuCadangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $name = $request->query('name');

        $query = PenerimaanSukuCadang::with('sukuCadang');

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
        $data = new PenerimaanSukuCadang();
        $data->suku_cadang_id = $request->suku_cadang_id;
        $data->jumlah = $request->jumlah;
        $data->save();

        return response()->json(['status' => 1, 'data' => $data]);
    }

    public function destroy($id)
    {
        PenerimaanSukuCadang::destroy($id);
        return response()->json(['status' => 1]);
    }
}
