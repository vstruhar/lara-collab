<?php

namespace App\Http\Requests\Invoice;

use App\Enums\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
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
            'number' => ['required', 'string'],
            'client_company_id' => ['required', 'integer', 'exists:client_companies,id'],
            'projects' => ['required', 'array', 'min:1'],
            'tasks' => ['required', 'array', 'min:1'],
            'type' => ['required', 'string', Rule::in(['default', 'fixed_amount'])],
            'hourly_rate' => $this->type !== Invoice::TYPE_FIXED_AMOUNT->value ? ['required', 'integer', 'min:1'] : [],
            'fixed_amount' => $this->type === Invoice::TYPE_FIXED_AMOUNT->value ? ['required', 'integer', 'min:1'] : [],
            'note' => ['string', 'nullable'],
        ];
    }
}
