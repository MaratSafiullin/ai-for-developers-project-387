<?php

use Inertia\Testing\AssertableInertia as Assert;

test('home page renders the home component with the owner timezone', function () {
    config(['owner.timezone' => 'Asia/Tokyo']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('home')
            ->where('timezone', 'Asia/Tokyo'));
});
