<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_users.php
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
    'view' => 'um_view',
    'button' => 'um_button',
    'link' => ($admin_link ?? ''),
    'settings' => TRUE,
    'title' => $locale['admins_user_settings'],
    'description' => $locale['admins_user_description'],
    'actions' => array('post' => array('savesettings' => array('form_id' => 'settingsFrm', 'callback' => 'um_post'))),
];

include __DIR__ . "/posts/um.php";
include __DIR__ . "/views/um.php";
