<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\Item as OriginalItem;
use KyleWLawrence\Infinity\Services\InfinityService;

class Item extends OriginalItem
{
    use Defaults {
        update as traitUpdate;
    }

    public function __construct(
        object $apiObject,
        string $board_id,
        ?array $attributes = null,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject, $board_id);

        if (! is_null($attributes)) {
            $this->setAttributes($attributes);
        }
    }

    public function getUpdateSet()
    {
        return [
            'folder_id' => $this->folder_id,
            'values' => $this->getUpdateValues(),
            'parent_id' => $this->parent_id,
        ];
    }

    public function getUpdateValues(): array
    {
        $values = [];
        foreach ($this->values as $val) {
            if (! empty($val->getData()) && $val->isUpdated()) {
                $values[] = $val->getUpdateSet();
            }
        }

        return $values;
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
