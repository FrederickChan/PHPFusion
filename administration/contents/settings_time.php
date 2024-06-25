<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_time.php
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

$contents = [
    'post' => 'time_post',
    'view' => 'time_view',
    'button' => 'time_button',
    'js' => 'pf_js',
    'link' => ($admin_link ?? ''),
    'settings' => TRUE,
    'title' => $locale['admins_time_settings'],
    'description' => $locale['admins_time_description'],
    'actions' => array('post' => array(
        'savesettings' => array('id' => 'settingsFrm', 'callback' => 'time_post')
    )),
];

include __DIR__ . "/posts/time.php";
include __DIR__ . "/views/time.php";
