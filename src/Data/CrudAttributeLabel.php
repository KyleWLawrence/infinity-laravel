<?php

namespace Infinity\Data;

use Infinity\Data\Traits\Construct;
use Infinity\Data\Traits\Defaults;

class CrudAttributeLabel extends AttributeLabel
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
        $label = $this->genLabel($name, $color);
        $this->update();

        return $label->id;
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
