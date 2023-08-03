<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiUserRequest;
use App\Http\Requests\UpdateApiUserRequest;
use App\Http\Resources\UserResource;
use App\Models\ApiUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ApiUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $data = ApiUser::where('name', 'like', '%' . $name_query . '%');

        if ($limit_query) { //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
            $data = $data->limit($limit_query)->latest()->get();
        } else {
            $data = $data->latest()->paginate($value_per_page_query);
            //add query string to all response links (KALAU MEMANG ADA QUERY STRING NYA SAAT PERTAMA FETCH DATA)
            $data->appends(['value_per_page' => $value_per_page_query]);
            $data->appends(['name' => $name_query]);
        }

        return UserResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreApiUserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreApiUserRequest $request)
    {
        $validated = $request->validated();
        

        $data = new ApiUser();
        $data->name = $validated['name'];
        $data->email = $validated['email'];
        $data->bidang_id = $validated['bidang_id'];
        $data->position = $validated['position'];
        
        // MENYIMPAN SIGNATURE
        $signatureData = $validated['signature'];
        
        $filename = 'signature-' . time() . '.png';
        $path = 'signatures/'.$filename;
        Storage::put($path , file_get_contents($signatureData));
        
        $data->signature = $path;

        // MENYIMPAN FOTO
        $photoData = $validated['photo'];
        
        $filenamePhoto = 'photo-' . time() . '.png';
        $photoPath = 'profile_photos/'.$filenamePhoto;
        Storage::put($photoPath , file_get_contents($photoData));
        
        $data->photo = $photoPath;
        $data->password = Hash::make('password');

        $data->save();

        return response()->json(['msg' => 'Data berhasil tersimpan!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApiUser  $apiUser
     * @return \Illuminate\Http\Response
     */
    public function show(ApiUser $apiUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateApiUserRequest  $request
     * @param  \App\Models\ApiUser  $apiUser
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateApiUserRequest $request, ApiUser $apiUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiUser  $apiUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiUser $apiUser)
    {
        //
    }
}
