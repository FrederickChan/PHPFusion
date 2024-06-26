<?php

function pm_remove() {
    dbquery("TRUNCATE TABLE " . DB_MESSAGES);
    addnotice('success', fusion_get_locale('712'));
    redirect(FUSION_REQUEST);
}


function pm_post() {
    $locale = fusion_get_locale();

    $inputData = [
        'pm_inbox_limit' => form_sanitizer($_POST['pm_inbox_limit'], '20', 'pm_inbox_limit'),
        'pm_outbox_limit' => form_sanitizer($_POST['pm_outbox_limit'], '20', 'pm_outbox_limit'),
        'pm_archive_limit' => form_sanitizer($_POST['pm_archive_limit'], '20', 'pm_archive_limit'),
        'pm_email_notify' => form_sanitizer($_POST['pm_email_notify'], '1', 'pm_email_notify'),
        'pm_save_sent' => form_sanitizer($_POST['pm_save_sent'], '1', 'pm_save_sent'),
    ];

    if (Defender::safe()) {
        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=:settings_value WHERE settings_name=:settings_name",
                [
                    ':settings_value' => $settings_value,
                    ':settings_name' => $settings_name,
                ]);
        }

        addnotice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }
}
