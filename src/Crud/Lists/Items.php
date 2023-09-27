<?php

namespace KyleWLawrence\Infinity\Crud\Lists;

use KyleWLawrence\Infinity\Crud\Lists\Traits\Delete;
use KyleWLawrence\Infinity\Data\Lists\Items as OriginalItems;
use KyleWLawrence\Infinity\Services\InfinityService;
use LogIt;

class Items extends OriginalItems
{
    use Delete;

    public function __construct(
        array $apiObjects,
        protected ?string $board_id,
        public ?array $attributes = null,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObjects, $board_id, $attributes);
    }

    public function deleteInFolder(?string $fid = null): object
    {
        $deleted = [];
        foreach ($this->list as $key => &$item) {
            if (is_null($fid) || $item->folder_id === $fid) {
                $deleted[] = $item->id;
                LogIt::LogActivity("Deleting Item: {$item->id}");
                $item->delete();
                unset($this->list[$key]);
            }
        }

        sort($this->list);

        return $this;
    }

    public function getDupeByAtts($aids, $return = 'obj'): array|object
    {
        if (! is_array($aids)) {
            $aids = [$aids];
        }

        $vals = [];
        $duplicates = [];
        $duplicate_ids = [];

        foreach ($this->list as &$item) {
            $matches = true;

            foreach ($aids as $aid) {
                $val = $item->getValueByAid($aid)->getData();

                if (is_array($val)) {
                    sort($val);
                    $val = implode(',', $val);
                } elseif (empty($val)) {
                    continue;
                }

                if (! in_array($val, $vals[$aid])) {
                    $matches = false;
                }

                if (! empty($val)) {
                    $vals[$aid] = $val;
                }
            }

            if ($matches) {
                $duplicates[] = $item;
                $duplicate_ids[] = $item->id;
            }
        }

        if ($return === 'id') {
            return array_column($duplicates, 'id');
        } else {
            return $this->collect($duplicates);
        }
    }

    public function moveToFolder($fid): object
    {
        foreach ($this->list as &$item) {
            LogIt::LogActivity("Moving Infinity Item {$item->id} to Folder $fid");
            $item->setFolderId($fid)->update();
        }

        return $this;
    }

    public function setAttributes(?array $atts = null): object
    {
        if (is_null($atts)) {
            $this->attributes = $this->client->boards($this->board_id)->attributes()->setSkipConvObj()->getAllLoop()->data;
        } else {
            $this->attributes = $atts;
        }

        $this->assignAttributes();

        return $this;
    }
}
