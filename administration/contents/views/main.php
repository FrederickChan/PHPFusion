<?php

/*
 * View
 */

use PHPFusion\Quantum\QuantumHelper;
use PHPFusion\QuantumFields;

function main_views() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform("settingsFrm");
    echo '<h6>Site Information</h6>';

    openside('Title & description<small>The details used to identify your publication around the web</small>', TRUE);

    echo form_text('sitename', $locale['admins_402'], $settings['sitename'], [
            'required'   => TRUE,
            'inline'     => FALSE,
            'max_length' => 255,
            'error_text' => $locale['error_value']
        ]).
        form_text('keywords', $locale['admins_410'], $settings['keywords'], [
            'autosize' => TRUE,
            'ext_tip'  => $locale['411']
        ]).
        form_textarea('description', $locale['admins_409'], $settings['description'], [
            'autosize' => TRUE
        ]).
        form_text('opening_page', $locale['admins_413'], $settings['opening_page'], [
            'required'   => TRUE,
            'max_length' => 100,
            'error_text' => $locale['error_value']
        ]);
    closeside();


    openside('Site credentials<small>Set the server information</small>', TRUE);
    echo form_text('siteusername', $locale['admins_406'], $settings['siteusername'], [
            'required'   => TRUE,
            'inline'     => FALSE,
            'max_length' => 32,
            'error_text' => $locale['error_value']
        ]).
        form_text('siteemail', $locale['admins_405'], $settings['siteemail'], [
            'inline'     => FALSE,
            'required'   => TRUE,
            'max_length' => 128,
            'type'       => 'email'
        ]);
    closeside();

    echo '<h6>Site Settings</h6>';
    openside('Publication Settings<small>Set the opening description</small>', TRUE);
    echo form_textarea('siteintro', $locale['admins_407'], stripslashes($settings['siteintro']), [
            'autosize' => TRUE
        ]).
        form_textarea('footer', $locale['admins_412'], stripslashes($settings['footer']), [
            'autosize' => TRUE,
        ]);
    closeside();

    openside('Logo Settings<small>Set the site logo configurations</small>', TRUE);
    echo form_text('sitebanner', $locale['admins_404'], $settings['sitebanner'], [
                'inline'     => FALSE,
                'required'   => TRUE,
                'error_text' => $locale['error_value']
            ]
        );
    echo '<div class="row"><div class="col-3 col-xs-12">'.$locale['admins_414'].'</div>';
    echo '<div class="col-9 col-xs-12">';
    echo '<div class="d-flex gap-2">';
    echo form_select('logoposition_xs', $locale['admins_414a'], $settings['logoposition_xs'], [
            'inline'  => FALSE,
            'flex' => TRUE,
            'options' => [
                'logo-xs-left'   => $locale['left'],
                'logo-xs-center' => $locale['center'],
                'logo-xs-right'  => $locale['right']
            ],
        ]).
        form_select('logoposition_sm', $locale['admins_414b'], $settings['logoposition_sm'], [
            'inline'  => FALSE,
            'flex' => TRUE,
            'options' => [
                'logo-sm-left'   => $locale['left'],
                'logo-sm-center' => $locale['center'],
                'logo-sm-right'  => $locale['right']
            ]
        ]).
        form_select('logoposition_md', $locale['admins_414c'], $settings['logoposition_md'], [
            'inline'  => FALSE,
            'flex' => TRUE,
            'options' => [
                'logo-md-left'   => $locale['left'],
                'logo-md-center' => $locale['center'],
                'logo-md-right'  => $locale['right']
            ]
        ]).
        form_select('logoposition_lg', $locale['admins_414d'], $settings['logoposition_lg'], [
            'inline'  => FALSE,
            'flex' => TRUE,
            'options' => [
                'logo-lg-left'   => $locale['left'],
                'logo-lg-center' => $locale['center'],
                'logo-lg-right'  => $locale['right']
            ]
        ]);
    echo '</div></div></div>';
    closeside();

    openside($locale['admins_419'].'<small>Set the default search configurations</small>', TRUE);
    echo form_select('default_search', "", $settings['default_search'], [
        'options'        => get_default_search_opts(),
        'callback_check' => 'validate_default_search'
    ]);
    closeside();

    openside($locale['admins_401a'].'<small>'.$locale['admins_401b'].'</small>', TRUE);
    echo "<div class='spacer-xs'>\n";
    echo "<i class='fa fa-external-link m-r-10'></i>";
    echo "<span id='display_protocol'>".$settings['site_protocol']."</span>://";
    echo "<span id='display_host'>".$settings['site_host']."</span>";
    echo "<span id='display_port'>".($settings['site_port'] ? ":".$settings['site_port'] : "")."</span>";
    echo "<span id='display_path'>".$settings['site_path']."</span>";
    echo "</div>";
    echo '<div class="flex-row">';
    echo form_select('site_protocol', $locale['admins_426'], $settings['site_protocol'], [
        'inline'     => FALSE,
        'regex'      => 'http(s)?',
        'error_text' => $locale['error_value'],
        'options'    => [
            'http'             => 'http://',
            'https'            => 'https://',
            'invalid_protocol' => $locale['admins_445']
        ]
    ]);
    echo form_text('site_host', $locale['admins_427'], $settings['site_host'], [
        'required'   => TRUE,
        'inline'     => FALSE,
        'max_length' => 255,
        'error_text' => $locale['error_value']
    ]);
    echo form_text('site_path', $locale['admins_429'], $settings['site_path'], [
        'required'   => TRUE,
        'inline'     => FALSE,
        'regex'      => '\/([a-z0-9-_]+\/)*?',
        'max_length' => 255
    ]);
    echo form_text('site_port', $locale['admins_430'], $settings['site_port'], [
        'inline'         => FALSE,
        'required'       => FALSE,
        'placeholder'    => 80,
        'max_length'     => 5,
        'type'           => 'number',
        'inner_width'    => '150px',
        'error_text'     => $locale['430_error'],
        'callback_check' => 'validate_site_port',
        'ext_tip'        => $locale['430_desc']
    ]);
    echo '</div>';
    closeside();
    // Domain names
    openside($locale['admins_444'].'<small>'.nl2br($locale['admins_444a']).'</small>', TRUE);
    $domain_server = str_replace('|', PHP_EOL, $settings['domain_server']);
    echo form_textarea('domain_server', $locale['444b'], $domain_server, [
        'inline'   => FALSE,
        'autosize' => TRUE, 'placeholder' => "example1.com\nexample2.com\n"]);
    closeside();
    // Panel Configurations
    openside('Panel Restrictions<small>Set panel display restrictions and exclusions</small>', TRUE);
    echo '<div class="spacer-sm">'.$locale['admins_424'].'</div>';
    echo form_textarea('exclude_left', $locale['admins_420'], $settings['exclude_left'], [
        'autosize' => TRUE
    ]);
    echo form_textarea('exclude_upper', $locale['admins_421'], $settings['exclude_upper'], ['autosize' => TRUE]);
    echo form_textarea('exclude_aupper', $locale['admins_435'], $settings['exclude_aupper'], ['autosize' => TRUE]);
    echo form_textarea('exclude_lower', $locale['admins_422'], $settings['exclude_lower'], ['autosize' => TRUE]);
    echo form_textarea('exclude_blower', $locale['admins_436'], $settings['exclude_blower'], ['autosize' => TRUE]);
    echo form_textarea('exclude_right', $locale['admins_423'], $settings['exclude_right'], ['autosize' => TRUE]);
    echo form_textarea('exclude_user1', $locale['admins_443a'], $settings['exclude_user1'], ['autosize' => TRUE]);
    echo form_textarea('exclude_user2', $locale['admins_443b'], $settings['exclude_user2'], ['autosize' => TRUE]);
    echo form_textarea('exclude_user3', $locale['admins_443c'], $settings['exclude_user3'], ['autosize' => TRUE]);
    echo form_textarea('exclude_user4', $locale['admins_443d'], $settings['exclude_user4'], ['autosize' => TRUE]);
    closeside();

    echo '<h6>Policy</h6>';
    openside('Privacy Policy Settings<small>Site policy license</small>', TRUE);
    if (count(fusion_get_enabled_languages()) > 1) {

        echo QuantumHelper::quantumMultilocaleFields('privacy_policy', $locale['admins_820'], $settings['privacy_policy'], [
            'autosize'  => 1,
            'form_name' => 'settingsform',
            'function'  => 'form_textarea'
        ]);
    } else {
        echo form_textarea('privacy_policy', $locale['admins_820'], $settings['privacy_policy'], [
            'autosize'  => 1,
            'form_name' => 'settingsform',
        ]);
    }
    closeside();
    openside('Membership Terms<small>Terms of Use and license agreement for new user registration</small>', TRUE);
    if (count(fusion_get_enabled_languages()) > 1) {
        echo QuantumHelper::quantumMultilocaleFields('license_agreement', $locale['admins_559'], $settings['license_agreement'], [
            'form_name' => 'settingsform',
            'input_id'  => 'enable_license_agreement',
            'autosize'  => TRUE,
            'type'      => $settings['tinymce_enabled'] ? 'tinymce' : 'html',
            'function'  => 'form_textarea'
        ]);
    } else {
        echo form_textarea('license_agreement', $locale['admins_559'], $settings['license_agreement'], [
            'form_name' => 'settingsform',
            //'type'      => $settings['tinymce_enabled'] ? 'tinymce' : 'html',
        ]);
    }
    closeside();

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    main_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();
}


/**
 * @param array $inputData
 *
 * @return array
 */
function getServerConfig(array $inputData): array {

    if ( strpos($inputData['site_host'], "/") !== FALSE ) {
        $inputData['site_host'] = explode("/", $inputData['site_host'], 2);
        if ( $inputData['site_host'][1] != "" ) {
            $_POST['site_path'] = "/" . $inputData['site_host'][1];
        }
        $inputData['site_host'] = $inputData['site_host'][0];
    }

    $inputData['siteurl'] = $inputData['site_protocol'] . "://" . $inputData['site_host'] . ( $inputData['site_port'] ? ":" . $inputData['site_port'] : "" ) . $inputData['site_path'];

    if ( ! empty($inputData['domain_server']) ) {
        $inputData['domain_server'] = str_replace(PHP_EOL, '|', $inputData['domain_server']);
    }

    return $inputData;
}

/*
 * Button
 */
function main_button() {
    $locale = fusion_get_locale();
    echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
}

/*
 * Js
 */
function pf_js() {
    return '
    $("#site_protocol").change(function(){$("#display_protocol").text($(this).val())}),
    $("#site_host").keyup(function(){$("#display_host").text($(this).val())}),
    $("#site_path").keyup(function(){$("#display_path").text($(this).val())}),
    $("#site_port").keyup(function(){if(":"==(t=":"+$(this).val())||":0"==t||":90"==t||":443"==t)var t="";$("#display_port").text(t)});
    ';
}

/**
 * Get the default search options
 * with file exists validation of the PHPFusion Search SDK files.
 *
 * @return array
 */
function get_default_search_opts() {

    $locale = fusion_get_locale();

    static $search_opts = [];

    if (empty($search_opts)) {

        $search_opts += [
            'all' => $locale['admins_419a'],
        ];

        if ($handle = opendir(INCLUDES."search/")) {
            while (FALSE !== ($file = readdir($handle))) {
                if (preg_match("/_include.php/i", $file)) {
                    $name = '';
                    $search_name = explode("_", $file);
                    $locale += fusion_get_locale('', LOCALE.LOCALESET."search/".$search_name[1].".php");
                    foreach ($locale as $key => $value) {
                        if (preg_match("/400/i", $key)) {
                            $name = $key;
                        }
                    }

                    if (isset($locale[$name])) {
                        $file = str_replace(['search_', '_include.php', '_include_button.php'], '', $file);
                        $search_opts[$file] = $locale[$name];
                    }
                }
            }
            closedir($handle);
        }

        $infusions = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
        if (!empty($infusions)) {
            foreach ($infusions as $infusions_to_check) {
                if (is_dir(INFUSIONS.$infusions_to_check.'/search/')) {
                    $inf_files = makefilelist(INFUSIONS.$infusions_to_check.'/search/', ".|..|index.php", TRUE, "files");

                    if (!empty($inf_files)) {
                        foreach ($inf_files as $file) {
                            if (preg_match("/_include.php/i", $file)) {
                                $file = str_replace(['search_', '_include.php', '_include_button.php'], '', $file);

                                if (file_exists(INFUSIONS.$infusions_to_check.'/locale/'.LOCALESET."search/".$file.".php")) {
                                    $locale_file = INFUSIONS.$infusions_to_check.'/locale/'.LOCALESET."search/".$file.".php";
                                } else {
                                    $locale_file = INFUSIONS.$infusions_to_check."/locale/English/search/".$file.".php";
                                }

                                $locale += fusion_get_locale('', $locale_file);
                                $search_opts[$file] = !empty($locale[$file.'.php']) ? $locale[$file.'.php'] : $file;
                            }
                        }
                    }
                }
            }
        }

    }

    return (array)$search_opts;
}

/**
 * Default Search file validation rules
 *
 * @param $value
 *
 * @return bool
 */
function validate_default_search($value) {
    $search_opts = get_default_search_opts();

    return (in_array($value, array_keys($search_opts)));
}

/**
 * Site Port validation rules
 *
 * @param $value
 *
 * @return bool
 */
function validate_site_port($value) {
    return ((isnum($value) || empty($value)) && in_array($value, [0, 80, 443]) or $value < 65001);
}

/*
 *
    $("#site_protocol").change(function() {$("#display_protocol").text($(this).val());});
    $("#site_host").keyup(function() {$("#display_host").text($(this).val());});
    $("#site_path").keyup(function() {$("#display_path").text($(this).val());});
    $("#site_port").keyup(function() {
        var value_port = ":"+ $(this).val();
        if (value_port == ":" || value_port == ":0" || value_port == ":90" || value_port == ":443") {
            var value_port = "";
        }
        $("#display_port").text(value_port);
    });
 */
