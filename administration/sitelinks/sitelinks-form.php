<?php

defined('IN_FUSION') || exit;

function remove_menu_action()
: bool {

    if (check_post('del_menu')) {

        $refs = get('refs');

        $res = dbquery('SELECT link_id FROM ' . DB_SITE_LINKS . ' WHERE link_position=:refs',
                       [ ':refs' => $refs ]);

        if ( ! dbrows($res) ) {
            // if there are no links in this cat, just remove it.
            if ( $refs != 0 ) {
                // delete the links
                dbquery('DELETE FROM ' . DB_SITE_MENU . ' WHERE menu_id=:id', [ ':id' => $refs ]);
            }
            return FALSE;
        }
        else {

            $ids = [];
            while ( $rows = dbarray($res) ) {
                $ids[] = $rows['link_id'];
            }

            // now we need to see user action
            if ( sanitizer('link_opt', 0, 'link_opt') == 1 ) {

                if ( $move_pos = sanitizer('link_position', '', 'link_position') ) {
                    if (
                        $move_pos <= 2 or dbcount('(menu_id)',
                                                  DB_SITE_MENU,
                                                  'menu_id=:id',
                                                  [ ':id' => $move_pos ])
                    ) {
                        // then move all here
                        dbquery('UPDATE ' . DB_SITE_LINKS . " SET link_position=:new_id WHERE link_id IN ('" . implode("','",
                                                                                                                       $ids) . "')",
                                [ ':new_id' => $move_pos ]);
                        //    delete the menu
                    }
                }
                // this one requires to move to another
            }
            else {
                // now we delete all the links
                dbquery('DELETE FROM ' . DB_SITE_LINKS . " WHERE link_id IN ('" . implode("','", $ids) . "')");
            }

            if ( $refs != 0 ) {
                // delete the links
                dbquery('DELETE FROM ' . DB_SITE_MENU . ' WHERE menu_id=:id', [ ':id' => $refs ]);
            }

            redirect(ADMIN_CURRENT_DIR);
        }

    }

    return TRUE;
}


function display_menu_form()
: string {

    $data = [
        'menu_id'   => 0,
        'menu_name' => '',
        'menu_ipp'  => 8,
    ];

    if ( check_post('savemenu') ) {
        $data = [
            'menu_id'   => sanitizer('menu_id', '', 'menu_id'),
            'menu_name' => sanitizer('menu_name', '', 'menu_name'),
            'menu_ipp'  => sanitizer('menu_ipp', '', 'menu_ipp')
        ];
        if ( fusion_safe() ) {
            $mode = 'save';
            $message = 'Menu has been created';
            if ( $data['menu_id'] ) {

                $mode = 'update';
                $message = 'Menu has been updated';
            }
            dbquery_insert(DB_SITE_MENU, $data, $mode);

            if ( $data['menu_id'] <= 3 ) {
                dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=:value WHERE settings_name=:name",
                        [ ':name' => 'links_per_page', ':value' => $data['menu_ipp'] ]);
            }

            add_notice('success', $message);
            redirect(FUSION_REQUEST);
        }
        else {
            $_menu_open = TRUE;
        }
    }

    else if ( check_post('deletemenu') ) {

        if ( $refs = get('refs') ) {

            if ( remove_menu_action() ) {

                // if have menu show pop up
                $link_position_opts = link_position_opts();
                unset($link_position_opts[ $refs ]);

                add_to_footer(
                    openmodal('linkwarn', '') .
                    openform('delMenuFrm', 'POST') .
                    form_hidden('deletemenu', '', '1') .
                    '<h3 class="m-b-20">Remove Links</h3>' .
                    '<div class="spacer-xs">' .
                    form_checkbox('link_opt',
                                  'There are links in the current menu. Please choose the following options to proceed:',
                                  '',
                                  [
                                      'options' => [
                                          0 => 'Remove all the links together with this menu',
                                          1 => 'Move the links to another menu, before removing this menu'
                                      ],
                                      'type'    => 'radio'
                                  ]) .
                    '<div id="mmp" style="display:none;">' .
                    '<div class="spacer-sm">' .
                    form_select('link_position', 'Choose menu to move links to', '', [
                        'options'     => $link_position_opts,
                        'inner_width' => '100%',
                        'width'       => '100%'
                    ]) .
                    '</div></div></div>' .

                    modalfooter(
                        '<a class="btn btn-default m-r-10" href="' . FORM_REQUEST . '"><span>Cancel</span></a>' .
                        form_button('del_menu', 'Confirm Delete Menu', 'del_menu', [ 'class' => 'btn-danger' ])
                    ) .
                    closeform() .
                    closemodal()
                );
                add_to_jquery("
                    $(document).on('change', 'input[name=\"link_opt\"]', function(e) {
                        $('#mmp').hide();
                        if ( $(this).val() == 1 ) {
                            $('#mmp').show();
                        }
                    });
                    ");

            }

        }
        else {
            redirect(ADMIN_CURRENT_DIR);
        }

    }


    $html = '<div id="newmenu"' . ( ! isset($_menu_open) ? ' style="display:none;"' : '' ) . '>';
    $html .= '<h6>Add New Navigation</h6>';
    $html .= fusion_get_function('openside', '');
    $html .= openform('menuFrm', 'POST');
    $html .= form_hidden('menu_id', '', $data['menu_id']);
    $html .= '<div class="row"><div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">';
    $html .= form_text('menu_name', 'Name', $data['menu_name'], [ 'required' => TRUE ]);
    $html .= form_text('menu_ipp', 'Items per page', $data['menu_ipp'], [ 'required' => TRUE ]);
    $html .= '</div></div>';
    $html .= form_button('savemenu', 'Save Menu', 'savemenu', [ 'class' => 'btn-primary' ]);
    $html .= closeform();
    $html .= fusion_get_function('closeside', '');
    $html .= '</div>';

    $rows = [
        'menu_id'   => get_refs(),
        'menu_name' => 'Default',
        'menu_ipp'  => 8
    ];
    if ( get_refs() != 0 ) {
        $res = dbquery('SELECT * FROM ' . DB_SITE_MENU . ' WHERE menu_id=:id', [ ':id' => $rows['menu_id'] ]);
        if ( dbrows($res) ) {
            $rows = dbarray($res);
        }
    }

    $html .= '<h6>Current Navigation</h6>';
    $html .= fusion_get_function('openside',
                                 'Menu: ' . $rows['menu_name'] . '<small>Edit current menu settings</small>',
                                 TRUE);
    $html .= openform('editMenuFrm', 'POST');
    $html .= '<div class="row"><div class="col-xs-12 col-sm-12 col-md-6">';
    $html .= form_hidden('menu_id', '', $rows['menu_id'], [ 'id' => 'cmenu_id' ]);
    if (get_refs() !=0) {
        $html .= form_text('menu_name', 'Menu Name', $rows['menu_name'], [ 'required' => TRUE, 'deactivate' => ( get_refs() <= 3 ), 'id' => 'cmenu_name' ]);
    }
    $html .= form_text('menu_ipp',
                       'Items per page',
                       $rows['menu_ipp'],
                       [ 'inner_width' => '200px', 'required' => TRUE, 'type' => 'number', 'id' => 'cmenu_ipp' ]);
    $html .= form_button('savemenu',
                         'Update Menu',
                         'savemenu',
                         [ 'class' => 'btn-primary m-r-10', 'id' => 'csavemenu' ]);
    if ($rows['menu_id'] != 0) {
        $html .= form_button('deletemenu', 'Delete Menu', 'deletemenu', [ 'class' => 'btn-danger' ]);

    }
    $html .= '</div></div>';
    $html .= closeform();
    $html .= fusion_get_function('closeside', '');

    return $html;

}

/**
 * Add new link form
 *
 * @return void
 */
function display_link_form() {

    $locale = fusion_get_locale();

    // data
    $data = do_callback_link();

    add_to_jquery(/** @lang JavaScript */ 'slAdmin.slFormJS();');

    if ( $data['link_position'] > 3 ) {
        $data['link_position_id'] = $data['link_position'];
        $data['link_position'] = 4;
    }

    echo openform('linkFrm', 'POST');
    echo "<div class='row'><div class='col-xs-12 col-sm-12 col-md-6'>";

    openside('');
    echo form_hidden('link_id', '', $data['link_id']) .
         form_text('link_name', $locale['SL_0020'], $data['link_name'], [
             'max_length' => 100,
             'required'   => TRUE,
             'error_text' => $locale['SL_0085'],
         ]) .
         form_text('link_icon', $locale['SL_0020a'], $data['link_icon'], [
             'max_length' => 100,
         ]) .
         form_text('link_url', $locale['SL_0021'], $data['link_url'], [
             'error_text' => $locale['SL_0086'],
         ]) .
         form_text('link_order', $locale['SL_0023'], $data['link_order'], [
             'width' => '250px',
             'type'  => 'number'
         ]) .
         form_select('link_position',
                     $locale['SL_0024'],
                     $data['link_position'],
                     [
                         'options'     => link_position_opts(),
                         'width'       => '60%',
                         'inner_width' => '60%',
                     ]) .


         form_checkbox('link_window', $locale['SL_0028'], $data['link_window'],
                       [ 'default_checked' => FALSE, 'reverse_label' => TRUE ]
         );
    closeside();

    echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-3'>\n";

    echo form_select('link_cat', $locale['SL_0029'], $data['link_cat'], [
            'input_id'        => 'link_categories',
            'parent_value'    => $locale['unparent'],
            'width'           => '100%',
            'inner_width'     => '100%',
            'query'           => ( multilang_table('SL') ? "WHERE link_language='" . LANGUAGE . "'" : '' ),
            'disable_opts'    => $data['link_id'],
            'hide_disabled'   => 1,
            'add_parent_opts' => TRUE,
            'db'              => DB_SITE_LINKS,
            'title_col'       => 'link_name',
            'id_col'          => 'link_id',
            'cat_col'         => 'link_cat',
        ]) . form_select('link_status', $locale['SL_0031'], $data['link_status'], [
            'options'     => [ 0 => $locale['unpublish'], 1 => $locale['publish'] ],
            'width'       => '100%',
            'inner_width' => '100%',
        ]) .
         form_select('link_language', $locale['global_ML100'], $data['link_language'], [
             'options'     => fusion_get_enabled_languages(),
             'placeholder' => $locale['choose'],
             'width'       => '100%',
             'inner_width' => '100%',
         ]) .
         form_select('link_visibility', $locale['SL_0022'], $data['link_visibility'], [
             'options'     => link_visibility_opts(),
             'placeholder' => $locale['choose'],
             'width'       => '100%',
             'inner_width' => '100%',
         ]);

    echo '</div></div>';
    echo closeform();
}

function display_link_listing() {

    $locale = fusion_get_locale();

    $html = '<h6>Navigation</h6>';
    $menus = get_menu();
    $tab = [];
    foreach ( $menus as $pos_id => $menu_name ) {

        $tab['title'][ $pos_id ] = $menu_name;
        $tab['id'][ $pos_id ] = $pos_id;

        if ( get_refs() == $pos_id || ! get_refs() && $pos_id == 0 ) {

            $table_api = fusion_table('sitelink', [
                // 'remote_file' => ADMIN . 'includes/?api=sitelinks-list&refs='.$pos_id.'cat=0',
                // 'server_side' => TRUE,
                // 'processing'  => TRUE,
                'col_resize'          => FALSE,
                'col_reorder'         => FALSE,
                'ordering'            => FALSE,
                'responsive'          => TRUE,
                'debug'               => FALSE,
                'row_reorder'         => TRUE,
                'row_reorder_url'     => ADMIN . 'includes/?api=sitelinks-order',
                'row_reorder_success' => 'Menu updated',
                'row_reorder_failed'  => 'Menu was not updated',
                'zero_locale'         => $locale['SL_0062'],
                'columns'             => [
                    // [ 'data' => 'link_checkbox', 'width' => '30', 'orderable' => FALSE ],
                    [ 'data' => 'link_name', 'width' => '45%', 'className' => 'all' ],
                    // [ 'data' => 'link_count', 'width' => '10%', 'className' => 'not-mobile' ],
                    [ 'data' => 'link_status', 'width' => '10%', 'className' => 'not-mobile' ],
                    [ 'data' => 'link_window', 'width' => '10%' ],
                    [ 'data' => 'link_visibility', 'className' => 'not-mobile' ],
                    [ 'orderable' => FALSE ],
                ]
            ]);

            $html .= fusion_get_function('openside', '');
            $html .= openform('fusion_sltable_form', 'POST');
            $html .= "<table id='$table_api' class='table'><thead>";
            $html .= '<tr>';
            // $html .= "<th class='text-center'>" . form_checkbox('check_all',
            //                                                 '',
            //                                                 '',
            //                                                 [ 'input_value' => 1, 'input_id' => 'check_all', 'default_checked' => FALSE ]) . '</th>';
            $html .= '<th>' . $locale['SL_0050'] . '</th>';
            // $html .= '<th>' . $locale['SL_0035'] . '</th>';
            $html .= '<th>' . $locale['SL_0031'] . '</th>';
            $html .= '<th>' . $locale['SL_0071'] . '</th>';
            $html .= '<th>Position</th>';

            $html .= '<th>' . $locale['SL_0051'] . '</th>';

            $html .= '<th></th>';
            $html .= '</tr>';
            $html .= '</thead><tbody>';

            $sql = 'SELECT link_id, link_name, link_url, link_status, link_position, link_window, link_visibility FROM ' . DB_SITE_LINKS . ' WHERE link_position IN ' . ( $pos_id > 0 ? '(' . $pos_id . ')' : '(1,2,3)' ) . ' AND link_cat=:cat ORDER BY link_order';
            $param = [ ':cat' => get_link_cat() ];

            $result = dbquery($sql, $param);

            if ( dbrows($result) ) {

                while ( $data = dbarray($result) ) {

                    $link_count = link_count($data['link_id']);
                    $manage_link = ADMIN_CURRENT_DIR . '&refs=' . get_refs() . '&link_cat=' . $data['link_id'];
                    $edit_link = ADMIN_CURRENT_DIR . '&pg=form&action=edit&id=' . $data['link_id'];
                    $del_link = ADMIN_CURRENT_DIR . '&pg=form&action=del&id=' . $data['link_id'];

                    $html .= '<tr>';
                    $html .= '<td><div class="move-handle" style="display:inline;"><svg viewBox="0 0 32 32"><path d="M9.125 27.438h4.563v4.563H9.125zm9.188 0h4.563v4.563h-4.563zm-9.188-9.125h4.563v4.563H9.125zm9.188 0h4.563v4.563h-4.563zM9.125 9.125h4.563v4.563H9.125zm9.188 0h4.563v4.563h-4.563zM9.125 0h4.563v4.563H9.125zm9.188 0h4.563v4.563h-4.563z"></path></svg></div>
                    <a href="' . $manage_link . '">' . $data['link_name'] . ' ' . ( $link_count ? '<span class="badge"><i class="fad fa-link m-r-10"></i>' . $link_count . '</span>' : '' ) . '</a></td>';
                    $html .= '<td>' . ( $data['link_status'] ? $locale['published'] : $locale['unpublished'] ) . '</td>';
                    $html .= '<td>' . ( $data['link_window'] ? "<i class='fas fa-check'></i>" : "<i class='fas fa-times-circle'></i>" ) . '</td>';
                    $html .= '<td>' . link_position($data['link_position']) . '</td>';
                    $html .= '<td>' . getgroupname($data['link_visibility']) . '</td>';
                    $html .= '<td>
                    <div class="dropdown">
                    <a href="#" data-toggle="dropdown" class="dropdown-toggle"><i class="fa fa-ellipsis-v"></i></a>
                    <ul class="dropdown-menu dropdown-menu-right">
                    <li> <a class="link-edit" href="' . $edit_link . '" data-id="' . $data['link_id'] . '"><i class="fad fa-edit m-r-10"></i>Edit</a></li>
                    <li> <a class="link-edit" href="' . $del_link . '" data-id="' . $data['link_id'] . '"><i class="fad fa-trash m-r-10"></i> ' . $locale['delete'] . '</a></li>
                    <li></li>
                    </ul>
                    </div>
                    </td>';
                    $html .= '</tr>';
                }
            }

            $html .= '</tbody></table>';
            $html .= form_hidden('table_action', '', '');
            $html .= closeform();
            $html .= fusion_get_function('closeside');
        }
    }

    $tab_active = tab_active($tab, 0, 'refs');

    echo opentab($tab, $tab_active, 'sl-menu', TRUE, '', 'refs', [ 'cat', 'refs' ]);
    echo opentabbody($tab['title'][ $tab_active ], $tab['id'][ $tab_active ], $tab_active, TRUE);
    echo display_menu_form();
    echo $html;
    echo closetabbody();
    echo closetab();
}
