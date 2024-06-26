<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_messages.php
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

$contents = array(
    'view' => 'pm_view',
    'button' => 'pm_button',
    'js' => 'pm_js',
    'settings' => TRUE,
    'link' => ($admin_link ?? ''),
    'title' => $locale['admins_message_settings'],
    'description' => $locale['admins_message_description'],
    'actions' => array(
        'post' => array(
            'savesettings' => array('form_id' => 'settingsFrm', 'callback' => 'pm_post'),
            'deletepm' => array('form_id' => 'delFrm', 'callback' => 'pm_remove'),
        ),
    ),
);

include __DIR__ . "/posts/pm.php";
include __DIR__ . "/views/pm.php";
