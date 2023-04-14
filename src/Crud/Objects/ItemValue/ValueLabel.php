<?php

namespace KyleWLawrence\Infinity\Crud\Objects\ItemValue;

use KyleWLawrence\Infinity\Data\Objects\ItemValue\ValueLabel as OriginalValueLabel;

class ValueLabel extends OriginalValueLabel
{
    public function addLabelName(string $name, ?object &$att = null): object
    {
        $error = (is_object($att)) ? false : true;
        $id = $this->getLabelId($name, $error, $att);

        return $this->addLabelId($id);
    }

     public function addLabelNames(array $names, ?object &$att = null): object
     {
         foreach ($names as $name) {
             $this->addLabelName($name, $att);
         }

         return $this;
     }

     public function setLabelName(?string $name, ?object &$att = null): object
     {
         $error = (is_object($att)) ? false : true;
         $id = (! $name) ? null : $this->getLabelId($name, $error, $att);

         return $this->setLabelId($id);
     }

     public function getLabelId($name, $error = false, ?object &$att = null): ?string
     {
         if (is_object($att)) {
             if ($error === true) {
                 return $att->getLabelId($name, $error);
             } else {
                 return $att->createOrGetLabelId($name);
             }
         } elseif (in_array($name, $this->label_map)) {
             return array_search($name, $this->label_map);
         }

         if ($error) {
             throw new \Exception("Unable to find \$label for $name from attr #{$this->attribute->id}");
         }

         return null;
     }
}
