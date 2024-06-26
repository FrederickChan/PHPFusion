<?php
function um_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('settingsFrm', 'POST');

    echo '<h6>User Account Settings</h6>';

    openside();
    echo form_checkbox('hide_userprofiles', $locale['admins_673'], $settings['hide_userprofiles'], ['toggle' => TRUE]);
    closeside();
    openside();
    echo form_checkbox('userthemes', $locale['admins_668'], $settings['userthemes'], ['toggle' => TRUE]);
    closeside();
    openside();
    echo form_checkbox('multiple_logins',
        $locale['admins_1014'],
        $settings['multiple_logins'],
        array('toggle' => TRUE, 'tip' => $locale['admins_1014a']));
    closeside();
    openside();
    echo form_checkbox('username_change', $locale['admins_691'], $settings['username_change'], ['toggle' => TRUE]);
    closeside();
    openside('Username settings<small>User profile and account configuration</small>', TRUE);
    echo form_textarea('username_ban', $locale['admins_649'], $settings['username_ban'], [
        'placeholder' => $locale['admins_411'],
        'autosize' => TRUE,
    ]);

    echo "<div class='row'>
    <label class='control-label col-xs-12' for='photo_max_w'>" . $locale['admins_1008'] . "</label>
    <div class='row'>
    " . form_text('avatar_width', '', $settings['avatar_width'], [
            'class' => 'col-6',
            'max_length' => 4,
            'type' => 'number',
            'prepend' => TRUE,
            'prepend_value' => $locale['admins_1015'],
            'append' => TRUE,
            'append_value' => 'px',
            'width' => '270px',
        ]) . "
    " . form_text('avatar_height', '', $settings['avatar_height'], [
            'class' => 'col-6',
            'max_length' => 4,
            'type' => 'number',
            'prepend' => TRUE,
            'prepend_value' => $locale['admins_1016'],
            'append' => TRUE,
            'append_value' => 'px',
            'width' => '270px',
        ]) . "
    </div></div>";
    $calc_c = calculate_byte($settings['avatar_filesize']);
    $calc_b = $settings['avatar_filesize'] / $calc_c;

    echo "<div class='row'>
    <label class='control-label col-xs-12' for='calc_b'>" . $locale['admins_605'] . "</label>
    <div class='row'>
    " . form_text('calc_b', '', $calc_b, array(
            'required' => TRUE,
            'type' => 'number',
            'error_text' => $locale['error_rate'],
            'width' => '150px',
            'max_length' => 4,
            'class' => 'col-xs-12 col-6',
        )) . "
    " . form_select('calc_c', '', $calc_c, array(
            'class' => 'col-xs-12 col-6',
            'options' => $locale['admins_1020'],
            'placeholder' => $locale['choose'],
            'width' => '180px',
        )) . "
    </div></div>";
    echo form_select('avatar_ratio', $locale['admins_1001'], $settings['avatar_ratio'], [
        'options' => ['0' => $locale['admins_955'], '1' => $locale['admins_956']],
        'inline' => FALSE,
        'width' => '100%',
    ]);
    closeside();

    echo '<h6>Deactivation Settings</h6>';
    openside();
    echo form_checkbox('enable_deactivation', $locale['admins_1002'], $settings['enable_deactivation'], ['toggle' => TRUE]);
    closeside();
    openside('Inactive period<small>' . $locale['admins_1004'] . '</small>', TRUE);
    echo form_text('deactivation_period', $locale['admins_1003'], $settings['deactivation_period'], [
        'max_length' => 3,
        'inner_width' => '150px',
        'type' => 'number',
    ]);
    echo form_text('deactivation_response', $locale['admins_1005'], $settings['deactivation_response'], [
        'max_length' => 3,
        'inner_width' => '150px',
        'type' => 'number',
        'ext_tip' => $locale['admins_1006'],
    ]);
    echo form_select('deactivation_action',
        $locale['1011'],
        $settings['deactivation_action'],
        ['options' => ['0' => $locale['admins_1012'], '1' => $locale['admins_1013']]]);
    closeside();

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    echo um_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();
}

function um_button() {

    $locale = fusion_get_locale();

    return form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
}
