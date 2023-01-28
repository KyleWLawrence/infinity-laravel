<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Create
{
    public function create(): object
    {
        $apiObject = $this->buildCreate(__FUNCTION__, $this->getUpdateSet());

        $this->updated = false;
        $this->setObjectVars($apiObject->all());

        return $this;
    }
}
