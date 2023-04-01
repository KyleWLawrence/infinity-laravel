<?php

namespace KyleWLawrence\Infinity\Crud\Lists;

use KyleWLawrence\Infinity\Crud\Lists\Traits\Delete;
use KyleWLawrence\Infinity\Data\Lists\References as OriginalReferences;
use KyleWLawrence\Infinity\Services\InfinityService;

class References extends OriginalReferences
{
    use Delete;

    public function __construct(
        array $apiObjects,
        protected ?string $board_id,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObjects, $board_id);
    }

    public function getReferencedItems(array|object $query, ?string $aid = null)
    {
        if (is_array($query)) {
            $query = (object) $query;
        }

        $key = 'to_item_id';
        if (empty($query->from_item_id)) {
            $key = 'from_item_id';
        }

        $items = [];
        foreach ($this->list as $ref) {
            if (! is_null($aid) && $ref->attribute_id !== $aid) {
                continue;
            }

            $items[] = $this->client->boards($this->board_id)->items()->get($ref->$key);
        }

        if (! empty($items)) {
            $atts = $this->client->boards($this->board_id)->attributes()->getAll()->data->toArray();
            $items = new Items($items, $this->board_id, $atts);
        }

        return $items;
    }
}
