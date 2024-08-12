<?php

namespace App\Http\Requests\ClientCompany;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientCompanyRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'address' => 'string|nullable',
            'postal_code' => 'string|nullable',
            'city' => 'string|nullable',
            'country_id' => 'integer|nullable',
            'currency_id' => 'integer|nullable',
            'email' => 'email:rfc,dns|nullable',
            'phone' => 'string|nullable',
            'web' => 'string|nullable',
            'iban' => 'string|nullable',
            'swift' => 'string|nullable',
            'business_id' => 'string|nullable',
            'tax_id' => 'string|nullable',
            'vat' => 'string|nullable',
            'clients' => 'array|nullable',
        ];
    }
}
