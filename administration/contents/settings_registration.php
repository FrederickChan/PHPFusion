<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_registration.php
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
    'view' => 'pf_reg_view',
    'button' => 'pf_reg_button',
    'link' => ($admin_link ?? ''),
    'settings' => TRUE,
    'title' => $locale['admins_register_settings'],
    'description' => $locale['admins_register_description'],
    'actions' => array('post' => array('savesettings' => array('form_id' => 'settingsFrm', 'callback' => 'pf_reg_post'))),
);

include __DIR__ . "/posts/reg.php";
include __DIR__ . "/views/reg.php";









