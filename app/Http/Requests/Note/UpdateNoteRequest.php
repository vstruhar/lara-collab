<?php

namespace App\Http\Requests\Note;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNoteRequest extends FormRequest
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
        $isLocked = (bool) ($this->route('note')->is_locked ?? false);

        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'passcode' => [Rule::requiredIf($isLocked), 'nullable', 'string', 'min:4'],
        ];
    }
}
