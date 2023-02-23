<?php

namespace KyleWLawrence\Infinity\Crud\Lists;

use KyleWLawrence\Infinity\Crud\Lists\Traits\Delete;
use KyleWLawrence\Infinity\Data\Lists\Folders as OriginalFolders;
use KyleWLawrence\Infinity\Services\InfinityService;

class Folders extends OriginalFolders
{
    use Delete;

    public function __construct(
        array $apiObjects,
        protected ?string $board_id,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObjects, $board_id);
    }
}
