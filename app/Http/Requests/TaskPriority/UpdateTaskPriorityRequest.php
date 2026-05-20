<?php

namespace App\Http\Requests\TaskPriority;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskPriorityRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => 'required|string|max:255',
            'color' => 'required|string|max:50',
            'order' => 'required|integer|min:1',
        ];
    }
}
