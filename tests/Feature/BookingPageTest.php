<?php

use Inertia\Testing\AssertableInertia as Assert;

test('booking page renders the booking index component with the owner timezone', function () {
    config(['owner.timezone' => 'Asia/Tokyo']);

    $this->get(route('booking.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('booking/index')
            ->where('timezone', 'Asia/Tokyo'));
});
