<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Create
{
    public function create(): object
    {
        $apiObject = $this->buildCreate(__FUNCTION__, $this->getUpdateSet());
        $this->updated = false;
        $this->api_created = true;
        $this->resetObjectVars($apiObject);

        return $this;
    }
}
