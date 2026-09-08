<?php

namespace App\Http\Controllers;

use App\Models\SukuCadang;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SukuCadangController extends Controller
{
    public function index()
    {
        $header = 'Data Suku Cadang';
        return view('barang_suku_cadang.index', compact('header'));
    }

    public function create()
    {
        $header = 'Data Suku Cadang';
        return view('barang_suku_cadang.form', compact('header'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'satuan' => 'required',
            'stock' => 'numeric|min:0',
        ]);

        $data = new SukuCadang();
        $data->name = $request->name;
        $data->satuan = $request->satuan;
        $data->stock = $request->stock;
        $data->description = $request->description;
        $data->save();

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is added successfully!']);
    }

    public function edit($id)
    {
        $header = 'Data Suku Cadang';
        $editeddata = SukuCadang::find($id);
        return view('barang_suku_cadang.form', compact('header', 'editeddata'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'satuan' => 'required',
            'stock' => 'numeric|min:0',
        ]);

        $data = SukuCadang::find($id);
        $data->name = $request->name;
        $data->satuan = $request->satuan;
        $data->stock = $request->stock;
        $data->description = $request->description;
        $data->save();

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is updated successfully!']);
    }

    public function destroy($id)
    {
        SukuCadang::destroy($id);
        return response()->json(['status' => 1, 'msg' => 'Deleted successfully']);
    }

    public function dtBarangSukuCadang()
    {
        $data = SukuCadang::all();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($data) {
                $actions = '';
                if (auth()->user()->level === 'admin') {
                    $actions .= '<a href="' . route('suku-cadang.edit', $data->id) . '" class="edit mr-3" title="Edit"><i class="zmdi zmdi-edit text-info"></i></a>';
                    $actions .= '<a href="#" value="a1" class="delete" title="Delete"><i class="zmdi zmdi-close text-danger"></i></a>';
                }
                return $actions;
            })
            ->addColumn('jumlahpermintaan', function ($data) {
                return '<input type="number" value="0" min="0" name="jumlahpermintaan" class="w-50" />';
            })
            ->addColumn('addBtn', function ($data) {
                $actions = '<a href="#" value="a1" class="add" title="Add"><i class="zmdi zmdi-check text-danger"></i></a>';
                return $actions;
            })
            ->rawColumns(['actions', 'jumlahpermintaan', 'addBtn'])
            ->toJson();
    }

    public function getDataSukuCadang(Request $request)
    {
        $value_per_page = $request->query('value_per_page');
        $name_query = $request->query('name');

        $data = SukuCadang::paginate($value_per_page);

        if ($name_query) {
            $data = SukuCadang::where('name', 'like', '%' . $name_query . '%')->paginate($value_per_page);
        }

        $data->appends(['value_per_page' => $value_per_page]);
        $data->appends(['name' => $name_query]);

        return response()->json($data);
    }
}
