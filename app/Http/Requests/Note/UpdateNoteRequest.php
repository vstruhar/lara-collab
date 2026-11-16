<?php

namespace App\Http\Requests\Note;

use App\Http\Requests\Concerns\SanitizesRichText;
use App\Services\RichTextSanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNoteRequest extends FormRequest
{
    use SanitizesRichText;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeRichText('content');
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
            'content' => ['nullable', 'string', 'max:'.RichTextSanitizer::MAX_LENGTH],
            'passcode' => [Rule::requiredIf($isLocked), 'nullable', 'string', 'min:4'],
        ];
    }
}
