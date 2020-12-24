<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'name' => 'string',
            'email' => 'email|unique:users,email,'.$this->auth_user,
            'password' => 'min:6',
            'phone' => 'min:10|unique:users,phone,'.$this->auth_user,
            'pronoun' => 'in:she/her,he/him,she/her they/them,he/him they/them,she/her he/him they/them',
            'looking' => 'in:she/her,he/him,she/her they/them,he/him they/them,she/her he/him they/them',
        ];
    }
}
