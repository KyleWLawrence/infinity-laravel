<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

/**
 * This trait gives resources access to the default CRUD methods.
 */
trait Defaults
{
    use Delete;
    use Update;
    use Create;
    use BuildChain;
}
