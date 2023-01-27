<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Construct;
use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\ObjectBase as OriginalObjectBase;

class ObjectBase extends OriginalObjectBase
{
    use Defaults;
    use Construct;
}
