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
    'token' => env('INF_TOKEN', ''),
    'workspace' => env('INF_WORKSPACE', 0),
    'objects' => env('INF_OBJECTS', false),

];
