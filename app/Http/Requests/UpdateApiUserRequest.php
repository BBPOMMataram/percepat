<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'name' => 'required|max:255',
            'email' => ['required','email', 'max:255', Rule::unique('products')->ignore($this->product)],
            'bidang_id' => 'required',
            'position' => 'required',
            'signature' => 'required',
            'photo' => 'nullable',
        ];
    }
}
