<?php

namespace KyleWLawrence\Infinity\Crud\Traits;

trait BuildChain
{
    public function buildDelete($command, string $id): object
    {
        $parentName = (isset($this->parent_resource_id_key) && isset($this->{$this->parent_resource_id_key})) ? $this->{$this->parent_resource_id_key} : null;
        $parentId = (isset($this->parent_resource_id) && isset($this->{$this->parent_resource_id})) ? $this->{$this->parent_resource_id} : null;

        if ($parentName !== null) {
            return $this->client->boards($this->board_id)->$parentName($parentId)->{$this->obj_name_plural}()->$command($id);
        } else {
            return $this->client->boards($this->board_id)->{$this->obj_name_plural}()->$command($id);
        }
    }

    public function buildCreate($command, array $params): object
    {
        $parentName = (isset($this->parent_resource_id_key) && isset($this->{$this->parent_resource_id_key})) ? $this->{$this->parent_resource_id_key} : null;
        $parentId = (isset($this->parent_resource_id) && isset($this->{$this->parent_resource_id})) ? $this->{$this->parent_resource_id} : null;

        if ($parentName !== null) {
            return $this->client->boards($this->board_id)->$parentName($parentId)->{$this->obj_name_plural}()->$command($params);
        } else {
            return $this->client->boards($this->board_id)->{$this->obj_name_plural}()->$command($params);
        }
    }

    public function buildUpdate($command, string $id, array $params): object
    {
        $parentName = (isset($this->parent_resource_id_key) && isset($this->{$this->parent_resource_id_key})) ? $this->{$this->parent_resource_id_key} : null;
        $parentId = (isset($this->parent_resource_id) && isset($this->{$this->parent_resource_id})) ? $this->{$this->parent_resource_id} : null;

        if ($parentName !== null) {
            return $this->client->boards($this->board_id)->$parentName($parentId)->{$this->obj_name_plural}()->$command($id, $params);
        } else {
            return $this->client->boards($this->board_id)->{$this->obj_name_plural}()->$command($id, $params);
        }
    }
}
