<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Construct;
use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\AttributeLabel as OriginalAttributeLabel;

class AttributeLabel extends OriginalAttributeLabel
{
    use Construct, Defaults;

    protected array $label_map;

    protected function setObjectVars(object $apiObject): void
    {
        parent::setObjectVars($apiObject);

        $this->label_map = array_combine(array_column($this->settings->labels, 'id'), array_column($this->settings->labels, 'name'));
    }

    public function createOrGetLabelIdBySet(array $set): string
    {
        $id = $this->genOrGetLabelIdBySet($set);
        $this->update();

        return $id;
    }

    public function createLabel(string $name, ?string $color = null): string
    {
        $labelId = $this->genLabel($name, $color);
        $this->update();

        return $labelId;
    }

    public function updateLabelName(string $name, string $id): void
    {
        $this->setLabelName($name, $id);
        $this->update();
    }

    public function createOrGetLabelId(string $name): string
    {
        $id = $this->genOrGetLabelId($name);
        $this->update();

        return $id;
    }

    public function createOrGetLabelIds(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $ids[] = $this->genOrGetLabelId($name);
        }

        return $ids;
    }

    public function clearUnusedLabels(): object
    {
        $items = $this->client->boards($bid)->items()->setAttributes($this->toArray())->getAllLoop()->data;
        $folders = $this->client->boards($bid)->folders()->getAllLoop()->data;
        $fids = [];

        foreach ($folders as $folder) {
            if (in_array($aid, $folder->attribute_ids)) {
                $fids[] = $folder->id;
            }
        }

        $labelIds = array_column($att->settings->labels, 'id');
        foreach ($items as $item) {
            if (! in_array($item->folder_id, $fids)) {
                continue;
            }

            $ids = $item->getValueByAid($aid)->getData();
            if ($ids) {
                $labelIds = array_diff($labelIds, $ids);

                if (empty($labelIds)) {
                    break;
                }
            }
        }

        if ($labelIds) {
            foreach ($labelIds as $lid) {
                $att->removeLabelId($lid);
            }

            $att->update();
        }

        return $this;
    }
}
