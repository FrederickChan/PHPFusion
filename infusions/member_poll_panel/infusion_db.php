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

defined( 'IN_FUSION' ) || exit;

// Locales
define( 'POLLS_LOCALE', fusion_get_inf_locale_path( 'polls.php', INFUSIONS . 'member_poll_panel/locale/' ) );

// Paths
const POLLS = INFUSIONS . 'member_poll_panel/';

// Database
const DB_POLL_VOTES = DB_PREFIX . "poll_votes";
const DB_POLLS = DB_PREFIX . "polls";


if ( infusion_exists( 'member_poll_panel' ) ) {

    // Admin Settings
    Admins::getInstance()->setAdminPageIcons( "PO", "<i class='admin-ico fa fa-fw fa-bar-chart'></i>" );

    function polls_cron_job24h_users_data( $data ) {
        dbquery( "DELETE FROM " . DB_POLL_VOTES . " WHERE vote_user=:user_id", [':user_id' => $data['user_id']] );
    }

    /**
     * @uses polls_cron_job24h_users_data()
     */
    fusion_add_hook( 'cron_job24h_users_data', 'polls_cron_job24h_users_data' );


    function member_poll_user_action_hook( $action, $user_id ) {

        if ( $action == 'delete_user' ) {

            dbquery( "DELETE FROM " . DB_POLL_VOTES . " WHERE vote_user=:uid", [':uid' => $user_id] );

        }

    }

    /**
     * @see member_poll_user_action_hook()
     */
    fusion_add_hook( 'fusion_user_action', 'member_poll_user_action_hook', 10, [], 2 );
}
