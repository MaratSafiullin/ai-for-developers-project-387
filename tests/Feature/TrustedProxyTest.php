<?php

use Illuminate\Support\Facades\Route;

test('urls use https when a TLS-terminating proxy forwards the request', function () {
    Route::get('/_test/current-url', fn () => url('/'));

    $this->get('/_test/current-url', [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-Host' => 'calendar.example.com',
        'X-Forwarded-Port' => '443',
    ])
        ->assertOk()
        ->assertContent('https://calendar.example.com');
});
