<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountBillingInfoRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:32', 'regex:/^[A-Za-z0-9\-\.\s]+$/'],
            'billing_address' => ['nullable', 'string', 'max:255'],
            'billing_postal_code' => ['nullable', 'string', 'max:16'],
            'billing_city' => ['nullable', 'string', 'max:120'],
            'billing_country' => ['nullable', 'string', 'size:2'],
        ];
    }
}
