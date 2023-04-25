<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Update
{
    public function update(): object
    {
        if ($this->isUpdated() === false) {
            return $this;
        }

        $apiObject = $this->buildUpdate(__FUNCTION__, $this->id, $this->getUpdateSet());
        $this->updated = false;
        $this->api_updated = true;
        $this->resetObjectVars($apiObject);

        return $this;
    }
}
