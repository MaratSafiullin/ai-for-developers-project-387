<?php

namespace App\Http\Controllers\Api\Owner\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\V1\StoreMeetingTypeRequest;
use App\Http\Resources\MeetingTypeResource;
use App\Models\MeetingType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeetingTypeController extends Controller
{
    /**
     * List the owner's meeting types in creation order.
     */
    public function index(): AnonymousResourceCollection
    {
        return MeetingTypeResource::collection(MeetingType::query()->orderBy('id')->get());
    }

    /**
     * Create a meeting type; it appears in both catalogs right away.
     */
    public function store(StoreMeetingTypeRequest $request): MeetingTypeResource
    {
        return new MeetingTypeResource(MeetingType::query()->create($request->meetingTypeAttributes()));
    }
}
