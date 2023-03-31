<?php

use KyleWLawrence\Infinity\Crud\Lists\Attributes;
use KyleWLawrence\Infinity\Crud\Lists\Folders;
use KyleWLawrence\Infinity\Crud\Lists\Items;
use KyleWLawrence\Infinity\Crud\Lists\ListBase;
use KyleWLawrence\Infinity\Crud\Lists\References;
use KyleWLawrence\Infinity\Crud\Lists\Views;
use KyleWLawrence\Infinity\Crud\Objects\Attribute;
use KyleWLawrence\Infinity\Crud\Objects\AttributeLabel;
use KyleWLawrence\Infinity\Crud\Objects\Item;
use KyleWLawrence\Infinity\Crud\Objects\ObjectBase;
use KyleWLawrence\Infinity\Crud\Objects\View;

if (! function_exists('conv_laravel_inf_obj')) {
    /**
     * @return Infinity\Data\ObjectBase
     */
    function conv_laravel_inf_obj(object $obj, ?string $boardId = null, object|array|null $atts = null): object
    {
        if (is_object($atts)) {
            $atts = $atts->toArray();
        }

        switch($obj->object) {
            case 'folderview':
                $obj = new View($obj, $boardId);
                break;
            case 'item':
                $obj = new Item($obj, $boardId);
                if (! is_null($atts)) {
                    $obj = $obj->setAttributes($atts);
                }
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

    /**
     * @return Infinity\Data\ObjectBase
     */
    function conv_laravel_inf_list(array $array, string $type, ?string $boardId = null, object|array|null $atts = null): object
    {
        if (is_object($atts)) {
            $atts = $atts->toArray();
        }

        switch($type) {
            case 'item':
                $list = new Items($array, $boardId, $atts);
                break;
            case 'attribute':
                $list = new Attributes($array, $boardId);
                break;
            case 'reference':
                $list = new References($array, $boardId);
                break;
            case 'folder':
                $list = new Folders($array, $boardId);
                break;
            case 'view':
                $list = new Views($array, $boardId);
                break;
            default:
                $list = new ListBase($array, $boardId);
                break;
        }

        return $list;
    }
}
