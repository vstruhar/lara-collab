<?php

namespace App\Http\Requests\Note;

use App\Http\Requests\Concerns\SanitizesRichText;
use App\Services\RichTextSanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LockNoteRequest extends FormRequest
{
    use SanitizesRichText;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeRichText('content');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'passcode' => ['required', 'string', 'min:4'],
            'content' => ['nullable', 'string', 'max:'.RichTextSanitizer::MAX_LENGTH],
        ];
    }
}
