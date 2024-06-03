<?php

use PHPFusion\Panels;

defined('IN_FUSION') || exit;


function get_panel_id() {

    static $value;
    if ( empty($value) ) {
        $value = get('panel_id');
    }

    return $value;
}

/**
 * Panel array
 *
 * @param int|null $panel_id
 *
 * @return array|string
 */
function get_panel_list(int $panel_id = NULL) {

    $panel_list = [];
    $panels = [];
    $result = dbquery('SELECT panel_id, panel_filename FROM ' . DB_PANELS . ' ORDER BY panel_id');
    while ( $data = dbarray($result) ) {
        $panels[] = $data['panel_filename'];
    }

    if ( ! empty($panels) ) {
        $temp = makefilelist(INFUSIONS, '.|..|index.php', TRUE, 'folders');
        foreach ( $temp as $folder ) {
            if ( strstr($folder, '_panel') ) {
                if ( ! in_array($folder, $panels) ) {
                    $panel_list[] = ucwords(str_replace('_', ' ', $folder));
                }
            }
        }
    }

    if ( $panel_id != NULL ) {
        return $panel_list[ $panel_id ];
    }

    sort($panel_list);

    return (array)$panel_list;
}

/**
 * Load from DB_PANELS table
 *
 * @param null $key
 *
 * @return array|null
 */
function load_panels($key = NULL)
: ?array {

    static $list = [];

    if ( empty($list) ) {
        $result = dbquery('SELECT * FROM ' . DB_PANELS . ' ORDER BY panel_side ASC, panel_order ASC');
        if ( dbrows($result) ) {
            while ( $data = dbarray($result) ) {
                $list[ $data['panel_side'] ][] = $data;
            }
        }

        // $_list = [];
        // $list_tmp = flatten_array($list);
        // foreach ( $list_tmp as $listing ) {
        //     $_list[] = $listing['panel_filename'];
        // }
        //
        // // need to merge with the panels
        // $k = 1;
        // $temp = makefilelist(INFUSIONS, '.|..|index.php', TRUE, 'folders');
        // foreach ( $temp as $folder ) {
        //     if ( strstr($folder, '_panel') ) {
        //         if ( ! in_array($folder, $_list) ) {
        //             $panel = Panels::panelInfo($folder);
        //             $list[0][] =
        //                 [
        //                     'panel_id'     => 'a_' . $k,
        //                     'panel_name'   => $panel['title'],
        //                     'panel_access' => 0,
        //                     'panel_status' => 0,
        //                 ];
        //
        //             $k++;
        //         }
        //     }
        // }
    }

    return $key === NULL ? $list : ( $list[ $key ] ?? NULL );
}


function get_page() {

    static $value;

    $allowed_value = [ 'list', 'form' ];

    if ( empty($value) ) {

        if ( $value = get('pg') ) {

            if ( in_array($value, $allowed_value) ) {
                return $value;
            }

            return $allowed_value[0];
        }
    }

    return $value;
}

/**
 * Return panel positions array
 *
 * @return array
 */
function panel_section()
: array {

    $locale = fusion_get_locale();

    return [
        0 => $locale['429'],
        1  => $locale['420'],
        2  => $locale['421'],
        3  => $locale['425'],
        4  => $locale['422'],
        5  => $locale['426'],
        6  => $locale['427'],
        7  => $locale['428a'],
        8  => $locale['428b'],
        9  => $locale['428c'],
        10 => $locale['428d']
    ];
}

/**
 * Return restrictions type array
 *
 * @return array
 */
function panel_include_opts()
: array {

    $locale = fusion_get_locale();

    return [
        3 => $locale['459'], // Display panel on all pages
        2 => $locale['467'], // Display on Opening Page only
        1 => $locale['464'], // Exclude on these pages only
        0 => $locale['465'], // Include on these pages only
    ];
}

/**
 * Return user groups array
 *
 * @return array
 */
function panel_access_opts() {

    $ref = [];
    $user_groups = getusergroups();

    foreach ( $user_groups as $key => $user_group ) {
        $ref[ $user_group[0] ] = $user_group[1];
    }

    return $ref;
}

/**
 * Checks if a panel id is valid
 *
 * @param $id
 *
 * @return bool
 */
function verify_panel($id)
: bool {

    if ( isnum($id) ) {
        return dbcount('(panel_id)', DB_PANELS, 'panel_id=:id', [ ':id' => $id ]);
    }

    return FALSE;
}

function get_panel_side() {

    static $sides;
    if ( empty($sides) ) {
        if ( $sides = get('panel_side') ) {
            if ( in_array($sides, array_flip(panel_section())) ) {
                return $sides;
            }

            return 0;
        }
    }

    return $sides;
}

function get_action() {

    static $action;
    if ( empty($action) ) {
        $action = get('action');
    }

    return $action;
}

function get_panel_status() {

    static $panel_status;
    if ( empty($panel_status) ) {
        $panel_status = get('status');
    }

    return $panel_status;
}

function get_status() {

    static $status;
    if ( empty($status) ) {
        $status = get('status');
    }

    return $status;
}

/**
 * Return list of panels
 *
 * @return array
 */
function panel_opts()
: array {

    $panel_data = load_panels();

    $current_panels = [];
    foreach ( $panel_data as $side => $panels ) {
        foreach ( $panels as $data ) {
            $current_panels[ $data['panel_filename'] ] = $data['panel_filename'];
        }
    }

    // unset this panel if edit mode.
    if ( isset($_GET['panel_id']) && isnum($_GET['panel_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ) {
        unset($current_panels[ $panel_data['panel_filename'] ]);
    }

    return Panels::getAvailablePanels($current_panels);
}
