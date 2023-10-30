<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Data\Objects\User as OriginalUser;
use KyleWLawrence\Infinity\Services\InfinityService;

class User extends OriginalUser
{
    public function __construct(
        protected object $apiObject,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject);
    }
}
