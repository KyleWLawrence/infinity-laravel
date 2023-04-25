<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\BuildChain;
use KyleWLawrence\Infinity\Crud\Traits\Construct;
use KyleWLawrence\Infinity\Crud\Traits\Create;
use KyleWLawrence\Infinity\Crud\Traits\Delete;
use KyleWLawrence\Infinity\Data\Objects\Reference as OriginalReference;

class Reference extends OriginalReference
{
    use Delete, Create, BuildChain, Construct;
}
