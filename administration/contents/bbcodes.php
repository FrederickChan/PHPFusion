<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bbcodes.php
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

$locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/settings.php', LOCALE.LOCALESET.'admin/bbcodes.php']);
$aidlink = fusion_get_aidlink();

$contents = [
    'view'     => 'pf_bb_view',
    'settings' => TRUE,
    'link'     => ($admin_link ?? ''),
    'title'    => $locale['admins_bbcode_settings'],
    'description' => $locale['admins_bbcode_description'],
];

require_once __DIR__."/views/bbcodes.php";
