<?php

defined('IN_FUSION') || exit;

/**
 * The Panel Editor Form
 */
function panel_form() {

    $locale = fusion_get_locale();

    $settings = fusion_get_settings();

    $data = do_panel_data();

    if ( $action = get_action() ) {

        $panel_id = get_panel_id();

        if ( verify_panel($panel_id) ) {

            if ( $action == 'setstatus' ) {

                dbquery('UPDATE ' . DB_PANELS . ' SET panel_status=:status WHERE panel_id=:id',
                        [ ':id' => $panel_id, ':status' => get_panel_status() ]);

                redirect(FUSION_SELF . fusion_get_aidlink());

            }
            else if ( $action == 'delete' ) {

                $data = dbarray(dbquery('SELECT panel_side, panel_order FROM ' . DB_PANELS . ' WHERE panel_id=:id',
                                        [ ':id' => $panel_id ]));

                dbquery('DELETE FROM ' . DB_PANELS . ' WHERE panel_id=:id', [ ':id' => $panel_id ]);

                dbquery('UPDATE ' . DB_PANELS . " SET panel_order=panel_order-1 WHERE panel_side='" . intval($data['panel_side']) . "' AND panel_order>='" . intval($data['panel_order']) . "'");

                add_notice('success', $locale['489']);

                redirect(FUSION_SELF . fusion_get_aidlink());

            }
            else if ( $action == 'edit' ) {
                $data = load_panels($panel_id);
            }
        }
    }

    if ( admin_post('panel_preview') && $settings['allow_php_exe'] ) {
        $panel_title = form_sanitizer($_POST['panel_name'], '', 'panel_name');
        if ( \defender::safe() ) {
            ob_start();
            echo openmodal('cp_preview', $panel_title);
            if ( fusion_get_settings('allow_php_exe') ) {
                ob_start();
                eval('?>' . stripslashes($_POST['panel_content']) . '<?php ');
                $eval = ob_get_contents();
                ob_end_clean();
                echo $eval;
            }
            else {
                echo '<p>' . nl2br(parse_textarea($_POST['panel_content'], FALSE, FALSE)) . "</p>";
            }
            echo closemodal();
            add_to_footer(ob_get_contents());
            ob_end_clean();
        }
        $data = [
            'panel_id'          => form_sanitizer($_POST['panel_id'], 0, 'panel_id'),
            'panel_name'        => form_sanitizer($_POST['panel_name'], '', 'panel_name'),
            'panel_filename'    => form_sanitizer($_POST['panel_filename'], '', 'panel_filename'),
            'panel_side'        => form_sanitizer($_POST['panel_side'], '', 'panel_side'),
            'panel_content'     => form_sanitizer($_POST['panel_content'], '', 'panel_content'),
            'panel_restriction' => form_sanitizer($_POST['panel_restriction'], '', 'panel_restriction'),
            'panel_url_list'    => form_sanitizer($_POST['panel_url_list'], '', 'panel_url_list'),
            'panel_display'     => form_sanitizer($_POST['panel_display'], '', 'panel_display'),
            'panel_access'      => form_sanitizer($_POST['panel_access'], iGUEST, 'panel_access'),
            'panel_languages'   => ! empty($_POST['panel_languages']) ? form_sanitizer($_POST['panel_languages'],
                                                                                       '',
                                                                                       'panel_languages') : LANGUAGE
        ];
    }

    // Cancellation Form
    echo openform('cancelFrm', 'POST') . closeform();

    // Panel Editing Form
    echo openform('panelFrm', 'POST');
    echo '<div class="row"><div class="col-xs-12 col-sm-12 col-md-8">';
    echo form_hidden('panel_id', '', $data['panel_id']);
    echo form_text('panel_name', $locale['452'], $data['panel_name'], [
        'inline'   => FALSE,
        'required' => TRUE
    ]);
    echo '<div class="row"><div class="col-xs-12 col-sm-12 col-md-6">';
    echo form_select('panel_filename', $locale['453'], $data['panel_filename'], [
        'options'     => panel_opts(),
        'inline'      => FALSE,
        'width'       => '100%',
        'inner_width' => '100%',
    ]);
    echo '</div><div class="col-xs-12 col-sm-12 col-md-6">';
    echo form_select('panel_side', $locale['457'], $data['panel_side'], [
        'options'     => panel_section(),
        'inline'      => FALSE,
        'width'       => '100%',
        'inner_width' => '100%',
    ]);
    echo '</div></div>';
    echo "<div id='pgrp'>";
    echo form_textarea('panel_content', $locale['455'], $data['panel_content'], [
        'html'      => $settings['allow_php_exe'],
        'form_name' => 'panel_form',
        'autosize'  => TRUE,
        'preview'   => $settings['allow_php_exe'],
        'descript'  => $settings['allow_php_exe']
    ]);
    echo '</div>';
    echo form_select('panel_restriction', $locale['468'], $data['panel_restriction'], [
        'options' => panel_include_opts(),
        'inline'  => FALSE
    ]);
    echo "<div id='panel_url_list-grp'>";
    echo form_textarea('panel_url_list', $locale['462'], $data['panel_url_list'], [
        'inline'   => FALSE,
        'required' => FALSE,
    ]);
    echo "<div class='text-smaller'>" . $locale['463'] . " <br />
        /home.php <br />
        /infusions/news* <br />
        /infusions/news/news.php <br />
        /infusions/forum* <br />
        /infusions/forum/index.php <br />
        </div>";
    echo "</div>";
    echo form_hidden('panel_display', '', $data['panel_display']);
    echo '</div><div class="col-xs-12 col-sm-12 col-md-4">';
    echo form_select('panel_access', $locale['458'], $data['panel_access'], [
        'options' => panel_access_opts()
    ]);
    echo "<label class='label-control m-b-10'>" . $locale['466'] . "</label>";

    $languages = ! empty($data['panel_languages']) && stristr($data['panel_languages'],
                                                              '.') ? explode('.',
                                                                             $data['panel_languages']) : $data['panel_languages'];
    if ( ! empty($languages) && is_array($languages) ) {
        $languages = array_flip($languages);
    }

    foreach ( fusion_get_enabled_languages() as $language_key => $language_name ) {

        if ( ! empty($languages) && is_array($languages) ) {
            $value = isset($languages[ $language_key ]) ? $language_key : '';
        }
        else {
            $value = $languages == $language_key ? $languages : '';
        }

        echo form_checkbox('panel_languages[]', $language_name, $value, [
            'class'         => 'm-b-0',
            'value'         => $language_key,
            'reverse_label' => TRUE,
            'input_id'      => 'panel_lang-' . $language_key
        ]);
    }

    echo '</div></div>';
    echo closeform();

    add_to_jquery("
        " . ( ( $data['panel_restriction'] == 3 || $data['panel_restriction'] == 2 ) ? "$('#panel_url_list-grp').hide();" : '' ) . "       
        " . ( ( ! empty($data['panel_filename']) && $data['panel_filename'] !== 'none' ) ? "$('#pgrp').hide();" : "$('#pgrp').show();" ) . "
        $('#panel_restriction').bind('change', function(e) {
            if ($(this).val() == '3' || $(this).val() == '2') { $('#panel_url_list-grp').hide(); } else { $('#panel_url_list-grp').show(); }
        });
        $('#panel_filename').bind('change', function(e) {
            var panel_val = $(this).val();
            if ($(this).val() !='none') { $('#pgrp').hide(); } else { $('#pgrp').show(); }
        });");


}

/**
 * @return array|null
 */
function do_panel_data()
: ?array {

    $data = [
        'panel_id'          => 0,
        'panel_name'        => '',
        'panel_filename'    => '',
        'panel_content'     => '', //stripslashes($data['panel_content']),
        'panel_type'        => 'php',
        'panel_side'        => 0,
        'panel_order'       => 0,
        'panel_access'      => 0,
        'panel_display'     => 0,
        'panel_status'      => 0,
        'panel_url_list'    => '',
        'panel_restriction' => 3,
        'panel_languages'   => LANGUAGE,
    ];

    $locale = fusion_get_locale();

    if ( admin_post('savepanel') ) {

        $data = [
            'panel_id'          => sanitizer('panel_id', '', 'panel_id'),
            'panel_name'        => sanitizer('panel_name', '', 'panel_name'),
            'panel_side'        => sanitizer('panel_side', 0, 'panel_side'),
            'panel_access'      => sanitizer('panel_access', 0, 'panel_access'),
            'panel_filename'    => sanitizer('panel_filename', 0, 'panel_filename'),
            'panel_content'     => '',
            'panel_type'        => 'file',
            // need to add fourth option. only show in front page.
            'panel_restriction' => sanitizer('panel_restriction', '0', 'panel_restriction'),
            'panel_languages'   => sanitizer([ 'panel_languages' ], '', 'panel_languages')
        ];

        // panel name is unique
        $result = dbcount('(panel_id)',
                          DB_PANELS,
                          'panel_name=:name AND panel_id !=:id',
                          [ ':id' => $data['panel_id'], ':name' => $data['panel_name'] ]
        );

        if ( $result ) {
            fusion_stop($locale['471']);
        }

        if ( fusion_safe() ) {
            // Panel content
            if ( $data['panel_filename'] == 'none' ) {

                $data['panel_type'] = 'php';

                $data['panel_content'] = addslashes(sanitizer('panel_content', '', 'panel_content'));

                if ( ! $data['panel_content'] ) {
                    $data['panel_content'] = "opentable(\"name\");" . "echo \"" . $locale['469a'] . "\";" . 'closetable();';

                    if ( $data['panel_side'] == 1 || $data['panel_side'] == 4 ) {
                        $data['panel_content'] = "openside(\"name\");" . "echo \"" . $locale['469a'] . "\";" . 'closeside();';
                    }
                }
            }

            if ( $panel_languages = sanitize_array(post([ 'panel_languages' ])) ) {
                $data['panel_languages'] = implode('.', $panel_languages);
            }

            // 3, show on all, 2 = show on home page. 1 = exclude , 0 = include
            //  post 0 to include all , 1 to exclude all, show all.
            if ( $data['panel_restriction'] == 3 ) { // show on all
                $data['panel_display'] = ( $data['panel_side'] !== 1 && $data['panel_side'] !== 4 ) ? 1 : 0;
                $data['panel_url_list'] = '';
            }
            else if ( $data['panel_restriction'] == 2 ) {
                // show on homepage only
                $data['panel_display'] = 0;
                $data['panel_url_list'] = '';
                if ( $data['panel_side'] == 1 || $data['panel_side'] == 4 ) {
                    $data['panel_url_list'] = fusion_get_settings('opening_page'); // because 1 and 4 directly overide panel_display.
                }
            }
            else {
                // require panel_url_list in this case
                $data['panel_url_list'] = sanitizer('panel_url_list', '', 'panel_url_list');

                if ( $data['panel_url_list'] ) {
                    $data['panel_url_list'] = str_replace(',', "\r", $data['panel_url_list']);
                    if ( $data['panel_restriction'] == 1 ) { // exclude mode
                        $data['panel_display'] = ( $data['panel_side'] !== 1 && $data['panel_side'] !== 4 ) ? 1 : 0;
                    }
                    else { // include mode
                        $data['panel_display'] = ( $data['panel_side'] !== 1 && $data['panel_side'] !== 4 ) ? 1 : 0;
                    }
                }
                else {
                    fusion_stop($locale['475']);
                }
            }

            if ( $data['panel_id'] && verify_panel($data['panel_id']) ) {
                // Panel Update
                dbquery_insert(DB_PANELS, $data, 'update');
                add_notice('success', $locale['482']);
            }
            else {
                // Panel Save
                $data['panel_order'] = 1;

                $res = dbquery('SELECT panel_order FROM ' . DB_PANELS . ' WHERE panel_side=:value ORDER BY panel_order DESC LIMIT 1',
                               [ ':value' => (int)$data['panel_side'] ]);
                if ( dbrows($res) ) {
                    $rows = dbarray($res);
                    $data['panel_order'] = $rows['panel_order'] + 1;
                }
                dbquery_insert(DB_PANELS, $data, 'save');
                add_notice('success', $locale['485']);

            }
            // Regulate Panel Ordering
            $result = dbquery('SELECT panel_id, panel_side FROM ' . DB_PANELS . ' ORDER BY panel_side ASC, panel_order ASC');
            if ( dbrows($result) ) {
                $current_side = 0;
                $order = '';
                while ( $data = dbarray($result) ) {
                    $panel_id = $data['panel_id'];
                    $panel_side = $data['panel_side'];
                    if ( $panel_side !== $current_side ) {
                        $order = 0;
                    }
                    $order = $order + 1;
                    dbquery('UPDATE ' . DB_PANELS . ' SET panel_order=:order WHERE panel_id=:panel_id',
                            [ ':order' => $order, ':panel_id' => $panel_id ]);
                    $current_side = $panel_side;
                }

            }

            redirect(ADMIN_CURRENT_DIR);
        }
    }

    return $data;
}
