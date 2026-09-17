<?php

namespace App\Http\Controllers;

use App\Models\ApiUser;
use App\Models\Permintaan;
use App\Models\PermintaanListSukuCadang;
use Barryvdh\DomPDF\Facade as PDF;
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

    public function showListBarang($permintaanId)
    {
        $data = PermintaanListSukuCadang::with(['sukuCadang'])
            ->where('permintaan_id', $permintaanId)
            ->get();
        return response()->json(['status' => 1, 'data' => $data]);
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
                throw new \Exception('Anda belum memilih fungsi atau bidang di profile Anda.');
            }
            $data->bidang_id_auth_external = $pemohon['employee']['fungsi_id'];
            $data->bidang_name_auth_external = $pemohon['employee']['fungsi']['name'];
            $data->katim_selected = ApiUser::where('external_user_id', $request->katimId)->first()->id;
            $userInternalId = ApiUser::where('external_user_id', $pemohon['id'])->first()->id;
            $data->created_by = $userInternalId;
            $data->tgl_permintaan = $request->createdAt;

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
            $data->katim_selected             = ApiUser::where('external_user_id', $request->katimId)->first()->id;
            $userInternalId                   = ApiUser::where('external_user_id', $pemohon['id'])->first()->id;
            $data->created_by                 = $userInternalId;
            $data->tgl_permintaan             = $request->createdAt;

            $data->status_id = 1;
            $data->save();

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
        PermintaanListSukuCadang::where('permintaan_id', $id)->delete();
        Permintaan::destroy($id);
        return response()->json(['status' => 1]);
    }

    public function download_permintaan_suku_cadang($permintaanId)
    {
        $datapermintaan = Permintaan::find($permintaanId);
        $datapermintaanlist = PermintaanListSukuCadang::with('sukuCadang')->where('permintaan_id', $permintaanId)->get();
        $penyerah = $datapermintaan->penyerah_id ? ApiUser::find($datapermintaan->penyerah_id) : null;
        $kasub = ApiUser::where('position', 'kasubbagumum')->first();
        $pemohon = ApiUser::find($datapermintaan->created_by);
        $kabid = ApiUser::find($datapermintaan->katim_selected);

        function pdfSignatureSukuCadang($model)
        {
            $signature = $model?->getRawOriginal('signature');
            if ($signature && file_exists(public_path('storage/' . $signature))) {
                return public_path('storage/' . $signature);
            }
            return public_path('vendor/assets/images/image-not-found.webp');
        }

        $penyerahSignature = pdfSignatureSukuCadang($penyerah);
        $kasubSignature    = pdfSignatureSukuCadang($kasub);
        $pemohonSignature  = pdfSignatureSukuCadang($pemohon);
        $kabidSignature    = pdfSignatureSukuCadang($kabid);

        $logobpom = 'storage/bpomri.jpg';
        $pdf = PDF::loadView('pdf/permintaan-suku-cadang', compact(
            'datapermintaan',
            'datapermintaanlist',
            'penyerah',
            'kasub',
            'pemohon',
            'kabid',
            'penyerahSignature',
            'kasubSignature',
            'pemohonSignature',
            'kabidSignature',
            'logobpom',
        ));

        return $pdf->download("SPB-SukuCadang-{$permintaanId}.pdf");
    }

    public function exportPdf(Request $request)
    {
        $perPage   = (int) $request->query('per_page', 10);
        $page      = $request->query('page', 1);
        $nameQuery = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $query = Permintaan::with([
            'peminta',
            'status',
            'bidang',
            'bidang.user',
            'katim',
            'penyerah',
            'permintaanListSukuCadang.sukuCadang',
        ])->where('jenis', 'suku_cadang');

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

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $pdf = PDF::loadView('pdf.permintaan-suku-cadang-export', [
            'items'      => $paginated->items(),
            'total'      => $paginated->total(),
            'page'       => $paginated->currentPage(),
            'perPage'    => $paginated->perPage(),
            'nameQuery'  => $nameQuery,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("permintaan-suku-cadang-hal{$page}.pdf");
    }
}
