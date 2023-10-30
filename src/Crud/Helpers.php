<?php

use KyleWLawrence\Infinity\Crud\Lists\Attributes;
use KyleWLawrence\Infinity\Crud\Lists\Folders;
use KyleWLawrence\Infinity\Crud\Lists\Items;
use KyleWLawrence\Infinity\Crud\Lists\ListBase;
use KyleWLawrence\Infinity\Crud\Lists\References;
use KyleWLawrence\Infinity\Crud\Lists\Views;
use KyleWLawrence\Infinity\Crud\Objects\Attribute;
use KyleWLawrence\Infinity\Crud\Objects\AttributeLabel;
use KyleWLawrence\Infinity\Crud\Objects\Board;
use KyleWLawrence\Infinity\Crud\Objects\Comment;
use KyleWLawrence\Infinity\Crud\Objects\Folder;
use KyleWLawrence\Infinity\Crud\Objects\Hook;
use KyleWLawrence\Infinity\Crud\Objects\Item;
use KyleWLawrence\Infinity\Crud\Objects\Reference;
use KyleWLawrence\Infinity\Crud\Objects\User;
use KyleWLawrence\Infinity\Crud\Objects\View;
use KyleWLawrence\Infinity\Crud\Objects\Workspace;

if (! function_exists('conv_laravel_inf_obj')) {
    /**
     * @return Infinity\Data\ObjectBase
     */
    function conv_laravel_inf_obj(object $obj, ?string $boardId = null, object|array|null $atts = null, ?string $item_id = null): object
    {
        $obj = match ($obj->object) {
            'attribute' => match ($obj->type) {
                'label' => new AttributeLabel($obj, $boardId),
                default => new Attribute($obj, $boardId),
            },
            'board' => new Board($obj),
            'comment' => new Comment($obj, $boardId, $item_id),
            'folder' => new Folder($obj, $boardId),
            'hook' => new Hook($obj, $boardId),
            'item' => new Item($obj, $boardId, $atts),
            'reference' => new Reference($obj, $boardId),
            'folderview', 'view' => new View($obj, $boardId),
            'workspace' => new Workspace($obj),
            'user' => new User($obj),
            default => throw new Exception("Unknown Object Type: $obj->type for {$obj->id}"),
        };

        return $obj;
    }

    /**
     * @return Infinity\Data\ObjectBase
     */
    function conv_laravel_inf_list(array $array, string $type, ?string $boardId = null, object|array|null $atts = null): object
    {
        $list = match ($type) {
            'attribute', 'attributes' => new Attributes($array, $boardId),
            'item', 'items' => new Items($array, $boardId, $atts),
            'reference', 'references' => new References($array, $boardId),
            'view', 'views' => new Views($array, $boardId),
            'folder', 'folders' => new Folders($array, $boardId),
            default => new ListBase($array, $boardId),
        };

        return $list;
    }
}
