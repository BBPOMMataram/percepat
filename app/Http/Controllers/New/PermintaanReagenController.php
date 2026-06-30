<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\Permintaan;
use App\Models\PermintaanList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class PermintaanReagenController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Permintaan::with(['peminta', 'status', 'bidang', 'bidang.user', 'katim', 'penyerah'])
            ->where('jenis', 'Reagen dan Bahan Laboratorium Lain');

        if ($name_query) {
            $query->whereHas('permintaanList.barang', function ($q) use ($name_query) {
                $q->where('name', 'like', '%' . $name_query . '%');
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
        $perPage   = $request->query('per_page', 10);
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
            'permintaanList.barang',
        ])->where('jenis', 'Reagen dan Bahan Laboratorium Lain');

        if ($nameQuery) {
            $query->whereHas('permintaanList.barang', function ($q) use ($nameQuery) {
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

        $pdf = Pdf::loadView('pdf.permintaan-reagen', [
            'items'      => $paginated->items(),
            'total'      => $paginated->total(),
            'page'       => $paginated->currentPage(),
            'perPage'    => $paginated->perPage(),
            'nameQuery'  => $nameQuery,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("permintaan-reagen-hal{$page}.pdf");
    }

    public function show($id)
    {
        $data = Permintaan::with([
            'permintaanList.barang',
            // 'createdByUser',
            'katim',
            'peminta'
        ])->findOrFail($id);

        $katimExternalId     = $data->katim?->external_user_id;

        // Reconstruct listBarang
        $listBarang = $data->permintaanList;

        return response()->json([
            'status' => 1,
            'data'   => [
                'id'         => $data->id,
                'pemohon'    => $data->peminta?->external_user_id, // cukup id eksternal saja karena nanti di frontend bisa query lagi untuk dapat data lengkap pemohon
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
            'pemohon' => ['required'], // bukan hanya id pemohon tapi data lengkap pemohon dalam bentuk object
            'createdAt' => ['required'],
            'listBarang' => ['required'],
            'katimId' => ['required'],
        ]);

        $listBarang = $request->listBarang;
        if (!$listBarang) {
            return response()->json(['status' => 1, 'msg' => 'Barang tidak boleh kosong !'], 400);
        }

        DB::transaction(function () use ($request, $listBarang) {
            // DI JSON DECODE AGAR DATA PROPERTY PADA OBJECT(KEY) DAPAT TERBACA BUKAN SEBAGAI STRING
            $pemohon = $request->pemohon;

            $data = new Permintaan();

            $data->jenis = 'Reagen dan Bahan Laboratorium Lain';
            $data->bidang_id = null; //dibuat null untuk menyesuaikan bidang user auth (si mandalika), ini untuk permintaan baru setelah SSO
            if (!$pemohon['employee']['fungsi_id']) {
                throw new \Exception('Anda belum memilih fungsi atau bidang di profile Anda. Silakan lengkapi data tersebut untuk dapat membuat permintaan.');
            }
            $data->bidang_id_auth_external = $pemohon['employee']['fungsi_id']; // sbg ganti nya gunakan fungsi dari user auth external  
            $data->bidang_name_auth_external = $pemohon['employee']['fungsi']['name']; // ini untuk langsung simpan nama bidang juga biar gak ribet join ke tabel bidang
            // $data->kabid_id = $request->kabid_id; // cek dulu kenapa di comment, sepertinya nanti diisi setelah diapprove kabid (untuk menjadi kabid siapa yg approve duluan mgkn karena ada beberapa kabid di satu fungsi)
            $data->katim_selected = User::where('external_user_id', $request->katimId)->first()->id; // karena skrg katim pilih manual jadi simpan katim yang dipilih pemohon (setelah SSO)
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
                $newInventory = new PermintaanList();
                $newInventory->permintaan_id = $data->id; //permintaan id
                $newInventory->barang_id = $value['id'];
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

            $data->status_id = 1; // set status kembali ke "Permohonan" setiap kali data diupdate
            $data->save();

            // HAPUS LIST BARANG LAMA LALU INSERT ULANG
            PermintaanList::where('permintaan_id', $data->id)->delete();

            foreach ($listBarang as $value) {
                $newInventory = new PermintaanList();
                $newInventory->permintaan_id    = $data->id;
                $newInventory->barang_id        = $value['id'];
                $newInventory->jumlahpermintaan = $value['jumlah'];
                $newInventory->keterangan       = $value['keterangan'];

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
            // Hapus list barang terlebih dahulu
            PermintaanList::where('permintaan_id', $permintaan->id)->delete();

            // Hapus permintaan
            $permintaan->delete();
        });

        return response()->json(['status' => 1, 'msg' => 'Data berhasil dihapus!']);
    }
}
