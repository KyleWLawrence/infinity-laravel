<?php

namespace KyleWLawrence\Infinity\Crud\Lists;

use KyleWLawrence\Infinity\Crud\Lists\Traits\Delete;
use KyleWLawrence\Infinity\Data\Lists\Attributes as OriginalAttributes;
use KyleWLawrence\Infinity\Services\InfinityService;

class Attributes extends OriginalAttributes
{
    use Delete;

    public function __construct(
        array $apiObjects,
        protected ?string $board_id,
        protected $client = new InfinityService(),
    ) {
        parent::__construct($apiObjects, $board_id);
    }

    public function listByFolder(string $fid, ?object $folders = null)
    {
        if (is_null($folders)) {
            $folders = $this->client->boards($this->board_id)->folders()->getAllLoop()->data;
        }

        $atts = $this->matchToFolders($folders);
        $atts = $atts->includeByFolders($fid);
        $keys = $atts->getColumn('name');
        $ids = $atts->getColumn('id');
        $list = array_combine($keys, $ids);

        return $list;
    }

    public function matchToFolders($folders): object
    {
        foreach ($folders as $folder) {
            foreach ($this->list as &$att) {
                if (in_array($att->id, $folder->attribute_ids)) {
                    $att->folder_names[] = $folder->name;
                    $att->folder_ids[] = $folder->id;
                }
            }
        }

        return $this;
    }

    public function excludeByFolders($fids)
    {
        if (! is_array($fids)) {
            $fids = [$fids];
        }

        $atts = [];
        foreach ($this->list as $att) {
            $diff = array_diff($att->folder_ids, $fids);

            if (! empty($diff)) {
                $atts[] = $att;
            }
        }

        return $this->collect($atts);
    }

    public function includeByFolders($fids)
    {
        if (! is_array($fids)) {
            $fids = [$fids];
        }

        $atts = [];
        foreach ($atts as $att) {
            $similar = array_intersect($fids, $att->folder_ids);

            if (! empty($similar)) {
                $atts[] = $att;
            }
        }

        return $this->collect($atts);
    }
}
