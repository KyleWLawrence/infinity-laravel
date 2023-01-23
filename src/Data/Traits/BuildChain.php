<?php

namespace KyleWLawrence\Infinity\Data\Traits;

trait BuildChain
{
    public function buildChain($command, ?string $id, ?array $params): void
    {
        $parentName = (isset($this->{$this->parent_id_key})) ? $this->{$this->parent_id_key} : false;
        $parentId = (isset($this->{$this->parent_id})) ? $this->{$this->parent_id} : null;

        return $this->client->boards($this->board_id)->when($parentName !== false, function ($zd) use ($parentName, $parentId) {
            return $zd->$parentName($parentId);
        })->when($id === null, function ($zd) use ($command, $params) {
            $zd->$command($params);
        })->when($id !== null, function ($zd) use ($command, $id, $params) {
            $zd->$command($id, $params);
        });
    }
}
