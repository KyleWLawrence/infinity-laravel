<?php

use KyleWLawrence\Infinity\Crud\Objects\Attribute;
use KyleWLawrence\Infinity\Crud\Objects\AttributeLabel;
use KyleWLawrence\Infinity\Exceptions\UnknownObjectException;
use KyleWLawrence\Infinity\Crud\Objects\Item;
use KyleWLawrence\Infinity\Crud\Objects\ObjectBase;
use KyleWLawrence\Infinity\Crud\Objects\View;

if (! function_exists('conv_laravel_inf_obj')) {
    /**
     * @return Infinity\Data\ObjectBase
     */
    function conv_laravel_inf_obj(object $obj, ?string $boardId = null): object
    {
        switch($obj->object) {
            case 'folderview':
                $obj = new View($obj, $boardId);
                break;
            case 'item':
                $obj = new Item($obj, $boardId);
                break;
            case 'attribute':
                $obj = match ($obj->type) {
                    'label' => new AttributeLabel($obj, $boardId),
                    default => new Attribute($obj, $boardId),
                };
                break;
            default:
                $obj = new ObjectBase($obj, $boardId);
                break;
        }

        return $obj;
    }
}
