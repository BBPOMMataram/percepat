<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $header = 'Profile Data';
        return view('profile', compact('header'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
            'photo' => 'image'
        ]);

        $data = User::find($id);

        if ($request->password) {
            if ($request->password !== $request->confirmpassword) {
                return redirect()->back()->withErrors('Password not match.')->withInput();
            }
            
            $data->password = Hash::make($request->password);
            $data->save();
        }

        $data->name = $request->name;
        $data->email = $request->email;
        $data->save();

        if ($request->photo) {
            Storage::delete($data->photo);

            $path = $request->photo->storeAs('profile_photos', $data->id . '.' . $request->photo->getClientOriginalExtension());
            $data->photo = $path;
            $data->save();
        }

        return redirect()->route('profile.index')->with('msg', 'Data is updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
