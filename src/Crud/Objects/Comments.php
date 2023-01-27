<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\Comments as OriginalComments;
use KyleWLawrence\Infinity\Services\InfinityService;

class Comments extends OriginalComments
{
    use Defaults;

    public readonly string $parent_id_key = 'items';

    public function __construct(
        object $apiObject,
        public readonly string $board_id,
        public readonly string $parent_id,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject);
    }
}
