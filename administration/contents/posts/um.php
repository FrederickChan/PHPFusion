<?php
function um_post() {

    $locale = fusion_get_locale();

    $inputData = [
        'enable_deactivation' => post('enable_deactivation') ? 1 : 0,
        'deactivation_period' => form_sanitizer($_POST['deactivation_period'], '365', 'deactivation_period'),
        'deactivation_response' => form_sanitizer($_POST['deactivation_response'], '14', 'deactivation_response'),
        'deactivation_action' => form_sanitizer($_POST['deactivation_action'], '0', 'deactivation_action'),
        'hide_userprofiles' => post('hide_userprofiles') ? 1 : 0,
        'avatar_filesize' => form_sanitizer($_POST['calc_b'],
                '15',
                'calc_b') * form_sanitizer($_POST['calc_c'], '100000', 'calc_c'),
        'avatar_width' => form_sanitizer($_POST['avatar_width'], '100', 'avatar_width'),
        'avatar_height' => form_sanitizer($_POST['avatar_height'], '100', 'avatar_height'),
        'avatar_ratio' => form_sanitizer($_POST['avatar_ratio'], '0', 'avatar_ratio'),
        'username_change' => post('username_change') ? 1 : 0,
        'username_ban' => stripinput($_POST['username_ban']),
        'userthemes' => post('userthemes') ? 1 : 0,
        'multiple_logins' => post('multiple_logins') ? 1 : 0,
    ];

    if (Defender::safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=:settings_value WHERE settings_name=:settings_name",
                [
                    ':settings_value' => $settings_value,
                    ':settings_name' => $settings_name,
                ]);
        }

        if (!post('enable_deactivation')) {
            $result = dbquery("UPDATE " . DB_USERS . " SET user_status='0' WHERE user_status='5'");
        }

        add_notice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }

}
