<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\Item as OriginalItem;
use KyleWLawrence\Infinity\Services\InfinityService;
use LogIt;

class Item extends OriginalItem
{
    use Defaults {
        update as traitUpdate;
    }

    public function __construct(
        protected object $apiObject,
        protected ?string $board_id = null,
        protected null|object|array $attributes = null,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObject, $board_id, $attributes);
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

    public function createRefToValMatch($val, $val_aid, $ref_aid, object &$items, object &$refs, $deleteExtra = true)
    {
        $this->createRefOnValMatch($val, $val_aid, $ref_aid, $items, $refs, 'to_item_id', $deleteExtra);
    }

    public function createRefFromValMatch($val, $val_aid, $ref_aid, object &$items, object &$refs, $deleteExtra = true)
    {
        $this->createRefOnValMatch($val, $val_aid, $ref_aid, $items, $refs, 'from_item_id', $deleteExtra);
    }

    public function createRefOnValMatch($val, $val_aid, $ref_aid, object &$items, object &$refs, $dir, $deleteExtra = true): object
    {
        $matches = $items->findItemsByData($val, $val_aid);
        if (empty($matches)) {
            return $this;
        } elseif (count($matches) > 1) {
            LogIt::reportWarning('Found '.count($matches)." for value ($val) and aid ($val_aid) on ".__FUNCTION__);
        }

        $match = $matches[0];
        $orig_refs = $refs->findRefsForItem($this->id, $ref_aid, $dir);
        $refKey = false;

        foreach ($orig_refs as $key => $ref) {
            if ($ref->$dir !== $match->id) {
                if ($deleteExtra) {
                    LogIt::LogActivity("Deleting Ref ({$ref->id}) from {$ref->from_item_id} and to {$ref->to_item_id}");
                    $refs->deleteById($ref->id);
                }
            } else {
                $refKey = $key;
            }
        }

        if ($refKey === false) {
            $from_id = ($dir === 'to_item_id') ? $this->id : $match->id;
            $to_id = ($dir === 'to_item_id') ? $match->id : $this->id;
            LogIt::LogActivity("Creating Ref from {$from_id} and to {$to_id}");
            $newRef = $this->client->boards($this->board_id)->references()->create([
                'from_item_id' => $from_id,
                'to_item_id' => $to_id,
                'attribute_id' => $ref_aid,
            ]);
            $refs->add($newRef);
        }

        return $this;
    }

    public function updateValToRef($val, $val_aid, $ref_aid, object &$items, object &$refs, $single = true)
    {
        $this->updateValOnRef($val, $val_aid, $ref_aid, $items, $refs, 'to_item_id', $single);
    }

    public function updateValFromRef($val, $val_aid, $ref_aid, object &$items, object &$refs, $single = true)
    {
        $this->updateValOnRef($val, $val_aid, $ref_aid, $items, $refs, 'from_item_id', $single);
    }

    public function updateValOnRef($val, $val_aid, $ref_aid, object &$items, object &$refs, $dir, $single = true)
    {
        $reverse = ($dir === 'to_item_id') ? 'from_item_id' : 'to_item_id';
        $matches = $refs->findRefsForItem($this->id, $ref_aid, $dir, 'obj');
        if (empty($matches)) {
            return $this;
        } elseif (count($matches) > 1 && $single === true) {
            LogIt::reportWarning('Found '.count($matches)." for id ($item->id) and ref aid ($ref_aid) on ".__FUNCTION__);
        }

        foreach ($matches as $match) {
            $items->getById($match->$reverse)->setDataByAid($val_aid, $val)->update();
        }

        return $this;
    }

    public function getValFromRef($val_aid, $ref_aid, object &$items, object &$refs, $single = true)
    {
        $this->getValOnRef($val_aid, $ref_aid, $items, $refs, 'from_item_id', $single);
    }

    public function getValToRef($val_aid, $ref_aid, object &$items, object &$refs, $single = true)
    {
        $this->getValOnRef($val_aid, $ref_aid, $items, $refs, 'to_item_id', $single);
    }

    public function getValOnRef($val_aid, $ref_aid, object &$items, object &$refs, $dir, $single = true)
    {
        $reverse = ($dir === 'to_item_id') ? 'from_item_id' : 'to_item_id';
        $matches = $refs->findRefsForItem($this->id, $ref_aid, $dir, 'obj');
        if (empty($matches)) {
            return $this;
        } elseif (count($matches) > 1 && $single === true) {
            LogIt::reportWarning('Found '.count($matches)." for id ($item->id) and ref aid ($ref_aid) on ".__FUNCTION__);
        }

        $valSet = [];
        if ($single === true) {
            $match = $matches[0];
            $valSet = $items->getById($match->$reverse)->getValueByAid($val_aid)->getData;
        } else {
            foreach ($matches as $match) {
                $valSet[$match->id] = $items->getById($match->$reverse)->getValueByAid($val_aid)->getData;
            }
        }

        return $valSet;
    }
}
