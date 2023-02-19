<?php

namespace KyleWLawrence\Infinity\Crud\Lists;

use KyleWLawrence\Infinity\Crud\Lists\Traits\Delete;
use KyleWLawrence\Infinity\Crud\Lists\Traits\Delete;
use KyleWLawrence\Infinity\Data\Lists\Views as OriginalViews;
use KyleWLawrence\Infinity\Services\InfinityService;

class Views extends OriginalViews
{
    use Delete;

    public function __construct(
        array $apiObjects,
        protected string $board_id,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObjects, $board_id);
    }
}
