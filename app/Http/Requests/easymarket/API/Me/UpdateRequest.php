<?php

namespace App\Http\Requests\easymarket\API\Me;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|max:255',
            'postal_code' => 'sometimes|required|regex:/\d{7}/',
            'address' => 'sometimes|required|max:255',
            'tel' => 'sometimes|required|regex:/\d{9,11}/',
            'nickname' => 'sometimes|required|max:255',
            'description' => 'sometimes|required|max:1000',
            'profile_image' => 'sometimes|required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     * 
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'postal_code.regex' => '郵便番号はハイフンなしの7桁の数字を入力してください。',
            'tel.regex' => '電話番号はハイフンなしの9~11桁の数字を入力してください。',
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
            'name' => '氏名',
            'postal_code' => '郵便番号',
            'address' => '住所',
            'tel' => '電話番号',
            'nickname' => 'ニックネーム',
            'description' => '紹介文',
            'profile_image' => 'プロフィール画像',
        ];
    }
}
