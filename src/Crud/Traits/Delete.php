<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait Delete
{
    public function delete(): void
    {
        $apiObject = $this->buildDelete(__FUNCTION__, $this->id);
        foreach ($this->object_keys as $key) {
            unset($this->$key);
        }

        $this->resetObjectVars($apiObject);
        $this->updated = false;
        $this->api_deleted = true;
    }
}
