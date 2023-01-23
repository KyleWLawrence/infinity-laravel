<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    |
    | This is the Auth vars.
    |
    */

    'driver' => env('INF_DRIVER', 'api'),
    'bearer' => env('INF_BEARER'),
    'workspace' => env('INF_WORKSPACE'),
    'objects' => env('INF_OBJECTS', false),

];
