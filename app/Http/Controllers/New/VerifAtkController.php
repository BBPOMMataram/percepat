<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\ApiAtk;
use App\Models\PerlengkapanKebersihan;
use App\Models\Permintaan;
use App\Models\PermintaanListAtk;
use App\Models\PermintaanListPerlengkapanKebersihan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifAtkController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $katim_id = User::where('external_user_id', $request->query('katim_id'))->first()->id;
        $is_kabagtu = $request->query('is_kabagtu', 0);

        $query = Permintaan::with(['peminta', 'status', 'bidang', 'bidang.user', 'katim', 'penyerah'])
            ->where('jenis', 'ATK')
            ->latest();

        //kalo verifikator selain kabagtu maka tampilkan permintaan yang katimnya sesuai dengan katim_id yang login
        if (!$is_kabagtu) {
            $query->where('katim_selected', $katim_id);
        }

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            'katim_id' => $request->query('katim_id'),
            'is_kabagtu' => $is_kabagtu
        ]);

        return response()->json($data);
    }

    public function verif_katim($id) // verif katim
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
        $permintaan->update([
            'status_id' => 3,
            'kasubbagumum_id' => User::where('external_user_id', $request->user["id"])->first()->id,
        ]);

        return response()->json([
            'message' => 'Permintaan ' . $permintaan->status->name,
            'data' => $permintaan,
        ]);
    }

    public function verif_petugas(Request $request, $id)
    {
        $permintaan = Permintaan::findOrFail($id);

        $listBarang = PermintaanListAtk::where('permintaan_id', $permintaan->id)->get();


        $realisasi = $request->realisasi; // array

        DB::transaction(function () use ($listBarang, $realisasi) {
            foreach ($listBarang as $key => $item) {
                $item->jumlahrealisasi = $realisasi[$key];
                $item->save();

                $barang = ApiAtk::lockForUpdate()->find($item->atk_id);

                if ($barang->stock < $realisasi[$key]) {
                    throw new \Exception("Stock tidak mencukupi");
                }

                $barang->decrement('stock', $realisasi[$key]);
            }
        });

        $permintaan->update([
            'status_id' => 4,
            'penyerah_id' => User::where('external_user_id', $request->user["id"])->first()->id,
        ]);

        return response()->json([
            'message' => 'Permintaan ' . $permintaan->status->name,
            'data' => $permintaan,
        ]);
    }
}
