<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Owner Timezone
    |--------------------------------------------------------------------------
    |
    | The single timezone of the service. Working hours are defined in it,
    | and both guests and the owner see meeting times in it. The database
    | still stores times in UTC (see app.timezone).
    |
    */

    'timezone' => env('OWNER_TIMEZONE', 'Europe/Moscow'),

    /*
    |--------------------------------------------------------------------------
    | Working Hours
    |--------------------------------------------------------------------------
    |
    | The daily interval, in the owner timezone, in which meetings may take
    | place. The same for every day of the week and every meeting type.
    |
    */

    'working_hours' => [
        'start' => env('OWNER_WORKING_HOURS_START', '09:00'),
        'end' => env('OWNER_WORKING_HOURS_END', '18:00'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Window
    |--------------------------------------------------------------------------
    |
    | The number of calendar days, starting tomorrow in the owner timezone,
    | in which a guest may book a meeting.
    |
    */

    'booking_window_days' => (int) env('OWNER_BOOKING_WINDOW_DAYS', 14),

];
