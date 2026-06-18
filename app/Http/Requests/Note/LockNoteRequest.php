<?php

namespace App\Http\Requests\Note;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LockNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'passcode' => ['required', 'string', 'min:4'],
            'content' => ['nullable', 'string'],
        ];
    }
}
