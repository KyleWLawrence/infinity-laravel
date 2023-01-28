<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\Comments as OriginalComments;
use KyleWLawrence\Infinity\Services\InfinityService;

class Comments extends OriginalComments
{
    use Defaults;

    protected string $parent_resource_id_key = 'items';

    public function __construct(
        object $apiObject,
        string $board_id,
        protected string $parent_resource_id,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject, $board_id);
    }
}
