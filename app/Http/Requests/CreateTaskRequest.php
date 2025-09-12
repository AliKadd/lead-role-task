<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
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
            'title' => ['required', 'string', 'min:5'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'due_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    $status = $this->input('status', 'pending');
                    if (in_array($status, ['pending', 'in_progress']) && strtotime($value) < now()->startOfDay()->timestamp) {
                        $fail('Due date cannot be in the past for pending or in-progress tasks.');
                    }
                }
            ],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'metadata' => ['nullable', 'json'],
        ];
    }
}
