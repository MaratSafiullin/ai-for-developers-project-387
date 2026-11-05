<?php

namespace App\Http\Requests\Api\Owner\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a new meeting type. Strings arrive trimmed and empty strings
 * arrive as null thanks to the global middleware, so a whitespace-only
 * name fails `required` and an empty description is stored as null.
 */
class StoreMeetingTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'durationMinutes' => ['required', 'integer', 'min:15', 'max:240', 'multiple_of:15'],
        ];
    }

    /**
     * Get the validated data mapped to meeting type attributes.
     *
     * @return array{name: string, description: string|null, duration_minutes: int}
     */
    public function meetingTypeAttributes(): array
    {
        return [
            'name' => $this->validated('name'),
            'description' => $this->validated('description'),
            'duration_minutes' => (int) $this->validated('durationMinutes'),
        ];
    }
}
