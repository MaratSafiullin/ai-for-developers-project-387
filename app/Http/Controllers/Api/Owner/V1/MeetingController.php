<?php

namespace App\Http\Controllers\Api\Owner\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingResource;
use App\Models\Meeting;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeetingController extends Controller
{
    /**
     * List the meetings of all types that have not ended yet, nearest first.
     */
    public function index(): AnonymousResourceCollection
    {
        return MeetingResource::collection(Meeting::query()
            ->with('meetingType')
            ->where('ends_at', '>', now())
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get());
    }
}
