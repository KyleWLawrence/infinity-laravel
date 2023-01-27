<?php

namespace KyleWLawrence\Infinity\Controllers;

use App\Http\Controllers\Controller;
use Cache;
use Exception;
use LogIt;
use Infinity;

class InfinityHelpers extends Controller
{
    //-----------------------------------------------------------------------------------
    //    References
    //-----------------------------------------------------------------------------------

    public static function get_references_for_item( $iid, $references, $aid, $dir = 'from', $return_data = 'id' ) {
        $dir = ( $dir === 'from' || 'from_item_id' ) ? 'from_item_id' : 'to_item_id';
        $return = ( $dir === 'from_item_id' ) ? 'to_item_id' : 'from_item_id';

        $list = [];
        foreach( $references as $ref  ) {
            if ( $ref[$dir] === $iid && $aid === $ref['attribute_id'] ) {
                $list[] = ( $return_data === 'id' ) ? $ref[$return] : $ref;
            }
        }

        return $list;
    }


    public static function update_reference_from_val( $val, $val_aid, $ref_aid, $items, $orig_iid, $bid, $val_dir = 'to_item_id' ) {
        $item = self::match_item_to_str_value( $val, $items, $val_aid );
        if ( $item === false ) return false;

        $refs = Infinity::get_board_refs( $bid, true );
        $opposite_val_dir = ( $val_dir === 'to_item_id' ) ? 'from_item_id' : 'to_item_id';
        $orig_refs = self::get_references_for_item( $orig_iid, $refs, $ref_aid, $opposite_val_dir, 'obj' );
        $delete_refs = [];
        $correct_ref = false;

        foreach( $orig_refs as $ref ) {
            if ( $ref[$val_dir] !== $item['id'] ) {
                $delete_refs[] = $ref;
            } else {
                $correct_ref = $ref;
            }
        }

        foreach( $delete_refs as $ref ) {
            LogIt::LogActivity( "Deleting Ref ({$ref['id']}) from {$ref['from_item_id']} and to {$ref['to_item_id']}" );
            Infinity::delete_ref( $ref['id'], $bid, true );
        }

        if ( $correct_ref === false ) {
            $from_id = ( $val_dir === 'to_item_id' ) ? $orig_iid : $item['id'];
            $to_id = ( $val_dir === 'to_item_id' ) ? $item['id'] : $orig_iid;
            LogIt::LogActivity( "Created Ref from {$from_id} and to {$to_id}" );
            $correct_ref = Infinity::create_ref( ['from_item_id' => $from_id, 'to_item_id' => $to_id, 'attribute_id' => $ref_aid], $bid, true );
        }

        return $correct_ref;
    }

    public static function update_val_on_ref( $val, $val_aid, $ref_aid, $items, $orig_iid, $bid, $val_dir = 'to_item_id', $single = true ) {
        $refs = Infinity::get_board_refs( $bid, true );
        $opposite_val_dir = ( $val_dir === 'to_item_id' ) ? 'from_item_id' : 'to_item_id';
        $refs = self::get_references_for_item( $orig_iid, $refs, $ref_aid, $opposite_val_dir, 'obj' );
        if ( $single === true && count( $refs ) > 1 ) {
            LogIt::LogActivity( "Found Multiple Refs $opposite_val_dir for {$orig_iid} on aid $ref_aid" );
            return false;
        }
        if ( count( $refs ) === 0 ) return false;

        $outcome = [];
        foreach( $refs as $ref ) {
            $item = self::get_by_id_from_list( $ref[$val_dir], $items, true )['object'];
            $outcome[$ref[$val_dir]] = self::update_val_on_item( $val, $val_aid, $item, $bid );
        }

        return $outcome;
    }

    public static function get_val_on_ref( $val_aid, $ref_aid, $items, $orig_iid, $bid, $val_dir = 'to_item_id', $single = true ) {
        $refs = Infinity::get_board_refs( $bid, true );
        $opposite_val_dir = ( $val_dir === 'to_item_id' ) ? 'from_item_id' : 'to_item_id';
        $refs = self::get_references_for_item( $orig_iid, $refs, $ref_aid, $opposite_val_dir, 'obj' );
        if ( $single === true && count( $refs ) > 1 ) {
            LogIt::LogActivity( "Found Multiple Refs $opposite_val_dir for {$orig_iid} on aid $ref_aid" );
            return false;
        }
        if ( count( $refs ) === 0 ) return false;

        $vals = [];
        foreach( $refs as $ref ) {
            $item = self::get_by_id_from_list( $ref[$val_dir], $items, true )['object'];
            $vals[$ref[$val_dir]] = self::get_val_on_item( $item, $val_aid, $bid );
        }

        return $vals;
    }

    //-----------------------------------------------------------------------------------
    //  General Values
    //-----------------------------------------------------------------------------------

    public static function get_update_item_set( $item ) {
        foreach( $item['values'] as $key => $value ) {
            if ( empty( $value['data'] ) ) {
                unset( $item['values'][$key]['data'] );
            }
        }

        return [
            'folder_id' => $item['folder_id'],
            'values' => $item['values'],
            'parent_id' => $item['parent_id'],
        ];
    }

    public static function get_val_on_item( $item, $aid, $bid ) {
        // Get Attribute
        $atts = Infinity::get_board_atts( $bid, true );
        $att = self::get_by_id_from_list( $aid, $atts, true )['object'];
        $type = $att['type'];

        // Get Value on Item
        $val_match = self::get_att_value_from_item( $aid, $item, false );
        if ( $val_match === false ) return false;

        $value_set = $item['values'][$val_match['key']]['data'];
        if ( $type === 'label' ) {
            $vals = [];
            foreach ( $value_set as $val ) {
                $vals[] = self::get_label_name_from_id( $val, $att, $item['id'] );
            }

            return $vals;
        } else {
            return $value_set;
        }
    }

    public static function remove_label_on_item( $val, $aid, $item, $bid, $clear_cache = true ) {
        $fid = $item['folder_id'];

        // Get Attribute
        $atts = Infinity::get_board_atts( $bid, true );
        $att = self::get_by_id_from_list( $aid, $atts, true )['object'];
        if ( $att['type'] !== 'label' ) throw new \Exception( "{$att['id']} is type {$att['type']} instead of label" );

        // Get Value on Item
        $val_match = self::get_att_value_from_item( $aid, $item, false );
        if ( $val_match === false ) return $item;
        $value_set = $item['values'][$val_match['key']];
        $orig_set = $value_set;
        if ( ! is_array( $val ) ) $val = [$val];
        $data_type = ( self::is_infinity_id( $val[0] ) ) ? 'ids' : 'names';

        if ( $data_type === 'ids' ) {
            $value_set = self::remove_val_by_label_ids( $value_set, $val );
        } else {
            $value_set = self::remove_val_by_label_name( $value_set, $val, $att );
        }

        if ( $value_set !== $orig_set ) {
            if ( $val_match === false ) {
                $item['values'][] = $value_set;
            } else {
                $item['values'][$val_match['key']] = $value_set;
            }

            LogIt::LogActivity( "Single Update Attr {$att['name']} on Item {$item['id']}" );
            $iid = $item['id'];
            $item = self::get_update_item_set( $item );
            if ( $clear_cache === true ) Cache::tags(['Infinity'])->forget("Get-Inf-Folder-Items:$bid-$fid-values");
            Infinity::infinity_api_put( "boards/$bid/items/{$iid}", $item );

            return $item;
        }

        return $item;
    }

    public static function update_val_on_item( $val, $aid, $item, $bid, $clear_cache = true ) {
        $fid = $item['folder_id'];

        // Get Attribute
        $atts = Infinity::get_board_atts( $bid, true );
        $att = self::get_by_id_from_list( $aid, $atts, true )['object'];
        $type = $att['type'];

        // Get Value on Item
        $val_match = self::get_att_value_from_item( $aid, $item, false );
        if ( $val_match === false ) {
            $value_set = [
                'data' => [],
                'attribute_id' => $aid,
            ];
        } else {
            $value_set = $item['values'][$val_match['key']];
        }
        $orig_set = $value_set;

        if ( $type === 'label' ) {
            $remove_labels = ( $att['settings']['multiple'] === true ) ? false : true;
            if ( ! is_array( $val ) ) $val = [$val];
            $data_type = ( self::is_infinity_id( $val[0] ) ) ? 'ids' : 'names';

            if ( $data_type === 'ids' ) {
                $value_set = self::update_val_by_label_ids( $value_set, $val, $remove_labels );
            } else {
                $value_set = self::update_val_by_label_name( $value_set, $val, $att, $bid, $remove_labels );
            }
        } else {
            $value_set = self::update_val_by_str( $value_set, $val );
        }

        if ( $value_set !== $orig_set ) {
            if ( $val_match === false ) {
                $item['values'][] = $value_set;
            } else {
                $item['values'][$val_match['key']] = $value_set;
            }

            LogIt::LogActivity( "Single Update Attr {$att['name']} on Item {$item['id']}" );
            $iid = $item['id'];
            $itemUpdate = self::get_update_item_set( $item );
            if ( $clear_cache === true ) Cache::tags(['Infinity'])->forget("Get-Inf-Folder-Items:$bid-$fid-values");
            Infinity::infinity_api_put( "boards/$bid/items/{$iid}", $itemUpdate );

            return $item;
        }

        return $item;
    }

    //-----------------------------------------------------------------------------------
    //  Label Values
    //-----------------------------------------------------------------------------------

    public static function remove_val_by_label_name( $value_set, $vals, $att ) {
        if ( ! is_array( $vals ) ) $vals = [ $vals ];

        $val_ids = [];
        foreach( $vals as $val ) {
            $obj = self::get_label_att_object_by_name( $val, $att, '' );
            $val_ids[] = $obj['label']['id'];
        }

        return self::remove_val_by_label_ids( $value_set, $val_ids );
    }

    public static function remove_val_by_label_ids( $value_set, $vals ) {
        if ( ! empty( $value_set['data'] ) ) {
            $value_set['data'] = array_diff( $value_set['data'], $vals );
        }

        return $value_set;
    }

    public static function select_single_val_by_label_name( $value_set, $keep_val, $remove_vals, &$att ) {
        $remove_vals = array_diff( $remove_vals, [ $keep_val ] );

        $obj = self::get_label_att_object_by_name( $keep_val, $att );
        $keep_id = $obj['label']['id'];

        $remove_ids = [];
        foreach( $remove_vals as $remove_val ) {
            $obj = self::get_label_att_object_by_name( $remove_val, $att );
            if ( isset( $obj['label']['id'] ) ) $remove_ids[] = $obj['label']['id'];
        }

        return self::select_single_val_by_label_ids( $value_set, $keep_id, $remove_vals );
    }

    public static function select_single_val_by_label_ids( $value_set, $keep_val, $remove_vals ) {
        $remove_vals = array_diff( $remove_vals, [ $keep_val ] );
        $value_set = self::remove_val_by_label_ids( $value_set, [ $keep_val ] );
        $value_set = self::update_val_by_label_ids( $value_set, [ $keep_val ], false );

        return $value_set;
    }

    // $val = label name/text
    public static function update_val_by_label_name( $value_set, $vals, &$att, $bid, $remove_labels = false ) {
        if ( ! is_array( $vals ) ) $vals = [ $vals ];
        if ( $att['settings']['multiple'] === false ) $remove_labels = true;

        $val_ids = [];
        foreach( $vals as $val ) {
            $obj = self::get_label_att_object_by_name( $val, $att, $bid );
            $val_ids[] = $obj['label']['id'];
        }

        return self::update_val_by_label_ids( $value_set, $val_ids, $remove_labels );
    }

    // $val = array of label IDs
    public static function update_val_by_label_ids( $value_set, $vals, $remove_labels = false ) {
        if ( ! empty( $value_set['data'] ) ) {
            $data = self::check_label_ids_exists_on_item( $value_set, $vals, $remove_labels );
            if ( $data['match'] === false ) $value_set['data'] = $data['values'];
        } else {
            $value_set['data'] = $vals;
        }

        $value_set['data'] = array_unique( $value_set['data'] );

        return $value_set;
    }

    public static function check_label_name_selected( $val, $item, &$att ) {
        $atid_match = self::get_att_value_from_item( $att['id'], $item, false );
        if ( $atid_match === false ) {
            return false;
        } else {
            $atid_match = $atid_match['key'];
        }

        $value_set = $item['values'][$atid_match];

        return self::check_label_name_selected_on_vals( $val, $value_set, $att );
    }

    public static function check_label_name_selected_on_vals( $val, $value_set, &$att ) {
        $label = self::get_label_att_object_by_name( $val, $att );
        $val = $label['label']['id'];

        return ( in_array( $val, $value_set['data'] ) ) ? true : false;
    }

    public static function check_label_ids_exists_on_item( $value_set, $val, $remove_values = false ) {
        $matches = true;
        $missing = array_diff( $val, $value_set['data'] );
        $new_val = $val;

        if ( ! empty( $missing ) ) {
            $matches = false;
            $new_val = ( $remove_values ) ? $val : array_merge( $value_set['data'], $val );
        } elseif ( $remove_values ) {
            $extra = array_diff( $value_set['data'], $val );
            if ( ! empty( $extra ) ) $matches = false;
        }

        return [
            'match' => $matches,
            'values' => $new_val,
        ];
    }

    public static function generate_light_hex() {
        $dt = '';
        for($o=1;$o<=3;$o++) {
            $dt .= str_pad( dechex( mt_rand( 128, 256 ) ), 2, '0', STR_PAD_LEFT);
        }

        return "#{$dt}";
    }

    //-----------------------------------------------------------------------------------
    //  String Values
    //-----------------------------------------------------------------------------------

    public static function update_val_by_str( $value_set, $val ) {
        if ( ! empty( $value_set['data'] ) ) {
            $data = self::check_str_exists_on_item( $value_set, $val );
            if ( $data['match'] === false ) $value_set['data'] = $data['values'];
        } else {
            $value_set['data'] = $val;
        }

        return $value_set;
    }

    public static function check_str_exists_on_item( $value_set, $val ) {
        if ( $value_set['data'] !== $val ) {
            $matches = false;
            $value_set['data'] = $val;
        }

        return array(
            'match' => $matches,
            'values' => $value_set['data'],
        );
    }

    public static function get_att_value_from_item( $aid, $item, $error = false ) {
        $val_match = array_search( $aid, array_column( $item['values'], 'attribute_id' ) );
        if ( $error === true && $val_match === false ) throw New Exception( "Unable to find \$val_match for $aid on item # {$item['id']}" );

        return ( $val_match === false ) ? false : array(
            'key' => $val_match,
            'value' => $item['values'][$val_match]['data'],
            'object' => $item['values'][$val_match],
        );
    }

    //-----------------------------------------------------------------------------------
    //  Link Values
    //-----------------------------------------------------------------------------------

    public static function get_links_item_object_by_url( $value_set, $val, $full_match = false ) {
        $status = 'matched';
        $val_match = array_search( $val['url'], array_column( $value_set['data'], 'url' ) );

        if ( $val_match === false ) {
            $link = array_merge( array(
                'id' => self::generate_infinity_36_id(),
            ), $val );
            $status = 'unmatched';
            $value_set['data'][] = $link;

            return array(
                'value_set' => $value_set,
                'link' => $link,
                'status' => $status,
            );
        } else {
            if ( $full_match && $val['name'] !== $value_set['data'][$val_match]['name'] ) {
                $status = 'modified';
                $value_set['data'][$val_match] = array_merge( $value_set['data'][$val_match], $val );
            }

            return array(
                'value_set' => $value_set,
                'link' => $value_set['data'][$val_match],
                'status' => $status,
            );
        }
    }

    //-----------------------------------------------------------------------------------
    //  Finding/Matching Item
    //-----------------------------------------------------------------------------------

    public static function get_by_id_from_list( $iid, $list, $error = false ) {
        $match = array_search( $iid, array_column( $list, 'id' ) );
        if ( $error === true && $match === false ) LogIt::throwError( "Unable to find \$match for $iid from list", $list ); //throw New Exception(  );

        return ( $match === false ) ? false : array(
            'key' => $match,
            'object' => $list[$match],
        );
    }

    public static function match_item_to_str_value( $val, $items, $aid ) {
        foreach( $items as $item ) {
            if ( ! isset( $item['values'] ) || $item['deleted'] === true ) continue;

            foreach( $item['values'] as $data ) {
                if ( $data['data'] == $val && $data['attribute_id'] === $aid ) {
                    return $item;
                }
            }
        }

        return false;
    }

    // Need to retire this and replace with above
    public static function match_attr_id_to_item_value( $items, $attr, $aid, $attr_key = 'id' ) {
        if ( ! is_array( $attr ) ) $attr = [$attr_key => $attr];

        foreach( $items as $item ) {
            if ( ! isset( $item['values'] ) || $item['deleted'] === true ) continue;

            foreach( $item['values'] as $data ) {
                if ( $data['data'] == $attr[$attr_key] && $data['attribute_id'] === $aid ) {
                    return $item;
                }
            }
        }

        return false;
    }

    //-----------------------------------------------------------------------------------
    //  Attributes
    //-----------------------------------------------------------------------------------

    public static function get_attr_by_name( $name, $bid ) {
        $atts = Infinity::get_board_atts( $bid, true );
        $att_key = array_search( $name, array_column( $atts, 'name' ) );

        if ( $att_key !== false )
            return $atts[$att_key];

        return false;
    }

    public static function exclude_attr_only_in_folders( $exclude, $atts ) {
        foreach( $atts as $key => $att ) {
            $diff = array_diff( $att['folder_ids'], $exclude );

            if ( empty( $diff ) ) unset( $atts[$key] );
        }

        return $atts;
    }

    public static function keep_attr_only_if_in_folders( $folders, $atts ) {
        if ( ! is_array( $folders ) ) $folders = [$folders];

        foreach( $atts as $key => $att ) {
            $similar = array_intersect( $folders, $att['folder_ids'] );

            if ( empty( $similar ) ) unset( $atts[$key] );
        }

        return $atts;
    }

   public static function match_attr_to_folders( $folders, $atts ) {
        foreach( $folders as $folder ) {
            foreach( $atts as &$att ) {
                if ( ! isset( $att['folder_names'] ) ) {
                    $att['folder_names'] = [];
                    $att['folder_ids'] = [];
                }

                if ( in_array( $att['id'], $folder['attribute_ids'] ) ) {
                    $att['folder_names'][] = $folder['name'];
                    $att['folder_ids'][] = $folder['id'];
                }
            }
        }

        return $atts;
    }

    public static function match_item_by_attr_value( $val, $aid, $items ) {
        foreach( $items as $item ) {
            $match = array_search( $aid, array_column( $item['values'], 'id' ) );

            if ( $match !== false && $item['values'][$match]['data'] == $val ) {
                return $item;
            }
        }

        return false;
    }

    public static function get_attr_from_list( $atts, $id, $key = 'id' ) {
        $att_key = array_search( $id, array_column( $atts, $key ) );

        if ( $att_key !== false )
            return $atts[$att_key];

        return false;
    }

    //-----------------------------------------------------------------------------------
    //   Atribute Labels
    //-----------------------------------------------------------------------------------

    public static function get_label_name_from_id( $id, $attr, $iid = '' ) {
        $label = array_search( $id, array_column( $attr['settings']['labels'], 'id' ) );
        if ( $label === false ) throw New Exception( "Unable to find \$label for $id from attr # {$attr['id']} on item $iid" );

        return $attr['settings']['labels'][$label]['name'];
    }

    public static function get_label_att_object_by_name( $val, &$att, $bid = '' ) {
        if ( $att['type'] !== 'label' ) throw New Exception( "Attribute {$att['id']} !== label" );
        $full_match = true;
        $status = 'matched';
        if ( is_array( $val ) ) {
            $val_atts = array_merge( array(
                'primary' => 'id',
                'by_id' => true,
                'update_name' => true,
            ), $val );
            if ( $val_atts['by_id'] === true ) $full_match = false;
        }

        if ( $full_match ) {
            $att_match = array_search( $val, array_column( $att['settings']['labels'], 'name' ) );
        } else {
            $att_match = array_column( $att['settings']['labels'], 'name' );
            foreach( $att_match as $key => $name ) {
                if ( strpos( $name, $val['id'] ) !== false ) {
                    $att_match = $key;
                    break;
                }
            }

            if ( is_array( $att_match ) ) $att_match = false;
            $val = "{$val['name']} ({$val['id']})";

            if ( $val_atts['update_name'] === true && $att_match !== false && $val !== $att['settings']['labels'][$att_match]['name'] ) {
                $att['settings']['labels'][$att_match]['name'] = $val;
                Infinity::update_attr( $att, $bid, true );
                $status = 'modified';
            }
        }

        if ( $att_match === false ) {
            $label = array(
                'name' => $val,
                'id' => self::generate_infinity_36_id(),
                'color' => self::generate_light_hex(),
            );
            $att['settings']['labels'][] = $label;

            if ( ! empty( $bid ) ) {
                Infinity::update_attr( $att, $bid, true );
                $status = 'added';
            } else {
                $status = 'unmatched';
            }

            return array(
                'label' => $label,
                'status' => $status,
            );
        } else {
            return array(
                'status' => $status,
                'label' => $att['settings']['labels'][$att_match],
            );
        }
    }

    //-----------------------------------------------------------------------------------
    //  ID
    //-----------------------------------------------------------------------------------

    public static function is_infinity_id( $id ) {
        $id = trim( $id );
        return ( substr_count( $id, '-' ) === 4 && strlen( $id ) === 36 ) ? true : false;
    }

    public static function generate_infinity_36_id() {
        $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($str),0, 8) . '-' . substr(str_shuffle($str),0, 4) . '-' . substr(str_shuffle($str),0, 4) . '-' . substr(str_shuffle($str),0, 4) . '-' . substr(str_shuffle($str),0, 12);
    }

    //-----------------------------------------------------------------------------------
    //  Display
    //-----------------------------------------------------------------------------------

    public static function printAtts( $bid, $fid = null ) {
        $atts = Infinity::get_board_atts( $bid, false );

        if ( $fid ) {
            $folders = Infinity::get_board_folders($bid, false);
            $atts = self::match_attr_to_folders($folders, $atts);
            $atts = self::keep_attr_only_if_in_folders( $fid, $atts );
        }

        $keys = array_column( $atts, 'name' );
        $ids = array_column( $atts, 'id' );

        return array_combine( $keys, $ids );
    }
}
