<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion_db.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Admins;

defined('IN_FUSION') || exit;

// Locales
define('SHOUTBOX_LOCALE', fusion_get_inf_locale_path('shoutbox.php', INFUSIONS.'shoutbox_panel/locale/'));

// Paths
const SHOUTBOX = INFUSIONS.'shoutbox_panel/';

// Database
const DB_SHOUTBOX = DB_PREFIX."shoutbox";

// Admin Settings
if (infusion_exists('shoutbox_panel')) {

    Admins::getInstance()->setAdminPageIcons('S', "<i class='admin-ico fa fa-fw fa-commenting'></i>");

    function shoutbox_user_action_hook($action, $user_id) {

        if ($action == 'delete_user') {
            dbquery("DELETE FROM ".DB_SHOUTBOX." WHERE shout_name=:uid", [':uid'=>$user_id]);
        }
    }

    /**
     * @see shoutbox_user_action_hook()
     */
    fusion_add_hook( 'fusion_user_action', 'shoutbox_user_action_hook', 10, [], 2 );

}
