<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Create
{
    public function create(): object
    {
        $apiObject = $this->buildChain(__FUNCTION__, null, $this->getUpdateSet());

        $this->updated = false;
        $this->setObjectVars($apiObject);

        return $this;
    }
}
