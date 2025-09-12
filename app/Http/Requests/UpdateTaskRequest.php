<?php

namespace App\Http\Requests;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'min:5'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status'      => ['sometimes', Rule::in(['pending', 'in_progress', 'completed'])],
            'priority'    => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'due_date'    => [
                'sometimes',
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    $status = $this->input('status', '');
                    if (in_array($status, ['pending', 'in_progress']) && strtotime($value) < now()->startOfDay()->timestamp) {
                        $fail('Due date cannot be in the past for pending or in-progress tasks.');
                    }
                }
            ],
            'assigned_to' => ['sometimes', 'nullable', 'exists:users,id'],
            'tags'        => ['sometimes', 'array'],
            'tags.*'      => ['integer', 'exists:tags,id'],
            'metadata'    => ['sometimes', 'nullable', 'json'],
            'version'     => ['required', 'integer'],
        ];
    }
}
