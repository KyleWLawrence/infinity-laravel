<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

use KyleWLawrence\Infinity\Services\InfinityService;

trait Construct
{
    public function __construct(
        protected object $apiObject,
        protected ?string $board_id,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject, $board_id);
    }
}
