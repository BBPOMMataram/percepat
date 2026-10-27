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
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $userObj = User::where('external_user_id', $request->query('katim_id'))->first();
        $katim_id = $userObj ? $userObj->id : 0;
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

    public function index_suku_cadang(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $userObj = User::where('external_user_id', $request->query('katim_id'))->first();
        $katim_id = $userObj ? $userObj->id : 0;
        $is_kabagtu = $request->query('is_kabagtu', 0);

        $query = Permintaan::with(['peminta', 'status', 'bidang', 'bidang.user', 'katim', 'penyerah'])
            ->where(function ($q) {
                $q->where('jenis', 'suku_cadang')->orWhere('jenis', 'Suku Cadang');
            })
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

    public function verif_katim(Request $request, $id) // verif katim
    {
        $permintaan = Permintaan::with('katim')->findOrFail($id);
        if ($permintaan->status_id != 1) {
            return response()->json([
                'message' => 'Permintaan ini sudah diverifikasi sebelumnya.'
            ], 400);
        }

        if ($request->filled('user')) {
            $reqUser = $request->input('user');
            $roleLevel = data_get($reqUser, 'role.level');
            $isSuperAdmin = $roleLevel === 'superadmin' || data_get($reqUser, 'is_admin');
            $extUserId = data_get($reqUser, 'id');
            $userObj = $extUserId ? User::where('external_user_id', $extUserId)->first() : null;
            $intUserId = $userObj ? $userObj->id : null;

            $isKatim = ($permintaan->katim_selected && $intUserId && $permintaan->katim_selected == $intUserId)
                || ($permintaan->katim && $extUserId && $permintaan->katim->external_user_id == $extUserId);

            if (!$isSuperAdmin && !$isKatim) {
                return response()->json([
                    'message' => 'Anda tidak memiliki hak akses untuk memverifikasi permintaan ini sebagai KaTim.'
                ], 403);
            }
        }

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
        $userData = $request->user;
        $groupJabatanId = data_get($userData, 'employee.group_jabatan_id') ?? data_get($userData, 'group_jabatan_id');
        $roleLevel = data_get($userData, 'role.level');
        $isAdmin = data_get($userData, 'is_admin') || $roleLevel === 'superadmin';

        if ($groupJabatanId != 2 && !$isAdmin) {
            return response()->json([
                'message' => 'Hanya Kepala Bagian Tata Usaha (Kabag TU) yang berwenang melakukan verifikasi ini.'
            ], 403);
        }

        $permintaan = Permintaan::findOrFail($id);
        if ($permintaan->status_id != 2) {
            return response()->json([
                'message' => 'Permintaan ini belum diverifikasi oleh KaTim atau sudah disetujui sebelumnya.'
            ], 400);
        }

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

        $realisasi = $request->realisasi; // array

        if ($permintaan->jenis === 'suku_cadang') {
            $listSukuCadang = \App\Models\PermintaanListSukuCadang::where('permintaan_id', $permintaan->id)->get();
            DB::transaction(function () use ($listSukuCadang, $realisasi) {
                foreach ($listSukuCadang as $key => $item) {
                    $val = isset($realisasi[$key]) ? $realisasi[$key] : $item->jumlahpermintaan;
                    $item->jumlahrealisasi = $val;
                    $item->save();

                    $barang = \App\Models\SukuCadang::lockForUpdate()->find($item->suku_cadang_id);
                    if ($barang && $barang->stock < $val) {
                        throw new \Exception("Stock suku cadang tidak mencukupi");
                    }
                    if ($barang) {
                        $barang->decrement('stock', $val);
                    }
                }
            });
        } else {
            $listBarang = PermintaanListAtk::where('permintaan_id', $permintaan->id)->get();
            DB::transaction(function () use ($listBarang, $realisasi) {
                foreach ($listBarang as $key => $item) {
                    $val = isset($realisasi[$key]) ? $realisasi[$key] : $item->jumlahpermintaan;
                    $item->jumlahrealisasi = $val;
                    $item->save();

                    $barang = ApiAtk::lockForUpdate()->find($item->atk_id);

                    if ($barang && $barang->stock < $val) {
                        throw new \Exception("Stock tidak mencukupi");
                    }

                    if ($barang) {
                        $barang->decrement('stock', $val);
                    }
                }
            });
        }

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
