<?php

namespace App\Http\Controllers\Api\Guest\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingTypeResource;
use App\Models\MeetingType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeetingTypeController extends Controller
{
    /**
     * List the meeting type catalog for guests in creation order.
     */
    public function index(): AnonymousResourceCollection
    {
        return MeetingTypeResource::collection(MeetingType::query()->orderBy('id')->get());
    }
}
