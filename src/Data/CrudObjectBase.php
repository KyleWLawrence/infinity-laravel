<?php

namespace KyleWLawrence\Infinity\Data;

use KyleWLawrence\Infinity\Data\Traits\Construct;
use KyleWLawrence\Infinity\Data\Traits\Defaults;

class CrudObjectBase extends ObjectBase
{
    use Defaults;
    use Construct;
}
