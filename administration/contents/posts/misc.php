<?php

function misc_post() {

    $locale = fusion_get_locale();

    if ( admin_post('savesettings') ) {
        $inputData = [
            'tinymce_enabled'        => post('tinymce_enabled') ? 1 : 0,
            'smtp_host'              => form_sanitizer($_POST['smtp_host'], '', 'smtp_host'),
            'smtp_port'              => form_sanitizer($_POST['smtp_port'], '', 'smtp_port'),
            'smtp_auth'              => isset($_POST['smtp_auth']) && ! empty($_POST['smtp_username']) && ! empty($_POST['smtp_password']) ? 1 : 0,
            'smtp_username'          => form_sanitizer($_POST['smtp_username'], '', 'smtp_username'),
            'smtp_password'          => form_sanitizer($_POST['smtp_password'], '', 'smtp_password'),
            'thumb_compression'      => form_sanitizer($_POST['thumb_compression'], '0', 'thumb_compression'),
            'guestposts'             => post('guestposts') ? 1 : 0,
            'comments_enabled'       => post('comments_enabled') ? 1 : 0,
            'comments_per_page'      => form_sanitizer($_POST['comments_per_page'], '10', 'comments_per_page'),
            'ratings_enabled'        => post('ratings_enabled') ? 1 : 0,
            'visitorcounter_enabled' => post('visitorcounter_enabled') ? 1 : 0,
            'rendertime_enabled'     => form_sanitizer($_POST['rendertime_enabled'], '0', 'rendertime_enabled'),
            'comments_avatar'        => post('comments_avatar') ? 1 : 0,
            'comments_sorting'       => form_sanitizer($_POST['comments_sorting'], 'DESC', 'comments_sorting'),
            'index_url_bbcode'       => post('index_url_bbcode') ? 1 : 0,
            'index_url_userweb'      => post('index_url_userweb') ? 1 : 0,
            'create_og_tags'         => post('create_og_tags') ? 1 : 0,
            'devmode'                => post('devmode') ? 1 : 0,
            'update_checker'         => post('update_checker') ? 1 : 0,
            'number_delimiter'       => sanitizer('number_delimiter', '.', 'number_delimiter'),
            'thousands_separator'    => sanitizer('thousands_separator', ',', 'thousands_separator')
        ];

        if ( fusion_safe() ) {
            foreach ( $inputData as $settings_name => $settings_value ) {
                dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=:settings_value WHERE settings_name=:settings_name",
                    [
                        ':settings_value' => $settings_value,
                        ':settings_name'  => $settings_name
                    ]);
            }

            add_notice('success', $locale['900']);
            redirect(FUSION_REQUEST);
        }
    }
}
