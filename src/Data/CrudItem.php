<?php

namespace KyleWLawrence\Infinity\Data;

use KyleWLawrence\Infinity\Data\Traits\Construct;
use KyleWLawrence\Infinity\Data\Traits\Defaults;

class CrudItem extends Item
{
    use Construct;
    use Defaults {
        update as traitUpdate;
    }

    public function deleteValue($id): void
    {
        $this->client->boards($this->board_id)->items($this->id)->values($id)->delete();
    }

    public function deleteEmptyValues(): void
    {
        foreach ($this->getDeleteSet() as $id) {
            $this->deleteValue($id);
        }

        $this->unsetEmptyVals();
    }

    public function update(): object
    {
        $this->deleteEmptyValues();

        return $this->traitUpdate();
    }
}
