<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $header = 'Data Reagen';
        return view('barang.index', compact('header'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $header = 'Data Barang';
        return view('barang.form', compact('header'));
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
            'name' => 'required',
            'satuan' => 'required',
            'stock' => 'numeric|min:0',
        ]);

        $data = new Barang();
        $data->name = $request->name;
        $data->satuan = $request->satuan;
        $data->expired = $request->expired;
        $data->stock = $request->stock;
        $data->msds = $request->msds;
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $header = 'Data Barang';
        $editeddata = Barang::find($id);
        return view('barang.form', compact('header', 'editeddata'));
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
            'name' => 'required',
            'satuan' => 'required',
            'stock' => 'numeric|min:0',
        ]);

        $data = Barang::find($id);
        $data->name = $request->name;
        $data->satuan = $request->satuan;
        $data->expired = $request->expired;
        $data->stock = $request->stock;
        $data->msds = $request->msds;
        $data->save();

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Barang::destroy($id);
        return response()->json(['status' => 1, 'msg' => 'Deleted successfully']);
    }

    public function dt_barang()
    {
        $data = Barang::all();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($data) {
                $actions = '';
                if (auth()->user()->level === 'admin') {
                    $actions .= '<a href="' . route('reagen.edit', $data->id) . '" class="edit mr-3" title="Edit"><i class="zmdi zmdi-edit text-info"></i></a>';
                    $actions .= '<a href="#" value="a1" class="delete" title="Delete"><i class="zmdi zmdi-close text-danger"></i></a>';
                }
                return $actions;
            })
            ->addColumn('expired', function ($data) {
                return $data->expired ? $data->expired->isoFormat('D MMM Y') : null;
            })
            ->addColumn('jumlahpermintaan', function ($data) {
                return '<input type="number" value="0" min="0" name="jumlahpermintaan" class="w-50" />';
            })
            ->addColumn('addBtn', function ($data) {
                $actions = '<a href="#" value="a1" class="add" title="Add"><i class="zmdi zmdi-check text-danger"></i></a>';
                return $actions;
            })
            ->addColumn('msds', function ($data) {
                return $data->msds ? '<a href="' . $data->msds . '" target="_blank"><i class="zmdi zmdi-link text-success"></i></a>' : null;
            })
            ->rawColumns(['actions', 'jumlahpermintaan', 'addBtn', 'msds'])
            ->toJson();
    }

    public function dt_barang_tanpalogin()
    {
        $data = Barang::all();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('expired', function ($data) {
                return $data->expired ? $data->expired->isoFormat('D MMM Y') : null;
            })
            ->addColumn('msds', function ($data) {
                return $data->msds ? '<a href="' . $data->msds . '" target="_blank"><i class="zmdi zmdi-link text-success"></i></a>' : null;
            })
            ->rawColumns(['msds'])
            ->toJson();
    }

    // ==============FOR API=============
    function getDataReagen(Request $request)
    {
        $value_per_page = $request->query('value_per_page', 10);
        $name_query = $request->query('name');
        $expiring_months = $request->query('expiring_months');
        $has_stock = $request->query('has_stock');
        $expired_status = $request->query('expired_status');

        $query = Barang::query();

        if ($name_query) {
            $query->where('name', 'like', '%' . $name_query . '%');
        }

        if ($expired_status === 'expired') {
            $query->whereNotNull('expired')
                  ->whereDate('expired', '<', now());
        } elseif ($expired_status === 'expiring_soon') {
            $months = $request->filled('expiring_months') ? (int) $expiring_months : 3;
            $query->whereNotNull('expired')
                  ->whereDate('expired', '>=', now())
                  ->whereDate('expired', '<=', now()->addMonths($months));
        } elseif ($request->filled('expiring_months')) {
            $months = (int) $expiring_months;
            $query->whereNotNull('expired')
                  ->whereDate('expired', '<=', now()->addMonths($months));
        }

        if ($has_stock === '1' || $has_stock === 'true' || !$request->has('include_zero_stock')) {
            $query->where('stock', '>', 0);
        }

        if ($expired_status || $request->filled('expiring_months')) {
            $query->orderBy('expired', 'asc');
        }

        $data = $query->paginate($value_per_page);

        //add query string to all response links
        $data->appends($request->query());

        return response()->json($data);
    }

    public function getDetailReagen($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        // Get latest completed and delivered permintaan for this reagen
        $latestPermintaanList = \App\Models\PermintaanList::with(['permintaan.peminta', 'permintaan.bidang'])
            ->where('barang_id', $id)
            ->whereHas('permintaan', function ($q) {
                $q->where('status_id', 4)
                  ->whereNotNull('tgl_penyerahan');
            })
            ->latest('id')
            ->first();

        $permintaan = $latestPermintaanList?->permintaan;
        $diterimaTanggal = $permintaan?->tgl_penyerahan;
        $diterimaPenerima = $permintaan?->peminta?->name;
        $diterimaLab = $permintaan?->bidang?->name ?? $permintaan?->bidang_name_auth_external;

        return response()->json([
            'id' => $barang->id,
            'name' => $barang->name,
            'code' => $barang->code ?? ('RG-' . str_pad($barang->id, 4, '0', STR_PAD_LEFT)),
            'stock' => $barang->stock,
            'satuan' => $barang->satuan,
            'expired' => $barang->expired ? $barang->expired->format('Y-m-d') : null,
            'msds' => $barang->msds,
            'diterima_tanggal' => $diterimaTanggal ? \Carbon\Carbon::parse($diterimaTanggal)->format('Y-m-d') : null,
            'diterima_penerima' => $diterimaPenerima,
            'diterima_lab' => $diterimaLab,
            'created_at' => $barang->created_at,
            'updated_at' => $barang->updated_at,
        ]);
    }
}
