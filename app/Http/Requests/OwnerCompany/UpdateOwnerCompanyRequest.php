<?php

namespace App\Http\Requests\OwnerCompany;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateOwnerCompanyRequest extends FormRequest
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
            'logo' => [
                File::image()
                    ->max(12 * 1024)
                    ->dimensions(Rule::dimensions()->ratio(15 / 4)),
                'nullable',
            ],
        ];
    }
}
