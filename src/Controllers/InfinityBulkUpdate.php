<?php

namespace KyleWLawrence\Infinity\Controllers;

use Exception;
use Infinity;
use InfinityHelpers;
use LogIt;

class InfinityBulkUpdate
{
    public function list_board_attributes($bid, $fid = '')
    {
        $atts = Infinity::get_board_atts($bid, false);

        if ($fid !== '') {
            $folders = Infinity::get_board_folders($bid, false);
            $atts = InfinityHelpers::match_attr_to_folders($folders, $atts);
            $atts = InfinityHelpers::keep_attr_only_if_in_folders($fid, $atts);
        }

        foreach ($atts as $att) {
            echo "'{$att['name']}' => '{$att['id']}',\n";
        }

        return true;
    }

    public function delete_all_from_folder($bid, $fid)
    {
        $items = Infinity::get_folder_items($bid, $fid, 'values');

        foreach ($items as $item) {
            LogIt::LogActivity("Deleting Item: {$item['id']}");
            Infinity::infinity_api_delete("boards/{$bid}/items/{$item['id']}");
        }

        return true;
    }

    public function delete_duplicate_items_from_attr($bid, $fid, $aid, $delete = true)
    {
        if (InfinityHelpers::is_infinity_id($aid) === false) {
            $aid = Infinity::get_attr_by_name($aid, $bid);

            if ($aid === false) {
                throw new Exception("Unable to find Attr ID ($aid) on board $bid");
            }
            $aid = $aid['id'];
        }

        $items = Infinity::get_folder_items($bid, $fid, 'values', false);
        $present = [];
        LogIt::LogActivity('Sorting through '.count($items).' items.');
        foreach ($items as $item) {
            $val = InfinityHelpers::get_att_value_from_item($aid, $item, false);
            if ($val == false) {
                continue;
            }

            if (is_array($val['value'])) {
                $val['value'] = implode(',', $val['value']);
            }

            if (in_array($val['value'], $present)) {
                LogIt::LogActivity("Deleting Item: {$item['id']} with dup value: {$val['value']}");
                if ($delete) {
                    Infinity::infinity_api_delete("boards/{$bid}/items/{$item['id']}");
                }
            } else {
                $present[] = $val['value'];
            }
        }

        return true;
    }

    public function move_all_items_to_folder($bid, $fid_from, $fid_to, $limit = 10)
    {
        $items = Infinity::get_folder_items($bid, $fid_from, '', true);

        $i = 0;
        foreach ($items as $item) {
            if (++$i > $limit) {
                break;
            }
            $item_update = ['folder_id' => $fid_to];

            LogIt::LogActivity("Moved Infinity Item {$item['id']} to Folder $fid_to");
            Infinity::infinity_api_put("boards/{$bid}/items/{$item['id']}", $item_update);
        }

        return true;
    }
}
