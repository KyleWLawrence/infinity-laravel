<?php

namespace KyleWLawrence\Infinity\Data\Traits;

trait Delete
{
    public function delete(): void
    {
        $this->buildChain(__FUNCTION__, $this->id);
    }
}
