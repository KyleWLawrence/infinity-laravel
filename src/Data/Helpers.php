<?php

use KyleWLawrence\Infinity\Data\CrudAttribute;
use KyleWLawrence\Infinity\Data\CrudAttributeLabel;
use KyleWLawrence\Infinity\Data\CrudItem;
use KyleWLawrence\Infinity\Data\CrudObjectBase;
use KyleWLawrence\Infinity\Data\Exceptions\UnknownObjectException;

if (! function_exists('conv_laravel_inf_obj')) {
    /**
     * @return Infinity\Data\ObjectBase
     */
    function conv_laravel_inf_obj(object $obj, string $boardId): object
    {
        switch($obj->object) {
            case 'folderview':
                $obj = new CrudView($obj, $boardId);
                break;
            case 'reference':
            case 'hook':
            case 'folder':
            case 'comment':
            case 'board':
                $obj = new CrudObjectBase($obj, $boardId);
                break;
            case 'item':
                $obj = new CrudItem($obj, $boardId);
                break;
            case 'attribute':
                $obj = match ($obj->type) {
                    'label' => new CrudAttributeLabel($obj, $boardId),
                    default => new CrudAttribute($obj, $boardId),
                };
                break;
            default:
                throw new UnknownObjectException("Obj ($obj->object) is not recognized");
                break;
        }

        return $obj;
    }
}
