<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait BuildChain
{
    public function buildChain($command, ?string $id = null, ?array $params = null): object
    {
        $parentName = (isset($this->{$this->parent_resource_id_key})) ? $this->{$this->parent_resource_id_key} : null;
        $parentId = (isset($this->{$this->parent_resource_id})) ? $this->{$this->parent_resource_id} : null;

        return $this->client->boards($this->board_id)->when($parentName !== null, function ($inf) use ($parentName, $parentId) {
            return $inf->$parentName($parentId);
        })->when($id === null, function ($inf) use ($command, $params) {
            return $inf->$command($params);
        })->when($id !== null, function ($inf) use ($command, $id, $params) {
            return $inf->$command($id, $params);
        });
    }
