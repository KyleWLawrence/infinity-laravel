<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Construct;
use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\Attribute as OriginalAttribute;

class Attribute extends OriginalAttribute
{
    use Defaults;
    use Construct;
}
