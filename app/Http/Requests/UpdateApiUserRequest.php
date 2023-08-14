<?php

namespace App\Http\Requests;

use App\Models\ApiUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateApiUserRequest extends FormRequest
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
            'email' => ['required','email', 'max:255', Rule::unique(ApiUser::class)->ignore($this->user)],
            'bidang_id' => 'required',
            'position' => 'required',
            'signature' => 'nullable',
            'photo' => 'nullable',
        ];
    }
}
