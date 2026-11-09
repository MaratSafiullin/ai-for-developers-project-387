<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * The meeting to cancel has already started or is over. An expected outcome
 * of an owner's action, answered with 409 `meeting_started` and not reported.
 */
class MeetingStartedException extends Exception
{
    /**
     * Render the exception into a 409 response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => __('The meeting has already started and cannot be cancelled.'),
            'code' => 'meeting_started',
        ], JsonResponse::HTTP_CONFLICT);
    }
}
