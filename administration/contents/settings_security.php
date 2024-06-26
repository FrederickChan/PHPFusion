<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_security.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/settings.php');

$settings = fusion_get_settings();

$contents = [
    'view' => 'sec_view',
    'button' => 'sec_button',
    'js' => 'sec_js',
    'link' => ($admin_link ?? ''),
    'settings' => TRUE,
    'title' => $locale['admins_security_settings'],
    'description' => $locale['admins_security_settings'],
    'actions' => array('post' => array('savesettings' => array('form_id' => 'settingsFrm', 'callback' => 'sec_post'),
        'clearcache' => array('settingsform', 'callback' => 'clear_cache'))),
];

include __DIR__ . "/posts/security.php";
include __DIR__ . "/views/security.php";


/* Get all available captchas */
function get_captchas() {

    $available_captchas = [];
    if ($temp = opendir(INCLUDES . "captchas/")) {
        while (FALSE !== ($file = readdir($temp))) {
            if ($file != "." && $file != ".." && is_dir(INCLUDES . "captchas/" . $file)) {
                $available_captchas[$file] = !empty($locale[$file]) ? $locale[$file] : $file;
            }
        }
    }

    return $available_captchas;
}
