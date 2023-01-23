<?php

namespace KyleWLawrence\Infinity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Infinity\Api\HttpClient
 */
class Infinity extends Facade
{
    /**
     * Return facade accessor.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Infinity';
    }
}
