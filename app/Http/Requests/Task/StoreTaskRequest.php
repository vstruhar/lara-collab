<?php

namespace App\Http\Requests\Task;

use App\Enums\PricingType;
use App\Http\Requests\Concerns\SanitizesRichText;
use App\Services\RichTextSanitizer;
use App\Services\TaskAttachmentService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
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
        $this->sanitizeRichText('description');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string:255'],
            'group_id' => ['required', 'exists:task_groups,id'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:'.RichTextSanitizer::MAX_LENGTH],
            'estimation' => ['nullable'],
            'priority_id' => ['nullable', 'exists:task_priorities,id'],
            'pricing_type' => ['required', 'string', Rule::enum(PricingType::class)],
            'fixed_price' => ['nullable', 'numeric', 'min:0', Rule::when($this->pricing_type === PricingType::FIXED->value, 'present')],
            'due_on' => ['nullable'],
            'hidden_from_clients' => ['required', 'boolean'],
            'billable' => ['required', 'boolean'],
            'subscribed_users' => ['array'],
            'labels' => ['array'],
            'attachments' => ['array'],
            'attachments.*' => [TaskAttachmentService::rule()],
        ];
    }
}
