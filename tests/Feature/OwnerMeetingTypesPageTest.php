<?php

use Inertia\Testing\AssertableInertia as Assert;

test('owner meeting types page renders its component with the owner timezone', function () {
    config(['owner.timezone' => 'Asia/Tokyo']);

    $this->get(route('owner.meeting-types.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('owner/meeting-types/index')
            ->where('timezone', 'Asia/Tokyo'));
});
