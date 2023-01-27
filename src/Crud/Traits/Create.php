<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Create
{
    public function create(): string
    {
        $apiObject = $this->buildChain(__FUNCTION__, null, $this->getUpdateSet());

        $this->updated = false;
        $keys = ['id', 'created_by', 'created_at', 'deleted'];
        foreach ($keys as $key) {
            $this->$key = $apiObject->$key;
        }

        return $this->id;
    }
}
