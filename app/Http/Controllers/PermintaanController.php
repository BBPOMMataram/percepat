<?php

namespace App\Http\Controllers;

use App\Mail\PermohonanEmail;
use App\Models\Barang;
use App\Models\Bidang;
use App\Models\Permintaan;
use App\Models\PermintaanList;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class PermintaanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $header = 'Data Permintaan';
        return view('permintaan.index', compact('header'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (auth()->user()->position !== 'pemohon') {
            return redirect()->route('permintaan.index');
        }
        $header = 'Data Permintaan';
        // $kabid = User::where('position', 'penyelia')->get();
        // $bidang = Bidang::all();
        // return view('permintaan.form', compact('header', 'bidang'));
        $data = new Permintaan();
        $user = User::with('bidang')->find(auth()->user()->id);
        $data->bidang_id = $user->bidang->id;
        // $data->kabid_id = $request->kabid_id;
        $data->created_by = $user->id;

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
        return redirect()->route('permintaanlist.index', $data->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'bidang_id' => 'required',
        ]);

        $data = new Permintaan();
        $data->bidang_id = $request->bidang_id;
        // $data->kabid_id = $request->kabid_id;
        $data->created_by = auth()->user()->id;

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

        // return redirect()->route('permintaanlist.index', $data->id);

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is added successfully!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Permintaan::find($id);
        $header = 'Data Permintaan no ' . $data->nourut;
        return view('permintaan.detail', compact('data', 'header'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $header = 'Data Pembelian';
        // $kabid = User::where('position', 'penyelia')->get();
        $bidang = Bidang::all();
        $editeddata = Permintaan::find($id);
        return view('permintaan.form', compact('header', 'editeddata', 'bidang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'bidang_id' => 'required',
        ]);

        $data = Permintaan::find($id);
        $data->bidang_id = $request->bidang_id;
        $data->save();

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is updated successfully!']);
    }

    public function destroy($id)
    {
        PermintaanList::where('permintaan_id', $id)->delete();
        Permintaan::destroy($id);
        return response()->json(['status' => 1, 'msg' => 'Deleted successfully']);
    }

    public function print_permintaan($id)
    {
        $datapermintaan = Permintaan::find($id);
        $datapermintaanlist = PermintaanList::where('permintaan_id', $id)->get();
        $penyerah = User::where('position', 'penyerah')->first();
        $kasub = User::where('position', 'kasubbagumum')->first();
        $pemohon = User::find($datapermintaan->created_by);
        $kabid = User::find($datapermintaan->bidang->user->id);
        $pdf = PDF::loadView('pdf/permintaan', compact('datapermintaan', 'datapermintaanlist', 'penyerah', 'kasub', 'pemohon', 'kabid'));
        return $pdf->stream();
    }

    public function kabid_accpermintaan($id)
    {
        $datapermintaan = Permintaan::with('peminta')->find($id);
        $datapermintaan->status_id = 2;
        if ($datapermintaan->save()) {
            $databarang = PermintaanList::with('barang')->where('permintaan_id', $datapermintaan->id)->get();
            $kepada = User::where('position', 'penyerah')->first();
            Mail::to($kepada)->send(new PermohonanEmail($datapermintaan, $databarang, $kepada->name));
        }

        return response(['status' => 1, 'msg' => 'Permintaan berhasil diverifikasi!']);
    }

    public function penyerah_accpermintaan($id)
    {
        $datapermintaan = Permintaan::with('peminta')->find($id);
        $datapermintaan->status_id = 3;
        if ($datapermintaan->save()) {
            $databarang = PermintaanList::with('barang')->where('permintaan_id', $datapermintaan->id)->get();
            foreach ($databarang as $value) {
                if (!$value->jumlahrealisasi) {
                    $value->jumlahrealisasi = $value->jumlahpermintaan;
                    $value->save();
                }
            }
            $kepada = User::where('position', 'kasubbagumum')->first();
            Mail::to($kepada)->send(new PermohonanEmail($datapermintaan, $databarang, $kepada->name));
        }

        return response(['status' => 1, 'msg' => 'Permintaan berhasil diterima!']);
    }

    public function kasubbagumum_accpermintaan($id)
    {
        $datapermintaan = Permintaan::with('peminta')->find($id);
        $datapermintaan->status_id = 4;

        if ($datapermintaan->save()) {
            $permintaanlist = PermintaanList::where('permintaan_id', $id)->get();
            foreach ($permintaanlist as $value) {
                $barang = Barang::find($value->barang_id);
                $barang->stock -= $value->jumlahrealisasi;
                $barang->save();
            }

            $databarang = PermintaanList::with('barang')->where('permintaan_id', $datapermintaan->id)->get();
            $kepada = User::where('position', 'penyerah')->first();
            // Mail::to('arfanihidayat@gmail.com')->send(new PermohonanEmail($datapermintaan, $databarang, $kepada));
        }

        return response(['status' => 1, 'msg' => 'Permintaan berhasil diselesaikan!']);
    }

    public function dt_permintaan()
    {
        $data = Permintaan::with('bidang.user', 'peminta', 'status')->orderByDesc('created_at')->get();
        if (auth()->user()->position === 'penyelia') {
            $data = $data->where('bidang.user.id', auth()->user()->id);
        } elseif (auth()->user()->position === 'pemohon') {
            $data = $data->where('created_by', auth()->user()->id);
        } elseif (auth()->user()->position === 'penyerah') {
            $data = $data->where('status_id', '>=', 2);
        } elseif (auth()->user()->position === 'kasubbagumum') {
            $data = $data->where('status_id', '>=', 3);
        }
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($data) {
                $actions = '';

                $actions .= '<a href="' . route('permintaan.show', $data->id) . '" title="Detail Data"><i class="zmdi zmdi-eye text-primary"></i></a>';

                // if (auth()->user()->position === 'pemohon' || auth()->user()->level === 'admin') {
                //     $actions .= '<a href="' . route('permintaan.edit', $data->id) . '" class="edit ml-2" title="Edit"><i class="zmdi zmdi-edit text-info"></i></a>';
                // }

                $actions .= '<a href="' . route('permintaanlist.index', $data->id) . '" class="permintaanlist mx-2" title="List Permintaan"><i class="zmdi zmdi-attachment text-success"></i></a>';

                $actions .= '<a href="' . route('print_permintaan', $data->id) . '" title="Cetak Permintaan" target="_blank"><i class="zmdi zmdi-print text-secondary"></i></a>';
                if (auth()->user()->position === 'penyelia') {
                    $actions .= '<a href="#" title="ACC Permintaan" ><i class="kabidacc zmdi zmdi-check text-info ml-2"></i></a>';
                } elseif (auth()->user()->position === 'penyerah') {
                    $actions .= '<a href="#" title="ACC Permintaan" ><i class="penyerahacc zmdi zmdi-check text-info ml-2"></i></a>';
                } elseif (auth()->user()->position === 'kasubbagumum') {
                    $actions .= '<a href="#" title="ACC Permintaan" ><i class="kasubbagumumacc zmdi zmdi-check text-info ml-2"></i></a>';
                }

                if (auth()->user()->position === 'pemohon' || auth()->user()->level === 'admin' && $data->status->id < 4) {
                    $actions .= '<a href="#" class="delete ml-2" title="Delete"><i class="zmdi zmdi-close text-danger"></i></a>';
                }

                return $actions;
            })
            ->addColumn('tgl_penyerahan', function ($data) {
                return $data->tgl_penyerahan ? $data->tgl_penyerahan->isoFormat('D MMM Y') : null;
            })
            ->addColumn('tgl_permintaan', function ($data) {
                return $data->tgl_permintaan ? $data->tgl_permintaan->isoFormat('D MMM Y') : null;
            })
            ->addColumn('namapeminta', function ($data) {
                return $data->peminta->name ?? '-';
            })
            ->addColumn('bidang.user.name', function ($data) {
                return $data->bidang->user->name ?? '-';
            })
            ->addColumn('bidang.name', function ($data) {
                return $data->bidang->name ?? '-';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function permintaanlist_done($idpermintaan)
    {
        $datapermintaan = Permintaan::with('bidang.user', 'peminta')->find($idpermintaan);
        $databarang = PermintaanList::with('barang')->where('permintaan_id', $datapermintaan->id)->get();
        $kepada = $datapermintaan->bidang->user->name;
        Mail::to($datapermintaan->bidang->user)->send(new PermohonanEmail($datapermintaan, $databarang, $kepada));

        return redirect()->route('permintaanlist.index', $datapermintaan->id)->with([
            'data' => $datapermintaan
        ]);
        // return view('permintaanlist.index', compact('header', 'data'));
    }

    public function permintaanlist_index($idpermintaan)
    {
        $data = Permintaan::find($idpermintaan);
        $header = 'List Permintaan no ' . $data->nourut;

        return view('permintaanlist.index', compact('header', 'data'));
    }

    public function permintaanlist_edit($idpermintaan, $idbarang)
    {
        $editeddata = PermintaanList::where('permintaan_id', $idpermintaan)
            ->where('barang_id', $idbarang)->first();
        $header = 'List Permintaan no ' . $editeddata->nourut;
        $barang = Barang::all();
        $data = Permintaan::find($idpermintaan);

        return view('permintaanlist.form', compact('header', 'editeddata', 'barang', 'data'));
    }

    public function permintaanlist_update(Request $request, $idpermintaan, $idbarang)
    {
        $this->validate($request, [
            'jumlahrealisasi' => 'required'
        ]);

        $existingData = PermintaanList::where('permintaan_id', $idpermintaan)
            ->where('barang_id', $idbarang)->first();

        $existingData->jumlahrealisasi = $request->jumlahrealisasi;
        $existingData->save();

        return response(['status' => 1, 'msg' => 'Data is updated successfully!']);
    }

    public function permintaanlist_create($idpermintaan)
    {
        if (auth()->user()->position !== 'pemohon') {
            return redirect()->back();
        }

        $data = Permintaan::find($idpermintaan);
        $header = 'List Permintaan no ' . $data->nourut;
        $barang = Barang::all();

        return view('permintaanlist.form', compact('header', 'barang', 'data'));
    }

    public function permintaanlist_store(Request $request, $idpermintaan)
    {
        $this->validate($request, [
            'barang_id' => 'required',
            'jumlahpermintaan' => 'numeric|min:1',
        ], [], [
            'barang_id' => 'Nama barang'
        ]);

        $existingData = PermintaanList::where('permintaan_id', $idpermintaan)
            ->where('barang_id', $request->barang_id)->first();

        if ($existingData) {
            $existingData->jumlahpermintaan += $request->jumlahpermintaan;
            $existingData->keterangan = $request->keterangan;
            $existingData->save();
        } else {
            $data = new PermintaanList();
            $data->permintaan_id = $idpermintaan;
            $data->barang_id = $request->barang_id;
            $data->jumlahpermintaan = $request->jumlahpermintaan;
            $data->keterangan = $request->keterangan;
            $data->save();
        }

        return response(['status' => 1, 'msg' => 'Data is added successfully!']);
    }

    public function Permintaanlist_destroy($idpermintaan, $idbarang)
    {
        PermintaanList::where('permintaan_id', $idpermintaan)
            ->where('barang_id', $idbarang)->delete();

        return response()->json(['status' => 1, 'msg' => 'Deleted successfully']);
    }

    public function dt_permintaanlist($idpermintaan)
    {
        $data = PermintaanList::with('barang', 'permintaan')
            ->where('permintaan_id', $idpermintaan)
            ->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($data) {
                $actions = '';
                if (auth()->user()->level === 'admin' || auth()->user()->position === 'pemohon') {
                    $actions .= '<a href="#" class="delete" title="Delete"><i class="zmdi zmdi-close text-danger mx-2"></i></a>';
                } elseif (auth()->user()->position === 'penyerah' && $data->permintaan->status_id < 3) {
                    $actions .= '<a href="' . route('permintaanlist.edit', [$data->permintaan_id, $data->barang_id]) . '" class="edit" title="Edit realisasi"><i class="zmdi zmdi-edit text-info"></i></a>';
                }
                return $actions;
            })
            ->addColumn('barang.name', function ($data) {
                return $data->barang->name ?? '<div class="text-danger">item not found</div>';
            })
            ->addColumn('barang.satuan', function ($data) {
                return $data->barang->satuan ?? '<div class="text-danger">item not found</div>';
            })
            ->addColumn('barang.expired', function ($data) {
                if (isset($data->barang->expired)) {
                    return $data->barang->expired->isoFormat('D MMM Y');
                } else {
                    return '-';
                }
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function laporan()
    {
        if (auth()->user()->level !== 'admin') {
            return redirect()->route('dashboard');
        }

        $header = 'Laporan';
        $barang = Barang::all();
        return view('laporan.index', compact('header', 'barang'));
    }

    public function print_laporan($id=0)
    {
        $datapermintaanlist = PermintaanList::with('barang', 'permintaan.peminta', 'permintaan.bidang', 'permintaan.status', 'permintaan')->get();
        if($id){
            $datapermintaanlist = $datapermintaanlist->where('barang_id', $id);
        }
        // $kabid = User::find(auth()->user()->id);
        $pdf = PDF::loadView(
            'pdf/laporan',
            compact(
                'datapermintaanlist',
            )
        );
        return $pdf->stream();
    }

    public function dt_laporan($id=null)
    {
        $data = PermintaanList::with('barang', 'permintaan.peminta', 'permintaan.bidang', 'permintaan.status', 'permintaan')->get();
        if($id){
            $data = $data->where('barang_id', $id);
        }
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('barang.expired', function ($data) {
                if (isset($data->barang->expired)) {
                    return $data->barang->expired ? $data->barang->expired->isoFormat('D MMM Y') : '-';
                }
            })
            ->addColumn('barang.name', function ($data) {
                return $data->barang->name ?? '<div class="text-danger">item not found</div>';
            })
            ->addColumn('barang.satuan', function ($data) {
                return $data->barang->satuan ?? '-';
            })
            ->addColumn('permintaan.peminta.name', function ($data) {
                return $data->permintaan->peminta->name ?? '<div class="text-danger">item not found</div>';
            })
            ->addColumn('permintaan.bidang.name', function ($data) {
                return $data->permintaan->bidang->name ?? '-';
            })
            ->addColumn('permintaan.status.name', function ($data) {
                return $data->permintaan->status->name ?? '-';
            })
            ->addColumn('permintaan.tgl_penyerahan', function ($data) {
                if (isset($data->permintaan->tgl_penyerahan)) {
                    return $data->permintaan->tgl_penyerahan ? $data->permintaan->tgl_penyerahan->isoFormat('D MMM Y') : null;
                }
            })
            ->addColumn('permintaan.tgl_permintaan', function ($data) {
                if (isset($data->permintaan->tgl_permintaan)) {
                    return $data->permintaan->tgl_permintaan ? $data->permintaan->tgl_permintaan->isoFormat('D MMM Y') : null;
                }
            })
            ->rawColumns(['actions'])
            ->toJson();
    }
}
