<?php

namespace App\Http\Requests\easymarket\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SignupVerifyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|integer|exists:users',
            'expires' => 'required|integer',
            'signature' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * 
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'id' => 'ユーザーID',
            'expires' => '有効期限',
            'signature' => '署名',
        ];
    }
}