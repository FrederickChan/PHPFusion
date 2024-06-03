<?php
defined('IN_FUSION') || exit;


function do_delete_link($link_id) {
    $locale = fusion_get_locale();
    $res = dbquery('SELECT link_id, link_order FROM '.DB_SITE_LINKS.' WHERE link_id=:id', [':id'=>$link_id]);
    if (dbrows($res)) {
        $rows = dbarray($res);

        dbquery('UPDATE ' . DB_SITE_LINKS . ' SET link_order=link_order-1 ' . ( multilang_table('SL') ? "WHERE link_language='" . LANGUAGE . "' AND" : 'WHERE' ) . ' link_order > :order',
                [ ':order' => (int)$rows['link_order'] ]);

        dbquery('DELETE FROM  ' . DB_SITE_LINKS . ' WHERE link_id=:id', [ ':id' => (int)$link_id ]);
        add_notice('success', $locale['SL_0017']);
        redirect(ADMIN_CURRENT_DIR);

    } else {
        admin_exit();
    }
}

/**
 * Updates, Saves and recall link.
 * @return array
 */
function do_callback_link()
: array {

    $locale = fusion_get_locale();

    $data = [
        'link_id'          => 0,
        'link_name'        => '',
        'link_url'         => '',
        'link_icon'        => '',
        'link_cat'         => get_link_cat(),
        'link_language'    => LANGUAGE,
        'link_visibility'  => 0,
        'link_status'      => 1,
        'link_order'       => 0,
        'link_position'    => get_refs(),
        'link_position_id' => 0,
        'link_window'      => 0,
    ];

    if ( get_action() == 'edit' ) {
        if ( $id = get('id', FILTER_VALIDATE_INT) ) {
            $result = dbquery('SELECT * FROM ' . DB_SITE_LINKS . ' WHERE link_id=:id', [ ':id' => $id ]);
            if ( dbrows($result) ) {
                $data = dbarray($result);
            }
            else {
                redirect(ADMIN_ERROR_DIR);
            }
        }
        else {
            redirect(ADMIN_ERROR_DIR);
        }
    }


    if ( admin_post('savelink') ) {

        $data = [
            'link_id'          => sanitizer('link_id', 0, 'link_id'),
            'link_cat'         => sanitizer('link_cat', 0, 'link_cat'),
            'link_name'        => sanitizer('link_name', '', 'link_name'),
            'link_url'         => sanitizer('link_url', '', 'link_url'),
            'link_icon'        => sanitizer('link_icon', '', 'link_icon'),
            'link_language'    => sanitizer('link_language', LANGUAGE, 'link_language'),
            'link_visibility'  => sanitizer('link_visibility', '', 'link_visibility'),
            'link_position'    => sanitizer('link_position', '', 'link_position'),
            'link_status'      => sanitizer('link_status', 0, 'link_status'),
            'link_order'       => sanitizer('link_order', '', 'link_order'),
            'link_window'      => ( check_post('link_window') ? '1' : '0' ),
            'link_position_id' => 0,
        ];

        if ( $data['link_order'] == 0 ) {
            $max_order_query = "SELECT MAX(link_order) 'link_order' FROM " . DB_SITE_LINKS . ' ' . ( multilang_table('SL') ? "WHERE link_language='" . LANGUAGE . "' AND" : 'WHERE' ) . " link_cat='" . $data['link_cat'] . "'";
            $data['link_order'] = dbresult(dbquery($max_order_query), 0) + 1;
        }

        if ( fusion_safe() ) {

            if ( ! empty($data['link_id']) ) {

                dbquery_order(DB_SITE_LINKS,
                              $data['link_order'],
                              'link_order',
                              $data['link_id'],
                              'link_id',
                              $data['link_cat'],
                              'link_cat',
                              multilang_table('SL'),
                              'link_language',
                              'update');

                dbquery_insert(DB_SITE_LINKS, $data, 'update');

                $child = get_child(get_link_index(), $data['link_id']);
                if ( ! empty($child) ) {
                    foreach ( $child as $child_id ) {
                        dbquery('UPDATE ' . DB_SITE_LINKS . " SET link_position='" . $data['link_position'] . "' WHERE link_id='$child_id'");
                    }
                }
                add_notice('success', $locale['SL_0016']);
            }
            else {

                dbquery_order(DB_SITE_LINKS,
                              $data['link_order'],
                              'link_order',
                              $data['link_id'],
                              'link_id',
                              $data['link_cat'],
                              'link_cat',
                              multilang_table('SL'),
                              'link_language',
                              'save');

                dbquery_insert(DB_SITE_LINKS, $data, 'save');
                // New link will not have child
                add_notice('success', $locale['SL_0015']);
            }

            redirect(ADMIN_CURRENT_DIR . '&refs=' . (int)$data['link_position'] . '&cat=' . (int)$data['link_cat']);
        }
    }

    return $data;

}


function get_link_index() {

    static $index;
    if ( empty($index) ) {
        $index = dbquery_tree(DB_SITE_LINKS, 'link_id', 'link_cat');
    }

    return $index;
}

function get_link_tree() {

    static $index;
    if ( empty($index) ) {
        $index = dbquery_tree_full(DB_SITE_LINKS, 'link_id', 'link_cat');
    }

    return $index;
}

function get_menu()
: array {

    $list = [
        0 => 'Default'
    ];

    $res = dbquery('SELECT menu_id, menu_name FROM ' . DB_SITE_MENU . ' WHERE menu_id>3 ORDER BY menu_id');
    if ( dbrows($res) ) {
        while ( $rows = dbarray($res) ) {
            $list[ $rows['menu_id'] ] = $rows['menu_name'];
        }
    }

    return $list;

}

function get_link_cat() {

    static $id;
    if ( empty($id) ) {
        $id = ( get('link_cat', FILTER_VALIDATE_INT) ?? '0' );
    }

    return $id;
}

function get_page() {

    static $p;
    if ( empty($p) ) {
        $p = get('pg');
    }

    return $p;
}

// link position
function get_refs() {

    static $refs;
    if ( empty($refs) ) {
        $refs = get('refs', FILTER_VALIDATE_INT);
    }

    return $refs;
}

function get_action() {

    static $action;
    if ( empty($action) ) {
        $action = get('action');
    }

    return $action;
}

function link_count($link_id)
: int {

    static $res;
    if ( empty($res) ) {
        $res = dbquery_tree(DB_SITE_LINKS, 'link_id', 'link_cat');
    }

    $child = get_child($res, $link_id);
    if ( ! empty($child) ) {
        return count($child);
    }

    return 0;
}

/**
 * @param $pos_id
 *
 * @return mixed|string
 */
function link_position($pos_id) {

    $locale = fusion_get_locale();
    $list = [
        '1' => $locale['SL_0025'],
        '2' => $locale['SL_0026'],
        '3' => $locale['SL_0027']
    ];
    if ( isset($list[ $pos_id ]) ) {
        return $list[ $pos_id ];
    }

    return 'Custom';
}

function link_position_opts()
: array {
    $locale = fusion_get_locale();

    $list = [
        '1' => $locale['SL_0025'],
        '2' => $locale['SL_0026'],
        '3' => $locale['SL_0027']
    ];

    $res = dbquery('SELECT menu_id, menu_name FROM ' . DB_SITE_MENU . ' WHERE menu_id>3 ORDER BY menu_id');
    if ( dbrows($res) ) {
        while ( $rows = dbarray($res) ) {
            $list[ $rows['menu_id'] ] = $rows['menu_name'];
        }
    }

    return $list;
}

function link_visibility_opts()
: array {
    static $visibility_opts = [];
    $user_groups = getusergroups();
    foreach ($user_groups as $user_group) {
        $visibility_opts[$user_group['0']] = $user_group['1'];
    }

    return $visibility_opts;
}

