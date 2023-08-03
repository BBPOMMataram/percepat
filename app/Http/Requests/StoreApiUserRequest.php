<?php

namespace App\Http\Requests;

use App\Models\ApiUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreApiUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'email' => ['required','unique:'. ApiUser::class,'max:255', 'email'],
            'bidang_id' => 'required',
            'position' => 'required',
            'signature' => 'required',
            'photo' => 'nullable',
        ];
    }
}
