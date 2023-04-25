<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\Comment as OriginalComment;
use KyleWLawrence\Infinity\Services\InfinityService;

class Comment extends OriginalComment
{
    use Defaults;

    protected string $parent_resource_id_key = 'items';

    public function __construct(
        protected object $apiObject,
        protected ?string $board_id,
        protected ?string $parent_resource_id,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject, $board_id);
    }
}
