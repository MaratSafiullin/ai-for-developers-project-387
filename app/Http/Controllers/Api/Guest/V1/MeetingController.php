<?php

namespace App\Http\Controllers\Api\Guest\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Guest\V1\StoreMeetingRequest;
use App\Http\Resources\MeetingResource;
use App\Services\Booking\MeetingBooker;

class MeetingController extends Controller
{
    /**
     * Book a free slot; a taken interval answers 409 `slot_taken`.
     */
    public function store(StoreMeetingRequest $request, MeetingBooker $meetingBooker): MeetingResource
    {
        return new MeetingResource($meetingBooker->book(
            $request->meetingType(),
            $request->startsAt(),
            $request->guestName(),
        ));
    }
}
