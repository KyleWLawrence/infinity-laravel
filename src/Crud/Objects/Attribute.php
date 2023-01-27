<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Data\Attribute as OriginalAttribute;
use KyleWLawrence\Infinity\Crud\Traits\Construct;
use KyleWLawrence\Infinity\Crud\Traits\Defaults;

class Attribute extends OriginalAttribute
{
    use Defaults;
    use Construct;
}
