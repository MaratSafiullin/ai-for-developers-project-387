<?php

use Illuminate\Support\Facades\DB;

test('a concurrent booking waits for the SQLite write lock instead of failing at once', function () {
    expect(DB::selectOne('PRAGMA busy_timeout')->timeout)->toBe(5000)
        ->and(DB::connection()->getConfig('transaction_mode'))->toBe('DEFERRED');
});
