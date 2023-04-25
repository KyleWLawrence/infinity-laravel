<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\BuildChain;
use KyleWLawrence\Infinity\Crud\Traits\Create;
use KyleWLawrence\Infinity\Data\Objects\Board as OriginalBoard;
use KyleWLawrence\Infinity\Services\InfinityService;

class Board extends OriginalBoard
{
    use Create;
    use BuildChain;

    public function __construct(
        protected object $apiObject,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject);
    }
}
