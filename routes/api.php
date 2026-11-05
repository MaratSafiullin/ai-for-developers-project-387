<?php

use App\Http\Controllers\Api\Guest\V1\MeetingController as GuestMeetingController;
use App\Http\Controllers\Api\Guest\V1\MeetingTypeController as GuestMeetingTypeController;
use App\Http\Controllers\Api\Guest\V1\SlotController;
use App\Http\Controllers\Api\Owner\V1\MeetingController as OwnerMeetingController;
use App\Http\Controllers\Api\Owner\V1\MeetingTypeController as OwnerMeetingTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('guest/v1')->name('api.guest.v1.')->group(function () {
    Route::get('meeting-types', [GuestMeetingTypeController::class, 'index'])->name('meeting-types.index');
    Route::get('slots', [SlotController::class, 'index'])->name('slots.index');
    Route::post('meetings', [GuestMeetingController::class, 'store'])->name('meetings.store');
});

Route::prefix('owner/v1')->name('api.owner.v1.')->group(function () {
    Route::get('meeting-types', [OwnerMeetingTypeController::class, 'index'])->name('meeting-types.index');
    Route::post('meeting-types', [OwnerMeetingTypeController::class, 'store'])->name('meeting-types.store');
    Route::get('meetings', [OwnerMeetingController::class, 'index'])->name('meetings.index');
});
