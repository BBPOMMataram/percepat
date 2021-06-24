<?php

namespace App\Http\Controllers;

use App\Mail\PermohonanEmail;
use App\Models\Barang;
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
        $header = 'Data Permintaan';
        $kabid = User::where('position', 'penyelia')->get();
        return view('permintaan.form', compact('header', 'kabid'));
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
            'bidang' => 'required',
            'kabid_id' => 'required',
        ], [], [
            'kabid_id' => 'Kabid'
        ]);

        $data = new Permintaan();
        $data->bidang = $request->bidang;
        $data->kabid_id = $request->kabid_id;
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
        return view('permintaan.detail', compact( 'data', 'header'));
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
        $kabid = User::where('position', 'penyelia')->get();
        $editeddata = Permintaan::find($id);
        return view('permintaan.form', compact('header', 'editeddata', 'kabid'));
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
            'bidang' => 'required',
            'kabid_id' => 'required',
        ], [], [
            'kabid_id' => 'Kabid'
        ]);

        $data = Permintaan::find($id);
        $data->bidang = $request->bidang;
        $data->kabid_id = $request->kabid_id;
        $data->save();

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is updated successfully!']);
    }

    public function print_permintaan($id)
    {
        $datapermintaan = Permintaan::find($id);
        $datapermintaanlist = PermintaanList::where('permintaan_id', $id)->get();
        $penyerah = User::where('position', 'penyerah')->first();
        $kasub = User::where('position', 'kasubbagumum')->first();
        $pemohon = User::find($datapermintaan->created_by);
        $kabid = User::find($datapermintaan->kabid_id);
        $pdf = PDF::loadView('pdf/permintaan', compact('datapermintaan', 'datapermintaanlist', 'penyerah', 'kasub', 'pemohon', 'kabid'));
        return $pdf->stream();
    }

    public function kabid_accpermintaan($id)
    {
        $datapermintaan = Permintaan::with('kabid', 'peminta')->find($id);
        $datapermintaan->status_id = 2;
        if($datapermintaan->save()){
            $databarang = PermintaanList::with('barang')->where('permintaan_id' ,$datapermintaan->id)->get();
            $kepada = User::where('position', 'penyerah')->first();
            Mail::to($kepada)->send(new PermohonanEmail($datapermintaan, $databarang, $kepada->name));
        }

        return response(['status' => 1, 'msg' => 'Permintaan berhasil diverifikasi!']);
    }

    public function penyerah_accpermintaan($id)
    {
        $datapermintaan = Permintaan::with('kabid', 'peminta')->find($id);
        $datapermintaan->status_id = 3;
        if($datapermintaan->save()){
            $databarang = PermintaanList::with('barang')->where('permintaan_id' ,$datapermintaan->id)->get();
            $kepada = User::where('position', 'kasubbagumum')->first();
            Mail::to($kepada)->send(new PermohonanEmail($datapermintaan, $databarang, $kepada->name));
        }

        return response(['status' => 1, 'msg' => 'Permintaan berhasil diterima!']);
    }

    public function kasubbagumum_accpermintaan($id)
    {
        $datapermintaan = Permintaan::with('kabid', 'peminta')->find($id);
        $datapermintaan->status_id = 4;
        
        if($datapermintaan->save()){
            $permintaanlist = PermintaanList::where('permintaan_id', $id)->get();
            foreach ($permintaanlist as $value) {
                $barang = Barang::find($value->barang_id);
                $barang->stock -= $value->jumlahrealisasi;
                $barang->save();
            }

            $databarang = PermintaanList::with('barang')->where('permintaan_id' ,$datapermintaan->id)->get();
            $kepada = User::where('position', 'penyerah')->first();
            // Mail::to('arfanihidayat@gmail.com')->send(new PermohonanEmail($datapermintaan, $databarang, $kepada));
        }

        return response(['status' => 1, 'msg' => 'Permintaan berhasil diselesaikan!']);
    }

    public function dt_permintaan()
    {
        $data = Permintaan::with('kabid', 'peminta', 'status')->orderByDesc('created_at')->get();
        if (auth()->user()->position === 'penyelia') {
            $data = $data->where('kabid_id', auth()->user()->id);
        } elseif (auth()->user()->position === 'pemohon') {
            $data = $data->where('created_by', auth()->user()->id);
        }
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($data) {
                $actions = '';
                $actions .= '<a href="' . route('permintaan.show', $data->id) . '" title="Detail Data"><i class="zmdi zmdi-eye text-primary"></i></a>';
                
                if(auth()->user()->position === 'pemohon' || auth()->user()->level === 'admin'){
                    $actions .= '<a href="' . route('permintaan.edit', $data->id) . '" class="edit ml-2" title="Edit"><i class="zmdi zmdi-edit text-info"></i></a>';
                }
                
                $actions .= '<a href="' . route('permintaanlist.index', $data->id) . '" class="permintaanlist mx-2" title="List Permintaan"><i class="zmdi zmdi-attachment text-success"></i></a>';
                $actions .= '<a href="' . route('print_permintaan', $data->id) . '" title="Cetak Permintaan" target="_blank"><i class="zmdi zmdi-print text-secondary"></i></a>';
                if(auth()->user()->position === 'penyelia'){
                    $actions .= '<a href="#" title="ACC Permintaan" ><i class="kabidacc zmdi zmdi-check text-danger ml-2"></i></a>';
                }elseif(auth()->user()->position === 'penyerah'){
                    $actions .= '<a href="#" title="ACC Permintaan" ><i class="penyerahacc zmdi zmdi-check text-danger ml-2"></i></a>';
                }elseif(auth()->user()->position === 'kasubbagumum'){
                    $actions .= '<a href="#" title="ACC Permintaan" ><i class="kasubbagumumacc zmdi zmdi-check text-danger ml-2"></i></a>';
                }

                return $actions;
            })
            ->addColumn('tgl_penyerahan', function ($data) {
                return $data->tgl_penyerahan ? $data->tgl_penyerahan->isoFormat('D MMM Y') : null;
            })
            ->addColumn('tgl_permintaan', function ($data) {
                return $data->tgl_permintaan ? $data->tgl_permintaan->isoFormat('D MMM Y') : null;
            })
            ->addColumn('namapeminta', function($data){
                return $data->peminta->name ?? '-' ;
            })
            ->addColumn('kabid.name', function($data){
                return $data->kabid->name ?? '-' ;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function permintaanlist_done($idpermintaan)
    {
        $datapermintaan = Permintaan::with('kabid', 'peminta')->find($idpermintaan);
        $databarang = PermintaanList::with('barang')->where('permintaan_id' ,$datapermintaan->id)->get();
        $kepada = $datapermintaan->kabid->name;
        Mail::to($datapermintaan->kabid)->send(new PermohonanEmail($datapermintaan, $databarang, $kepada));

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

        return response(['status' => 1, 'msg' => 'Data is added successfully!']);
    }

    public function permintaanlist_create($idpermintaan)
    {
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
        $data = PermintaanList::with('barang')
            ->where('permintaan_id', $idpermintaan)
            ->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($data) {
                $actions = '';
                $actions .= '<a href="#" class="delete" title="Delete"><i class="zmdi zmdi-close text-danger mx-2"></i></a>';
                if(auth()->user()->position === 'penyerah' || auth()->user()->level === 'admin' ){
                    $actions .= '<a href="' . route('permintaanlist.edit', [$data->permintaan_id, $data->barang_id]) . '" class="edit" title="Edit"><i class="zmdi zmdi-edit text-info"></i></a>';
                }
                return $actions;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }
}
