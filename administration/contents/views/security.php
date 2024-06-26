<?php

function sec_view() {

    $settings = fusion_get_settings();
    $locale = fusion_get_locale();
    $is_multilang = count(fusion_get_enabled_languages()) > 1;

    echo openform('settingsFrm');

    echo '<h6>Sessions Configuration</h6>';
    // This opens roadmaps to load balancers.
    openside($locale['admins_security_001'] . '<small>' . $locale['admins_security_002'] . '</small>', TRUE);
    echo form_select('database_sessions', $locale['admins_security_003'], $settings['database_sessions'], [
        'options' => [
            1 => $locale['admins_security_004'].' (Slow)',
            0 => $locale['admins_security_005'].' (Fast)',
        ],
        'inner_width' => '100%',
    ]);
    closeside();

    openside('Administrator Password Timeout<small>Length of each administration login session</small>', TRUE);
    echo '<div class="row"><div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">';
    echo form_text('admin_timeout', 'Duration', $settings['admin_timeout'], [
        'width' => '100%',
    ]);
    echo '</div><div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">';
    echo form_select('admin_timeout_period', 'Period', $settings['admin_timeout_period'], [
        'options' => [
            1 => 'Minute(s)',
            60 => 'Hour(s)',
            1440 => 'Day(s)',
            10080 => 'Week(s)',
            43800 => 'Month(s)',
            525600 => 'Year(s)',
        ],
        'width' => '100%',
        'inner_width' => '100%',
    ]);
    echo '</div>';
    closeside();

    openside($locale['admins_security_008'] . '<small>' . $locale['admins_security_009'] . '</small>', TRUE);
    echo form_text('form_tokens', 'Round robin tokens <small>(Default: 5)</small>', $settings['form_tokens'], [
        'type' => 'number',
    ]);
    closeside();

    echo '<h6>System Maintenance</h6>';
    openside();
    echo form_checkbox('maintenance', $locale['admins_657'], $settings['maintenance'], array('toggle' => TRUE));
    closeside();
    echo '<div id="ac_maintenance" style="display:' . ($settings['maintenance'] ? 'block' : 'none') . ';">';
    openside('Maintenance Settings<small>Configuration for maintenance mode</small>', TRUE);
    //openside();
    echo form_select('maintenance_level', $locale['admins_675'], $settings['maintenance_level'], [
        'options' => [
            USER_LEVEL_ADMIN => $locale['admins_676'],
            USER_LEVEL_SUPER_ADMIN => $locale['admins_677'],
            USER_LEVEL_MEMBER => $locale['admins_678'],
        ],
        'inner_width' => '100%',
    ]);
    echo form_textarea('maintenance_message', $locale['admins_658'], stripslashes($settings['maintenance_message']), [
        'autosize' => TRUE,
        'placeholder'=> "Enter message",
        'form_name' => 'settingsform',
    ]);
    closeside();
    echo '</div>';

    echo '<h6>Captchas</h6>';
    openside();
    echo form_checkbox('display_validation', $locale['admins_553'], $settings['display_validation'], [
        'toggle' => TRUE,
        'ext_tip' => 'Validation code can be used to protect site from spambots',
        'class' => 'm-t-10',
    ]);
    closeside();
    openside('Captcha Options<small>Captcha plugin configurations</small>', TRUE);
    echo form_select('captcha', $locale['admins_693'], $settings['captcha'], [
        'options' => get_captchas(),
        'inner_width' => '100%',
    ]);
    echo "<div id='extDiv' " . ($settings['captcha'] !== 'grecaptcha' ? "style='display:none;'" : '') . ">";
    if (!$settings['recaptcha_public']) {
        $link = [
            'start' => '[RECAPTCHA_LINK]',
            'end' => '[/RECAPTCHA_LINK]',
        ];
        $link_replacements = [
            'start' => "<a href='https://www.google.com/recaptcha/admin' target='_BLANK'>",
            'end' => "</a>\n",
        ];
        $locale['no_keys'] = str_replace($link, $link_replacements, $locale['no_keys']);
        echo "<div class='alert alert-warning m-t-10'><i class='fa fa-google fa-lg fa-fw'></i> " . $locale['no_keys'] . "</div>\n";
    }

    echo form_text('recaptcha_public', $locale['grecaptcha_0100'], $settings['recaptcha_public'], [
        'placeholder' => $locale['grecaptcha_placeholder_1'],
        'required' => FALSE,
    ]);
    echo form_text('recaptcha_private', $locale['grecaptcha_0101'], $settings['recaptcha_private'], [
        'placeholder' => $locale['grecaptcha_placeholder_2'],
        'required' => FALSE,
    ]);
    echo form_select('recaptcha_theme', $locale['grecaptcha_0102'], $settings['recaptcha_theme'], [
        'options' => [
            'light' => $locale['grecaptcha_0102a'],
            'dark' => $locale['grecaptcha_0102b'],
        ],
        'inner_width' => '100%',
    ]);
    echo form_select('recaptcha_type', $locale['grecaptcha_0103'], $settings['recaptcha_type'], [
        'options' => [
            'text' => $locale['grecaptcha_0103a'],
            'audio' => $locale['grecaptcha_0103b'],
        ],
        'type' => 'number',
        'inner_width' => '100%',
        'required' => TRUE,
    ]);
    echo "</div>";
    closeside();

    echo '<h6>File Upload Security</h6>';
    openside();
    echo form_checkbox('mime_check', $locale['admins_699f'], $settings['mime_check'], [
        'toggle' => TRUE,
    ]);
    closeside();
    // upgrade api to flow with checkboxes.
    // fix btn group css.
    // fix secondary btn delete
    echo '<h6>Flood Security</h6>';
    openside();
    echo form_checkbox('flood_autoban', $locale['admins_680'], $settings['flood_autoban'], ['toggle' => TRUE]);
    closeside();
    echo '<div id="a_flood_autoban" style="display:' . ($settings['flood_autoban' ? 'block' : 'none']) . ';">';
    openside();
    echo form_text('flood_interval', $locale['admins_660'], $settings['flood_interval'], array(
        'type' => 'number',
        'inner_width' => '150px',
        'max_length' => 2,
    ));
    closeside();
    echo '</div>';

    echo '<h6>Errors and Debugging</h6>';
    openside();
    echo form_checkbox('error_logging_enabled', $locale['admins_security_015'], $settings['error_logging_enabled'], [
        'toggle' => TRUE,
    ]);
    closeside();
    echo '<div id="a_error_logging_enabled" style="display:' . ($settings['error_logging_enabled' ? 'block' : 'none']) . ';">';
    openside();
    echo form_select('error_logging_method', $locale['admins_security_016'], $settings['error_logging_method'], [
        'options' => [
            'file' => $locale['admins_security_017'],
            'database' => $locale['admins_security_018'],
        ],
        'inner_width' => '100%',
    ]);
    closeside();
    echo '</div>';

    echo '<h6>Badwords</h6>';
    openside();
    echo form_checkbox('bad_words_enabled', $locale['admins_659'], $settings['bad_words_enabled'], [
        'toggle' => TRUE,
    ]);
    closeside();
    echo '<div id="a_bad_words_enabled" style="display:' . ($settings['bad_words_enabled' ? 'block' : 'none']) . ';">';
    openside();
    echo form_text('bad_word_replace', $locale['admins_654'], $settings['bad_word_replace']);
    echo form_textarea('bad_words', $locale['admins_651'], $settings['bad_words'], [
        'placeholder' => $locale['admins_652'],
        'autosize' => TRUE,
    ]);
    closeside();
    echo '</div>';

    echo '<h6>Dangerous Area</h6>';
    openside($locale['admins_694'] . '<small>' . $locale['admins_695'] . '</small>', TRUE);
    echo form_select('allow_php_exe', $locale['admins_694'], $settings['allow_php_exe'], [
        'options' => [
            0 => $locale['disable'],
            1 => $locale['enable'],
        ],
        'inner_width' => '100%',
    ]);
    closeside();
    echo "</div>\n</div>\n";

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    sec_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();

}

function sec_button() {

    $locale = fusion_get_locale();

    echo form_button('clearcache', $locale['admins_security_006'], 'clearcache', ['class' => 'btn-danger']) .
        form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
}

function sec_js() {

    return "
    checkboxToggle('maintenance', 'ac_maintenance');
    
    checkboxToggle('error_logging_enabled', 'a_error_logging_enabled');
    
    checkboxToggle('bad_words_enabled', 'a_bad_words_enabled');
    
    checkboxToggle('flood_autoban', 'a_flood_autoban');
    
    val = $('#captcha').select2().val(); if (val == 'grecaptcha') { $('#extDiv').slideDown('slow'); } else { $('#extDiv').slideUp('slow'); } $('#captcha').bind('change', function() { var val = $(this).select2().val(); if (val == 'grecaptcha') { $('#extDiv').slideDown('slow'); } else { $('#extDiv').slideUp('slow');}});";
}

