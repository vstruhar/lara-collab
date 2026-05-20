<?php

namespace App\Http\Requests\Task;

use App\Enums\PricingType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'name' => ['string:255'],
            'group_id' => ['exists:task_groups,id'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable'],
            'estimation' => ['nullable'],
            'priority_id' => ['nullable', 'exists:task_priorities,id'],
            'pricing_type' => ['string', Rule::enum(PricingType::class)],
            'fixed_price' => ['nullable', 'numeric', 'min:0'],
            'due_on' => ['nullable'],
            'hidden_from_clients' => ['boolean'],
            'billable' => ['boolean'],
            'subscribed_users' => ['array'],
            'labels' => ['array'],
        ];
    }
}
