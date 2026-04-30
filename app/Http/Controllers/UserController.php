<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $header = 'Users Data';
        return view('user.index', compact('header'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $header = 'Users Data';
        $bidangs = Bidang::all();
        return view('user.form', compact('header', 'bidangs'));
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
            'email' => 'required|email|unique:users',
        ]);

        $data = new User();
        $data->name = $request->name;
        $data->email = $request->email;
        $data->position = $request->position;
        $data->password = Hash::make('password');
        $data->bidang_id = $request->bidang ?? 0;

        if ($data->save()) {
            if ($request->signed) {
                $signed = $request->signed;
                $encoded_image = explode(",", $signed)[1];
                $decoded_image = base64_decode($encoded_image);
                Storage::put('signatures/' . $data->id . '.png', $decoded_image);
                $data->signature = 'signatures/' . $data->id . '.png';
                $data->save();
            }

            if ($request->photo) {
                $path = $request->photo->storeAs('profile_photos', $data->id . '.' . $request->photo->getClientOriginalExtension());
                $data->photo = $path;
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
        $header = 'Users Data';
        $user = User::find($id);
        $bidangs = Bidang::all();
        return view('user.form', compact('header', 'user', 'bidangs'));
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
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
        ]);

        $data = User::find($id);
        $data->name = $request->name;
        $data->email = $request->email;
        $data->position = $request->position;
        $data->bidang_id = $request->bidang ?? 0;

        // dd($request->all());
        if ($data->save()) {
            if ($request->signed) {
                Storage::delete($data->signature);

                $encoded_image = explode(",", $request->signed)[1];
                $decoded_image = base64_decode($encoded_image);
                Storage::put('signatures/' . $data->id . '.png', $decoded_image);
                $data->signature = 'signatures/' . $data->id . '.png';
                $data->save();
            }

            if ($request->photo) {
                Storage::delete($data->photo);

                $path = $request->photo->storeAs('profile_photos', $data->id . '.' . $request->photo->getClientOriginalExtension());
                $data->photo = $path;
                $data->save();
            }
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
        $data = User::find($id);
        Storage::delete($data->photo, $data->signature);
        $data->delete();
        return response()->json(['status' => 1, 'msg' => 'Deleted successfully']);
    }

    public function dt_users()
    {
        $data = User::with('bidang')->where('level', '<>', 'admin')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($data) {
                return $data->created_at->isoFormat('D MMM Y');
            })
            ->addColumn('photo', function ($data) {
                if ($data->photo) {
                    if (Storage::exists($data->photo)) {
                        return '<img src="' . Storage::url($data->photo) . '" width="40" />';
                    }
                    return '<span class="alert-danger p-1">File not found</span>';
                }
                return '<img src="' . Storage::url('noimage.webp') . '" width="40" />';
            })
            ->addColumn('signature', function ($data) {
                if ($data->signature) {
                    if (Storage::exists($data->signature)) {
                        return '<img src="' . Storage::url($data->signature) . '" width="40" />';
                    }
                    return '<span class="alert-danger p-1">File not found</span>';
                }
                return '<span class="alert-danger p-1">sign isn\'t available</span>';
            })
            ->addColumn('position', function ($data) {
                return $data->position === 'penyerah' ? 'petugas gudang' : $data->position;
            })
            ->addColumn('bidang.name', function ($data) {
                return $data->bidang->name ?? '-';
            })
            ->addColumn('actions', function ($data) {
                $actions = '';
                $actions .= '<a href="' . route('users.edit', $data->id) . '" class="edit mr-3" title="Edit"><i class="zmdi zmdi-edit text-info"></i></a>';
                $actions .= '<a href="#" value="a1" class="delete" title="Delete"><i class="zmdi zmdi-close text-danger"></i></a>';
                return $actions;
            })
            ->rawColumns(['actions', 'photo', 'signature'])
            ->toJson();
    }

    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature_path' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Hapus signature lama jika ada
        if ($user->signature && Storage::disk('public')->exists($user->signature)) {
            Storage::disk('public')->delete($user->signature);
        }

        $signatureData = $request->input('signature_path');

        if (str_starts_with($signatureData, 'data:')) {
            $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
            $decoded = base64_decode($signatureData);
        } else {
            $decoded = base64_decode($signatureData);
        }

        if ($decoded === false) {
            return response()->json([
                'message' => 'Invalid signature data',
            ], 422);
        }

        $filename = 'signature-' . time() . '.png';
        $path = 'signatures/' . $filename;

        Storage::disk('public')->put($path, $decoded); // ← eksplisit disk public

        $user->signature = $path;
        $user->save();

        return response()->json([
            'message' => 'Signature updated successfully',
        ]);
    }
}
