<?php

function pf_reg_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('settingsFrm');
    openside();
    echo form_checkbox('enable_terms', $locale['admins_558'], $settings['enable_terms'], ['toggle' => TRUE]);
    closeside();
    openside();
    echo form_checkbox('enable_registration', $locale['admins_551'], $settings['enable_registration'], ['toggle' => TRUE]);
    closeside();
    openside();
    echo form_checkbox('email_verification', $locale['admins_552'], $settings['email_verification'], ['toggle' => TRUE]);
    closeside();
    openside();
    echo form_checkbox('admin_activation', $locale['admins_557'], $settings['admin_activation'], ['toggle' => TRUE]);
    closeside();
    openside('Login<small>Login configurations and methods</small>', TRUE);
    echo form_select('login_method', $locale['admins_699'], $settings['login_method'], array(
        'options' => array('0' => $locale['global_101'], '1' => $locale['admins_699e'], '2' => $locale['admins_699b']),
        'inner_width' => '100%',
    ));
    closeside();
    echo '<h6>Registration Gateway</h6>';
    openside();
    echo form_checkbox('gateway', $locale['admins_security_010'], $settings['gateway'], [
        'toggle' => TRUE,
        'ext_tip' => 'Enables secondary validation to curb bots reaching registration page',
    ]);
    closeside();
    openside($locale['admins_security_011'] . '<small>Registration gateway security page configurations</small>', TRUE);
    echo form_select('gateway_method', $locale['admins_security_011'], $settings['gateway_method'], [
        'options' => [
            0 => $locale['admins_security_012'],
            1 => $locale['admins_security_013'],
            2 => $locale['admins_security_014'],
        ],
        'inner_width' => '100%',
    ]);
    closeside();
    echo '<noscript>';
    echo '<div class="spacer-sm">';
    echo pf_reg_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();
}

function pf_reg_button() {
    $locale = fusion_get_locale();
    return form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
}
