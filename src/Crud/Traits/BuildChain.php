<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait BuildChain
{
    public function buildChain($command, ?string $id = null, ?array $params = null): object
    {
        $parentName = (isset($this->parent_resource_id_key) && isset($this->{$this->parent_resource_id_key})) ? $this->{$this->parent_resource_id_key} : null;
        $parentId = (isset($this->parent_resource_id) && isset($this->{$this->parent_resource_id})) ? $this->{$this->parent_resource_id} : null;

        return $this->client->boards($this->board_id)->when($parentName !== null, function ($inf) use ($parentName, $parentId) {
            return $inf->$parentName($parentId);
        })->when($id === null, function ($inf) use ($command, $params) {
            return $inf->{$this->obj_name_plural}()->$command($params);
        })->when($id !== null, function ($inf) use ($command, $id, $params) {
            return $inf->{$this->obj_name_plural}()->$command($id, $params);
        });
    }
}
