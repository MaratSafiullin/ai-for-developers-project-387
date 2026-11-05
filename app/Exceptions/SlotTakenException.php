<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * The requested interval overlaps another meeting. An expected outcome of
 * a booking race, answered with 409 `slot_taken` and not reported.
 */
class SlotTakenException extends Exception
{
    /**
     * Render the exception into a 409 response without validation errors.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => __('This time is already taken. Choose another slot.'),
            'code' => 'slot_taken',
        ], JsonResponse::HTTP_CONFLICT);
    }
}
