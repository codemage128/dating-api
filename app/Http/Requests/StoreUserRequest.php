<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'min:10|unique:users,phone',
            'pronoun' => 'required|in:she/her,he/him,she/her they/them,he/him they/them,she/her he/him they/them',
            'looking' => 'required|in:she/her,he/him,she/her they/them,he/him they/them,she/her he/him they/them',
            'birthday' => 'required',
        ];
    }
}
