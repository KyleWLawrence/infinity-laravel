<?php

namespace KyleWLawrence\Infinity\Data\Traits;

use KyleWLawrence\Infinity\Services\InfinityService;

trait Construct
{
    public function __construct(
        object $apiObject,
        protected readonly string $board_id,
        protected $client = new InfinityService(),
    ): void {
        parent::construct($apiObject);
    }
}
