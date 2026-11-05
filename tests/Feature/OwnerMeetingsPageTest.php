<?php

use Inertia\Testing\AssertableInertia as Assert;

test('owner meetings page renders its component with the owner timezone', function () {
    config(['owner.timezone' => 'Asia/Tokyo']);

    $this->get(route('owner.meetings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('owner/meetings/index')
            ->where('timezone', 'Asia/Tokyo'));
});
