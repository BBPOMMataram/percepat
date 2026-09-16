<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\PermintaanListSukuCadang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $this->validate($request, [
            'pemohon' => ['required'],
            'createdAt' => ['required'],
            'listBarang' => ['required'],
            'katimId' => ['required'],
        ]);

        $listBarang = $request->listBarang;
        if (!$listBarang) {
            return response()->json(['status' => 0, 'msg' => 'Barang tidak boleh kosong!'], 400);
        }

        DB::transaction(function () use ($request, $listBarang) {
            $pemohon = $request->pemohon;

            $data = new Permintaan();

            $data->jenis = 'suku_cadang';
            $data->bidang_id = null;
            if (!$pemohon['employee']['fungsi_id']) {
                throw new \Exception('Anda belum memilih fungsi atau bidang di profile Anda. Silakan lengkapi data tersebut untuk dapat membuat permintaan.');
            }
            $data->bidang_id_auth_external = $pemohon['employee']['fungsi_id'];
            $data->bidang_name_auth_external = $pemohon['employee']['fungsi']['name'];
            $data->katim_selected = User::where('external_user_id', $request->katimId)->first()->id;
            $userInternalId = User::where('external_user_id', $pemohon['id'])->first()->id;
            $data->created_by = $userInternalId;
            $data->tgl_permintaan = $request->createdAt;

            // isi no urut
            $last_data = Permintaan::latest()->first();
            if ($last_data) {
                if (now()->month !== $last_data->created_at->month) {
                    $data->nourut = 1;
                } else {
                    $data->nourut = $last_data->nourut + 1;
                }
            } else {
                $data->nourut = 1;
            }

            $data->save();

            // STORE LIST BARANG
            foreach ($listBarang as $value) {
                $newInventory = new PermintaanListSukuCadang();
                $newInventory->permintaan_id = $data->id;
                $newInventory->suku_cadang_id = $value['id'];
                $newInventory->jumlahpermintaan = $value['jumlah'];
                $newInventory->keterangan = $value['keterangan'];

                $newInventory->save();
            }
        });

        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'pemohon'    => ['required'],
            'createdAt'  => ['required'],
            'listBarang' => ['required'],
            'katimId'    => ['required'],
        ]);

        $listBarang = $request->listBarang;
        if (!$listBarang) {
            return response()->json(['status' => 0, 'msg' => 'Barang tidak boleh kosong!'], 400);
        }

        $data = Permintaan::findOrFail($id);

        DB::transaction(function () use ($request, $listBarang, $data) {
            $pemohon = $request->pemohon;

            $data->bidang_id_auth_external    = $pemohon['employee']['fungsi_id'];
            $data->bidang_name_auth_external  = $pemohon['employee']['fungsi']['name'];
            $data->katim_selected             = User::where('external_user_id', $request->katimId)->first()->id;
            $userInternalId                   = User::where('external_user_id', $pemohon['id'])->first()->id;
            $data->created_by                 = $userInternalId;
            $data->tgl_permintaan             = $request->createdAt;

            $data->status_id = 1;
            $data->save();

            // Delete old list and recreate
            PermintaanListSukuCadang::where('permintaan_id', $data->id)->delete();

            foreach ($listBarang as $value) {
                $newInventory = new PermintaanListSukuCadang();
                $newInventory->permintaan_id = $data->id;
                $newInventory->suku_cadang_id = $value['id'];
                $newInventory->jumlahpermintaan = $value['jumlah'];
                $newInventory->keterangan = $value['keterangan'];

                $newInventory->save();
            }
        });

        return response(['status' => 1, 'msg' => 'Data berhasil diperbarui!']);
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
