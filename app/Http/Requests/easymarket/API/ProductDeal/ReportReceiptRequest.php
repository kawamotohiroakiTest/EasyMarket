<?php

namespace App\Http\Requests\easymarket\API\ProductDeal;

use Illuminate\Foundation\Http\FormRequest;

class ReportReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\Product $product */
        $product = $this->route('product');
        $deal = $product->deal;

        if ($this->user()->cannot('report-receipt-deal', $deal)) {
            return false;
        }

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
            //
        ];
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('受取報告できないユーザーです。');
    }
}
