<?php

namespace App\Http\Controllers\Api\Owner\V1;

use App\Exceptions\MeetingStartedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingResource;
use App\Models\Meeting;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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

    /**
     * Cancel an upcoming meeting by deleting it; its cells go with it.
     *
     * @throws MeetingStartedException when the meeting starts now or earlier
     */
    public function destroy(int $id): Response
    {
        $meeting = Meeting::query()->find($id) ?? abort(404, __('Meeting not found.'));

        if ($meeting->starts_at->lte(now())) {
            throw new MeetingStartedException;
        }

        $meeting->delete();

        return response()->noContent();
    }
}
