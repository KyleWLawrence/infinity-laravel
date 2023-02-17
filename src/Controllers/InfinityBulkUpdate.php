<?php

namespace KyleWLawrence\Infinity\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Infinity;
use KyleWLawrence\Infinity\InfinityHelpers;
use LogIt;
use Ramsey\Uuid\Uuid;

class InfinityBulkUpdate extends Controller
{
    public function listBoardAtts(string $bid, ?string $fid): array
    {
        $atts = Infinity::boards($bid)->attributes->getAllLoop()->data;

        if ($fid !== null) {
            $folders = Infinity::boards($bid)->folders()->getAllLoop()->data;
            $atts = InfinityHelpers::matchAttsToFolders($folders, $atts);
            $atts = InfinityHelpers::keepAttsByFolders($fid, $atts);
        }

        $attList = [];
        foreach ($atts as $att) {
            $attList[$att['id']] = $att['name'];
        }

        return $attList;
    }

    public function searchBoardAtts(string $search, string $bid, ?string $fid): array
    {
        $atts = $this->listBoardAtts($bid, $fid);

        $attList = [];
        foreach ($atts as $id => $name) {
            if ( strpos($name, $search) !== false ) {
                $attList[$id] = $name;
            }
        }

        return $attList;
    }

    public function deleteItemsInFolder($bid, $fid): array
    {
        $items = Infinity::boards($bid)->items()->getAllLoop(['folder_id' => $fid, 'expand[]' => 'values'])->data;
        $deleted = [];

        foreach ($items as $item) {
            LogIt::LogActivity("Deleting Item: {$item->id}");
            $deleted[] = $item->id;
            Infinity::boards($bid)->items()->delete($item->id);
        }

        return $deleted;
    }

    public function deleteDupesFromAttr($bid, $fid, $aid, $delete = true)
    {
        if (! Uuid::isValid($aid)) {
            $atts = Infinity::boards($bid)->attributes()->getAllLoop()->data;
            $attList = conv_inf_list($atts);
            $att = $attList->getByKey($aid);

            if ($att === null) {
                throw new Exception("Unable to find Attr ID ($aid) on board $bid");
            } else {
                $aid = $att->getId();
            }
        }

        $items = Infinity::boards($bid)->items()->getAllLoop(['folder_id' => $fid, 'expand[]' => 'values.attributes'])->data;
        $present = [];
        LogIt::LogActivity('Sorting through '.count($items).' items.');
        foreach ($items as $item) {
            $item = conv_inf_obj($item, $bid);
            $val = $item->get

            $val = InfinityHelpers::get_att_value_from_item($aid, $item, false);
            if ($val == false) {
                continue;
            }

            if (is_array($val->value)) {
                $val->value = implode(',', $val->value);
            }

            if (in_array($val->value, $present)) {
                LogIt::LogActivity("Deleting Item: {$item->id} with dup value: {$val->value}");
                if ($delete) {
                    Infinity::infinity_api_delete("boards/{$bid}/items/{$item->id}");
                }
            } else {
                $present[] = $val->value;
            }
        }

        return true;
    }

    public function moveItemsToFolder($bid, $fid_from, $fid_to, $limit = 10): array
    {
        $items = Infinity::boards($bid)->items()->getAllLoop(['folder_id' => $fid_from, 'expand[]' => 'values'])->data;
        $moved = [];

        $i = 0;
        foreach ($items as $item) {
            if (++$i > $limit) {
                break;
            }

            LogIt::LogActivity("Moving Infinity Item {$item->id} to Folder $fid_to");
            Infinity::boards($bid)->items()->update($item->id, ['folder_id' => $fid_to]);
            $moved[] = $item->id;
        }

        return $moved;
    }
}
