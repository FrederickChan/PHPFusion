<?php
use PHPFusion\Sessions;

function sec_post() {

    $locale = fusion_get_locale();
    // Save settings after validation
    $inputData = [
        'captcha' => sanitizer('captcha', '', 'captcha'),
        'display_validation' => post('display_validation') ? 1 : 0,
        'allow_php_exe' => post('allow_php_exe') ? 1 : 0,
        'flood_interval' => sanitizer('flood_interval', 15, 'flood_interval'),
        'flood_autoban' => post('flood_autoban') ? 1 : 0,
        'maintenance_level' => sanitizer('maintenance_level', USER_LEVEL_ADMIN, 'maintenance_level'),
        'maintenance' => post('maintenance') ? 1 : 0,
        'maintenance_message' => sanitizer('maintenance_message', '', 'maintenance_message'),
        'bad_words_enabled' => post('bad_words_enabled') ? 1 : 0,
        'bad_words' => post('bad_words'),
        'bad_word_replace' => sanitizer('bad_word_replace', '', 'bad_word_replace'),
        'database_sessions' => sanitizer('database_sessions', 0, 'database_sessions'),
        'form_tokens' => sanitizer('form_tokens', '', 'form_tokens'),
        'mime_check' => post('mime_check') ? 1 : 0,
        'error_logging_enabled' => post('error_logging_enabled') ? 1 : 0,
        'error_logging_method' => sanitizer('error_logging_method', '', 'error_logging_method'),
        'admin_timeout' => sanitizer('admin_timeout', '', 'admin_timeout'),
        'admin_timeout_period' => sanitizer('admin_timeout_period', '', 'admin_timeout_period'),
    ];

    // Validate extra fields
    if ($inputData['captcha'] == 'grecaptcha') {
        // appends captcha settings
        $inputData += [
            'recaptcha_public' => form_sanitizer($_POST['recaptcha_public'], '', 'recaptcha_public'),
            'recaptcha_private' => form_sanitizer($_POST['recaptcha_private'], '', 'recaptcha_private'),
            'recaptcha_theme' => form_sanitizer($_POST['recaptcha_theme'], '', 'recaptcha_theme'),
            'recaptcha_type' => form_sanitizer($_POST['recaptcha_type'], '', 'recaptcha_type'),
        ];
    }

    if (fusion_safe()) {

        foreach ($inputData as $settings_name => $settings_value) {

            dbquery('UPDATE ' . DB_SETTINGS . ' SET settings_value=:settings_value WHERE settings_name=:settings_name',
                [
                    ':settings_value' => $settings_value,
                    ':settings_name' => $settings_name,
                ]);

        }

        addnotice('success', $locale['900']);

    } else {
        addnotice('danger', $locale['901']);
        addnotice('danger', $locale['696']);
        addnotice('danger', $locale['900']);
    }

    redirect(FUSION_REQUEST);
}

function sec_cache() {
    $settings = fusion_get_settings();

    if ($settings['database_sessions']) {
        $session = Sessions::getInstance(COOKIE_PREFIX . 'session');
        $session->_purge();
    } else {
        // Where system has been disabled and instance could not be found, invoke manually.
        dbquery('DELETE FROM ' . DB_SESSIONS);
    }
    addnotice('success', fusion_get_locale('security_007'));
    redirect(FUSION_REQUEST);

}
