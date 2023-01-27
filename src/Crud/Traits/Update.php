<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Update
{
    public function update(): object
    {
        if ($this->updated === false) {
            return $this;
        }

        $apiObject = $this->buildChain(__FUNCTION__, $this->id, $this->getUpdateSet());
        $this->updated = false;

        $this->setObjectVars($apiObject);

        return $this;
    }
}
