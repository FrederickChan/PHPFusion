<?php

function time_post() {

    $locale = fusion_get_locale();

    if (isset($_POST['savesettings'])) {
        $inputData = [
            'shortdate' => sanitizer('shortdate', '', 'shortdate'),
            'longdate' => sanitizer('longdate', '', 'longdate'),
            'forumdate' => sanitizer('forumdate', '', 'forumdate'),
            'newsdate' => sanitizer('newsdate', '', 'newsdate'),
            'subheaderdate' => sanitizer('subheaderdate', '', 'subheaderdate'),
            'timeoffset' => sanitizer('timeoffset', '', 'timeoffset'),
            'serveroffset' => sanitizer('serveroffset', '', 'serveroffset'),
            'default_timezone' => sanitizer('default_timezone', '', 'default_timezone'),
            'week_start' => sanitizer('week_start', 0, 'week_start'),
        ];

        if (fusion_safe()) {
            foreach ($inputData as $settings_name => $settings_value) {
                dbquery("UPDATE " . DB_SETTINGS . " SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                    ':settings_value' => $settings_value,
                    ':settings_name' => $settings_name,
                ]);
            }

            addnotice("success", $locale['900']);
            redirect(FUSION_REQUEST);
        }
    }
}

