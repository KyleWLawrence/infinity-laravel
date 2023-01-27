<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Delete
{
    public function delete(): void
    {
        $this->buildChain(__FUNCTION__, $this->id);
    }
}
