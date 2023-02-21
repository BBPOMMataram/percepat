<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use function PHPSTORM_META\type;

class PembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $header = 'Data Penerimaan';
        return view('pembelian.index', compact('header'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $header = 'Data Pembelian';
        $barang = Barang::all();
        return view('pembelian.form', compact('header', 'barang'));
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
            'barang_id' => 'required',
            'jumlah' => 'numeric|min:1',
        ], [], [
            'barang_id' => 'Name'
        ]);

        $data = new Pembelian();
        $data->barangs_id = $request->barang_id;
        $data->expired = $request->expired;
        $data->jumlah = $request->jumlah;
        $data->vendor = $request->vendor;
        $data->created_at = $request->created_at; //new tgl pembelian
        if ($data->save()) {
            $barang = Barang::find($request->barang_id);
            if (isset($barang->expired)) {
                $barangExpired = $barang->expired->format('Y-m-d');
            } else {
                $barangExpired = $barang->expired;
            }
            if ($request->expired === $barangExpired) {
                $barang->stock = $barang->stock + $request->jumlah;
                $barang->save();
            } else {
                $newBarang = new Barang();

                $newBarang->code = $barang->code;
                $newBarang->name = $barang->name;
                $newBarang->satuan = $barang->satuan;
                $newBarang->expired = $request->expired; //untuk data barang baru yang beda expired
                $newBarang->stock = $request->jumlah; //untuk data barang baru yang beda expired
                $newBarang->save();
                $data->barangs_id = $newBarang->id;
                $data->save();
            }
        }

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
        $header = 'Data Pembelian';
        $barang = Barang::all();
        $editeddata = Pembelian::find($id);
        return view('pembelian.form', compact('header', 'editeddata', 'barang'));
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
            'barang_id' => 'required',
            'jumlah' => 'numeric|min:1',
        ], [], [
            'barang_id' => 'Name'
        ]);

        $data = Pembelian::find($id);
        // $data->barangs_id = $request->barang_id;
        $data->expired = $request->expired;
        $data->vendor = $request->vendor;
        if ($data->save()) {
            $barang = Barang::find($data->barangs_id);
            if (isset($barang->expired)) {
                $barangExpired = $barang->expired->format('Y-m-d');
            } else {
                $barangExpired = $barang->expired;
            }

            if ($request->expired === $barangExpired) {
                $barang->stock = $barang->stock - $data->jumlah + $request->jumlah;
                $barang->save();
            } else {
                $otherBarang = Barang::where('name', $barang->name)->get();
                $countItem = count($otherBarang);
                $i = 0;
                if ($otherBarang) {
                    foreach ($otherBarang as $value) {
                        $barang1 = Barang::find($value->id);
                        if (isset($barang1->expired)) {
                            $barangExpired1 = $barang1->expired->format('Y-m-d');
                        } else {
                            $barangExpired1 = $barang1->expired;
                        }

                        if ($request->expired === $barangExpired1) {
                            $barang->stock = $barang->stock - $data->jumlah;
                            $barang1->stock = $barang1->stock + $request->jumlah;
                            $barang->save();
                            $barang1->save();
                            $data->barangs_id = $barang1->id;
                        } else {
                            if (++$i === $countItem) {
                                $newBarang = new Barang();

                                $newBarang->code = $barang->code;
                                $newBarang->name = $barang->name;
                                $newBarang->satuan = $barang->satuan;
                                $newBarang->expired = $request->expired; //untuk data barang baru yang beda expired
                                $newBarang->stock = $request->jumlah; //untuk data barang baru yang beda expired
                                if ($newBarang->save()) {
                                    $barang->stock = $barang->stock - $data->jumlah;
                                    $barang->save();
                                }
                                $data->barangs_id = $newBarang->id;
                            }
                        }
                    }
                } else {
                    $newBarang = new Barang();

                    $newBarang->code = $barang->code;
                    $newBarang->name = $barang->name;
                    $newBarang->satuan = $barang->satuan;
                    $newBarang->expired = $request->expired; //untuk data barang baru yang beda expired
                    $newBarang->stock = $request->jumlah; //untuk data barang baru yang beda expired
                    $newBarang->save();
                    $data->barangs_id = $newBarang->id;
                }
            }

            $data->jumlah = $request->jumlah;
            $data->save();
        }

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
        $data = Pembelian::find($id);

        $barang = Barang::find($data->barangs_id);
        if (isset($barang)) {
            $barang->stock -= $data->jumlah;
            $barang->save();
        }

        $data->delete();

        return response()->json(['status' => 1, 'msg' => 'Deleted successfully']);
    }

    public function dt_pembelian()
    {
        $data = Pembelian::with('barang')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($data) {
                $actions = '';
                if (auth()->user()->level === 'admin') {
                    $actions .= '<a href="' . route('pembelian.edit', $data->id) . '" class="edit mr-3" title="Edit"><i class="zmdi zmdi-edit text-info"></i></a>';
                    $actions .= '<a href="#" value="a1" class="delete" title="Delete"><i class="zmdi zmdi-close text-danger"></i></a>';
                }
                return $actions;
            })
            ->addColumn('expired', function ($data) {
                return $data->expired ? $data->expired->isoFormat('D MMM Y') : null;
            })
            ->addColumn('created_at', function ($data) {
                return $data->created_at ? $data->created_at->isoFormat('D MMM Y') : null;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }
}
