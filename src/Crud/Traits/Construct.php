<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

use KyleWLawrence\Infinity\Services\InfinityService;

trait Construct
{
    public function __construct(
        object $apiObject,
        ?string $board_id = null,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject, $board_id);
    }
}
