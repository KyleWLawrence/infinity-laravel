<?php

namespace KyleWLawrence\Infinity\Crud\Lists\Traits;

use Exception;

trait Delete
{
    public function deleteById(string $id): object
    {
        $itemKey = array_search($id, $this->getColumn('id'));
        if (! is_int($itemKey)) {
            throw new Exception("Unable to find item id ($id) in list");
        } else {
            $this->list[$itemKey]->delete();
            $this->remove($itemKey);
        }

        return $this;
    }

    public function deleteAll(): array
    {
        $deleted = [];
        foreach ($this->list as &$obj) {
            $deleted[] = $obj->id;
            $obj->delete();
        }

        return $deleted;
    }
}
