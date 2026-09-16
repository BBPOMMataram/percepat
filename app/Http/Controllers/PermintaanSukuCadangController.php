<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\PermintaanListSukuCadang;
use Illuminate\Http\Request;

class PermintaanSukuCadangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $nameQuery = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Permintaan::with(['peminta', 'status', 'bidang', 'bidang.user', 'katim', 'penyerah'])
            ->where('jenis', 'suku_cadang');

        if ($nameQuery) {
            $query->whereHas('permintaanListSukuCadang.sukuCadang', function ($q) use ($nameQuery) {
                $q->where('name', 'like', '%' . $nameQuery . '%');
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('tgl_permintaan', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);
        } elseif ($startDate) {
            $query->whereDate('tgl_permintaan', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('tgl_permintaan', '<=', $endDate);
        }

        $query->latest();

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            'name' => $nameQuery,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return response()->json($data);
    }

    public function show($id)
    {
        $data = Permintaan::with(['peminta', 'status', 'bidang', 'bidang.user', 'katim', 'penyerah'])
            ->where('jenis', 'suku_cadang')
            ->find($id);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $data = new PermintaanListSukuCadang();
        $data->permintaan_id = $request->permintaan_id;
        $data->suku_cadang_id = $request->suku_cadang_id;
        $data->jumlahpermintaan = $request->jumlahpermintaan ?? $request->jumlah;
        $data->save();

        return response()->json(['status' => 1, 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = PermintaanListSukuCadang::find($id);
        $data->permintaan_id = $request->permintaan_id;
        $data->suku_cadang_id = $request->suku_cadang_id;
        $data->jumlahpermintaan = $request->jumlahpermintaan ?? $request->jumlah;
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
