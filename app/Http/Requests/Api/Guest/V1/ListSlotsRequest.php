<?php

namespace App\Http\Requests\Api\Guest\V1;

use App\Models\MeetingType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the slot query. An unknown meeting type is not a validation
 * error: the type is the addressed resource, so it answers 404.
 */
class ListSlotsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'meetingTypeId' => ['required', 'integer', 'max:'.MeetingType::MAX_ID],
        ];
    }

    /**
     * Get the requested meeting type or answer 404.
     */
    public function meetingType(): MeetingType
    {
        return MeetingType::query()->find((int) $this->validated('meetingTypeId'))
            ?? abort(404, __('Meeting type not found.'));
    }
}
