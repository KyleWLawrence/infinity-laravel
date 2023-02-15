<?php

namespace KyleWLawrence\Infinity\Crud\Objects;

use KyleWLawrence\Infinity\Crud\Traits\Construct;
use KyleWLawrence\Infinity\Crud\Traits\Defaults;
use KyleWLawrence\Infinity\Data\Objects\AttributeLabel as OriginalAttributeLabel;

class AttributeLabel extends OriginalAttributeLabel
{
    use Defaults;
    use Construct;

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
}
