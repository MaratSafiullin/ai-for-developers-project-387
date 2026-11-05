<?php

namespace App\Http\Controllers\Api\Guest\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Guest\V1\ListSlotsRequest;
use App\Http\Resources\SlotCalendarResource;
use App\Services\Booking\SlotFinder;

class SlotController extends Controller
{
    /**
     * List the free slots of a meeting type for every date of the booking window.
     */
    public function index(ListSlotsRequest $request, SlotFinder $slotFinder): SlotCalendarResource
    {
        return new SlotCalendarResource($slotFinder->slotCalendar($request->meetingType()));
    }
}
