<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Delete
{
    public function delete(): void
    {
        $this->buildDelete(__FUNCTION__, $this->id);
        $this->deleted = true;
    }
}
