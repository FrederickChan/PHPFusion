<?php
function pf_reg_post() {
    $locale = fusion_get_locale();
    $inputData = array(
        'login_method' => sanitizer('login_method', '0', 'login_method'),
        'enable_registration' => post('enable_registration') ? 1 : 0,
        'email_verification' => post('email_verification') ? 1 : 0,
        'admin_activation' => post('admin_activation') ? 1 : 0,
        'enable_terms' => post('enable_terms') ? 1 : 0,
        'gateway' => post('gateway') ? 1 : 0,
        'gateway_method' => sanitizer('gateway_method', 0, 'gateway_method'),
    );

    if (fusion_safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name' => $settings_name,
            ]);
        }

        add_notice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}
