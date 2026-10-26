<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\BakuPembanding;
use App\Models\Permintaan;
use App\Models\PermintaanListBakuPembanding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifBakuPembandingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $userObj = User::where('external_user_id', $request->query('katim_id'))->first();
        $katim_id = $userObj ? $userObj->id : 0;
        $is_kabagtu = $request->query('is_kabagtu', 0);

        $query = Permintaan::with(['peminta', 'status', 'bidang', 'bidang.user', 'katim', 'penyerah'])
            ->where('jenis', 'Baku Pembanding')
            ->latest();

        if (!$is_kabagtu) {
            $query->where('katim_selected', $katim_id);
        }

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            'katim_id' => $request->query('katim_id'),
            'is_kabagtu' => $is_kabagtu
        ]);

        return response()->json($data);
    }

    public function verif_katim($id)
    {
        $permintaan = Permintaan::findOrFail($id);
        $permintaan->update([
            'status_id' => 2,
        ]);

        return response()->json([
            'message' => 'Permintaan ' . $permintaan->status->name,
            'data' => $permintaan,
        ]);
    }

    public function verif_kabagtu(Request $request, $id)
    {
        $permintaan = Permintaan::findOrFail($id);
        $userObj = isset($request->user['id']) ? User::where('external_user_id', $request->user['id'])->first() : null;
        $kasubbagumum_id = $userObj ? $userObj->id : null;

        $permintaan->update([
            'status_id' => 3,
            'kasubbagumum_id' => $kasubbagumum_id,
        ]);

        return response()->json([
            'message' => 'Permintaan ' . $permintaan->status->name,
            'data' => $permintaan,
        ]);
    }

    public function verif_petugas(Request $request, $id)
    {
        $permintaan = Permintaan::findOrFail($id);
        $listBarang = PermintaanListBakuPembanding::where('permintaan_id', $permintaan->id)->get();
        $realisasi = $request->realisasi;

        DB::transaction(function () use ($listBarang, $realisasi) {
            foreach ($listBarang as $key => $item) {
                $val = isset($realisasi[$key]) ? $realisasi[$key] : $item->jumlahpermintaan;
                $item->jumlahrealisasi = $val;
                $item->save();

                $barang = BakuPembanding::lockForUpdate()->find($item->baku_pembanding_id);

                if ($barang && $barang->stock < $val) {
                    throw new \Exception("Stock tidak mencukupi untuk item " . $barang->name);
                }

                if ($barang) {
                    $barang->decrement('stock', $val);
                }
            }
        });

        $tglPenyerahan = $request->filled('tgl_penyerahan') ? \Carbon\Carbon::parse($request->tgl_penyerahan) : now();
        $userObj = isset($request->user['id']) ? User::where('external_user_id', $request->user['id'])->first() : null;
        $penyerah_id = $userObj ? $userObj->id : null;

        $permintaan->update([
            'status_id' => 4,
            'penyerah_id' => $penyerah_id,
            'tgl_penyerahan' => $tglPenyerahan,
        ]);

        return response()->json([
            'message' => 'Permintaan ' . $permintaan->status->name,
            'data' => $permintaan,
        ]);
    }
}
