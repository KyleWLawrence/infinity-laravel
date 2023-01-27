<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Construct;
use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\Item as OriginalItem;

class Item extends OriginalItem
{
    use Construct;
    use Defaults {
        update as traitUpdate;
    }

    public function isNew(): bool
    {
        return ($this->id) ? true : false;
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
