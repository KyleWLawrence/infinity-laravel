<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Data\Objects\Workspace as OriginalWorkspace;
use KyleWLawrence\Infinity\Services\InfinityService;

class Workspace extends OriginalWorkspace
{
    public function __construct(
        protected object $apiObject,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject);
    }
}
