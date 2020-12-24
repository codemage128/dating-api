<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizRequest extends FormRequest
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
            'user_id' => 'required|integer',
            'answer_1' => 'required|boolean',
            'answer_2' => 'required|boolean',
            'answer_3' => 'required|boolean',
            'answer_4' => 'required|boolean',
        ];
    }
}
