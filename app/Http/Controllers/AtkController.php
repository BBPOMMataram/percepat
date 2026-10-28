<?php

namespace App\Http\Controllers;

use App\Models\Atk;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AtkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $header = 'Data ATK';
        return view('barang_atk.index', compact('header'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $header = 'Data ATK';
        return view('barang_atk.form', compact('header'));
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

        $data = new Atk();
        $data->name = $request->name;
        $data->satuan = $request->satuan;
        $data->stock = $request->stock;
        $data->description = $request->description;
        $data->save();

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is added successfully!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $header = 'Data ATK';
        $editeddata = Atk::find($id);
        return view('barang_atk.form', compact('header', 'editeddata'));
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

        $data = Atk::find($id);
        $data->name = $request->name;
        $data->satuan = $request->satuan;
        $data->stock = $request->stock;
        $data->description = $request->description;
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
        Atk::destroy($id);
        return response()->json(['status' => 1, 'msg' => 'Deleted successfully']);
    }

    public function dt_barang_atk()
    {
        $data = Atk::all();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($data) {
                $actions = '';
                if (auth()->user()->level === 'admin') {
                    $actions .= '<a href="' . route('atk.edit', $data->id) . '" class="edit mr-3" title="Edit"><i class="zmdi zmdi-edit text-info"></i></a>';
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
            ->rawColumns(['actions', 'jumlahpermintaan', 'addBtn', 'msds'])
            ->toJson();
    }

    public function getDataAtk(Request $request)
    {
        $value_per_page = $request->query('value_per_page', 10);
        $name_query = $request->query('name');
        $has_stock = $request->query('has_stock');

        $query = Atk::query();

        if ($name_query) {
            $query->where('name', 'like', '%' . $name_query . '%');
        }

        if ($has_stock === '1' || $has_stock === 'true' || !$request->has('include_zero_stock')) {
            $query->where('stock', '>', 0);
        }

        $data = $query->paginate($value_per_page);

        //add query string to all response links
        $data->appends($request->query());

        return response()->json($data);
    }

    public function getDetailAtk($id)
    {
        $atk = Atk::find($id);
        if (!$atk) {
            return response()->json(['message' => 'ATK tidak ditemukan'], 404);
        }
        return response()->json([
            'id' => $atk->id,
            'name' => $atk->name,
            'code' => 'ATK-' . str_pad($atk->id, 4, '0', STR_PAD_LEFT),
            'stock' => $atk->stock,
            'satuan' => $atk->satuan,
            'expired' => null,
            'created_at' => $atk->created_at,
            'updated_at' => $atk->updated_at,
        ]);
    }
}

