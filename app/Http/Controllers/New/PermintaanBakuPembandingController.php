<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\Permintaan;
use App\Models\PermintaanListBakuPembanding;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class PermintaanBakuPembandingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Permintaan::with(['peminta', 'status', 'bidang', 'bidang.user', 'katim', 'penyerah'])
            ->where('jenis', 'Baku Pembanding');

        if ($name_query) {
            $query->whereHas('permintaanListBakuPembanding.baku_pembanding', function ($q) use ($name_query) {
                $q->where('name', 'like', '%' . $name_query . '%')
                  ->orWhere('kode_baru', 'like', '%' . $name_query . '%')
                  ->orWhere('letak', 'like', '%' . $name_query . '%');
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
            'name' => $name_query,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return response()->json($data);
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
            'permintaanListBakuPembanding.baku_pembanding',
        ])->where('jenis', 'Baku Pembanding');

        if ($nameQuery) {
            $query->whereHas('permintaanListBakuPembanding.baku_pembanding', function ($q) use ($nameQuery) {
                $q->where('name', 'like', '%' . $nameQuery . '%')
                  ->orWhere('kode_baru', 'like', '%' . $nameQuery . '%')
                  ->orWhere('letak', 'like', '%' . $nameQuery . '%');
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

        $pdf = Pdf::loadView('pdf.permintaan-baku-pembanding', [
            'items'      => $paginated->items(),
            'total'      => $paginated->total(),
            'page'       => $paginated->currentPage(),
            'perPage'    => $paginated->perPage(),
            'nameQuery'  => $nameQuery,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("permintaan-baku-pembanding-hal{$page}.pdf");
    }

    public function show($id)
    {
        $data = Permintaan::with([
            'permintaanListBakuPembanding.baku_pembanding',
            'katim',
            'peminta'
        ])->findOrFail($id);

        $katimExternalId = $data->katim?->external_user_id;

        // Reconstruct listBarang
        $listBarang = $data->permintaanListBakuPembanding;

        return response()->json([
            'status' => 1,
            'data'   => [
                'id'         => $data->id,
                'pemohon'    => $data->peminta?->external_user_id,
                'createdAt'  => $data->tgl_permintaan,
                'listBarang' => $listBarang,
                'katimId'    => $katimExternalId,
                'nourut'     => $data->nourut,
                'jenis'      => $data->jenis,
                'created_at' => $data->created_at,
            ],
        ]);
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
            return response()->json(['status' => 1, 'msg' => 'Barang tidak boleh kosong !'], 400);
        }

        DB::transaction(function () use ($request, $listBarang) {
            $pemohon = $request->pemohon;

            $data = new Permintaan();
            $data->jenis = 'Baku Pembanding';
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
                $newInventory = new PermintaanListBakuPembanding();
                $newInventory->permintaan_id = $data->id;
                $newInventory->baku_pembanding_id = $value['id'];
                $newInventory->jumlahpermintaan = $value['jumlah'];
                $newInventory->keterangan = $value['keterangan'] ?? null;
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
            return response()->json(['status' => 1, 'msg' => 'Barang tidak boleh kosong !'], 400);
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

            PermintaanListBakuPembanding::where('permintaan_id', $data->id)->delete();

            foreach ($listBarang as $value) {
                $newInventory = new PermintaanListBakuPembanding();
                $newInventory->permintaan_id    = $data->id;
                $newInventory->baku_pembanding_id = $value['id'];
                $newInventory->jumlahpermintaan = $value['jumlah'];
                $newInventory->keterangan       = $value['keterangan'] ?? null;
                $newInventory->save();
            }
        });

        return response(['status' => 1, 'msg' => 'Data berhasil diperbarui!']);
    }

    public function destroy($id)
    {
        $permintaan = Permintaan::find($id);

        if (!$permintaan) {
            return response()->json(['status' => 0, 'msg' => 'Data tidak ditemukan!'], 404);
        }

        DB::transaction(function () use ($permintaan) {
            PermintaanListBakuPembanding::where('permintaan_id', $permintaan->id)->delete();
            $permintaan->delete();
        });

        return response()->json(['status' => 1, 'msg' => 'Data berhasil dihapus!']);
    }
}
