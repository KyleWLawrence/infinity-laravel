<?php

namespace KyleWLawrence\Infinity\Data;

use KyleWLawrence\Infinity\Data\Traits\Defaults;
use KyleWLawrence\Infinity\Services\InfinityService;

class CrudView extends View
{
    use Defaults;

    public readonly string $parent_id_key = 'items';

    public function __construct(
        object $apiObject,
        public readonly string $board_id,
        public readonly string $parent_id,
        protected $client = new InfinityService(),
    ): void {
        parent::__construct($apiObject);
    }
}
