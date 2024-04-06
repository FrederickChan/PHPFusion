<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: users_include.php
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

/**
 * Fetch user data of the currently logged-in user from database.
 *
 * @param string $key The key of one column.
 *
 * @return string|array Associative array of all data or one column by key.
 */
function fusion_get_userdata($key = NULL) {

    $userdata = fusion_set_user();

    if (empty($userdata)) {
        $userdata = ["user_level" => 0, "user_rights" => "", "user_groups" => "", "user_theme" => 'Default', "user_ip" => USER_IP];
    }
    $userdata = $userdata + ["user_id" => 0, "user_name" => fusion_get_locale("user_guest"), "user_status" => 1, "user_level" => 0, "user_rights" => "", "user_groups" => "", "user_theme" => fusion_get_settings("theme"),];

    return $key === NULL ? $userdata : ($userdata[$key] ?? NULL);
}

/**
 * Fetch user settings preference from database
 * @param null $key
 *
 * @return array|null
 */
function fusion_get_user_settings($user_id = NULL) {
    $result = dbquery("SELECT * FROM " . DB_USER_SETTINGS . " WHERE user_id=:uid", [
        ':uid' => $user_id,
    ]);
    if (dbrows($result)) {
        return dbarray($result);
    }
    return NULL;
}


/**
 * Get the data of any user by ID.
 *
 * @param int $user_id The user ID.
 * @param string $key The key of column.
 *
 * @return string|array Associative array of all data or one column by key.
 */
function fusion_get_user($user_id, $key = NULL) {

    static $user = [];
    if (!isset($user[$user_id]) && isnum($user_id)) {
        $user[$user_id] = dbarray(dbquery("SELECT * FROM " . DB_USERS . " WHERE user_id='" . intval($user_id) . "'"));
    }
    if (!isset($user[$user_id])) {
        return NULL;
    }

    return $key === NULL ? $user[$user_id] : ($user[$user_id][$key] ?? NULL);
}


/**
 * Fetch user PM settings.
 *
 * @param int $user_id User ID.
 * @param string $key user_inbox, user_outbox, user_archive, user_pm_email_notify, user_pm_save_sent
 *
 * @return array|string Associative array of all data or one column by key.
 */
function user_pm_settings($user_id, $key = NULL) {

    return PrivateMessages::getPmSettings($user_id, $key);
}


/**
 * Log user actions.
 *
 * @param int $user_id User ID.
 * @param string $column_name Affected column.
 * @param string $new_value New value.
 * @param string $old_value Old value.
 */
function save_user_log($user_id, $column_name, $new_value, $old_value) {

    $data = ["userlog_id" => 0, "userlog_user_id" => $user_id, "userlog_field" => $column_name, "userlog_value_new" => $new_value, "userlog_value_old" => $old_value, "userlog_timestamp" => time(),];
    dbquery_insert(DB_USER_LOG, $data, "save", ["keep_session" => TRUE]);
}

/**
 * Tag a user by simply just posting his name like @Nick and if found, returns a tooltip.
 *
 * @param string $user_name @Nick
 * @param string $tooltip Additional info e.g. ($userdata['user_lastvisit'] - 120 < time() ? 'Online' : 'Offline').
 *
 * @return string Tooltip with info.
 */
function fusion_parse_user($user_name, $tooltip = '') {
    return preg_replace_callback("/@([A-Za-z0-9\-_!.]+)/", function ($user_name) use ($tooltip) {
        $user = $user_name[1];
        $result = dbquery("SELECT *
            FROM " . DB_USERS . "
            WHERE (user_name=:user_00 OR user_name=:user_01 OR user_name=:user_02 OR user_name=:user_03) AND user_status='0'
            LIMIT 1
        ", [':user_00' => $user, ':user_01' => ucwords($user), ':user_02' => strtoupper($user), ':user_03' => strtolower($user)]);
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            return render_user_tags($data, $tooltip);
        }

        return $user_name[0];
    }, $user_name);
}

/**
 * Set password of the currently logged in an administrator.
 *
 * @param string $password Any password.
 *
 * @return bool True if a password is set.
 */
function set_admin_pass($password) {

    return Authenticate::setAdminCookie($password);
}

/**
 * Check if admin password matches userdata.
 *
 * @param string $password Password.
 *
 * @return bool True if the password matches the user's admin password or if the admin's cookie or session is set and is valid.
 */
function check_admin_pass($password) {

    return Authenticate::validateAuthAdmin($password);
}


/**
 * Get a user level's name by the numeric code of level.
 *
 * @param int $userlevel Level code.
 *
 * @return string The name of the given user level, null if it does not exist.
 */
function getuserlevel($userlevel) {

    $locale = fusion_get_locale();
    $userlevels = [USER_LEVEL_MEMBER => $locale['user1'], USER_LEVEL_ADMIN => $locale['user2'], USER_LEVEL_SUPER_ADMIN => $locale['user3']];

    return $userlevels[$userlevel] ?? NULL;
}

/**
 * Get a user status by the numeric code of status.
 *
 * @param int $userstatus Status code 0 - 8.
 * @param int $join_timestamp User lastvisit.
 *
 * @return string|null The name of the given user status, null if it does not exist.
 */
function getuserstatus($userstatus, $join_timestamp = 0) {

    $locale = fusion_get_locale();

    if ($join_timestamp) {
        return ($userstatus >= 0 and $userstatus <= 8) ? $locale['status' . $userstatus] : NULL;
    }

    return $locale['status_pending'];
}

/**
 * Check if an Administrator has the correct rights assigned.
 *
 * @param string $rights Rights you want to check for the administrator.
 *
 * @return bool True if the user is an Administrator with rights defined in $rights.
 */
function checkrights($rights) {
    if (iSUPERADMIN) {
        return TRUE;
    } else if (iADMIN && in_array($rights, explode(".", iUSER_RIGHTS))) {
        return TRUE;
    }
    return FALSE;

}


/**
 * UF blacklist for SQL - same as groupaccess() but $field is the user_id column.
 *
 * @param string $field The name of the field
 *
 * @return string SQL condition. It can return an empty condition if the user_blacklist field is not installed!
 */
function blacklist($field) {

    if (column_exists('users', 'user_blacklist')) {
        $user_id = fusion_get_userdata('user_id');
        if (!empty($user_id)) {
            $result = dbquery("SELECT user_id, user_level FROM " . DB_USERS . " WHERE " . in_group('user_blacklist', $user_id));
            if (dbrows($result) > 0) {
                $i = 0;
                $sql = '';

                while ($data = dbarray($result)) {
                    $sql .= ($i > 0) ? "AND $field !='" . $data['user_id'] . "'" : "($field !='" . $data['user_id'] . "'";
                    $i++;
                }
                $sql .= $sql ? ")" : '1=1';

                return $sql;
            }
        }
    }

    return '';
}

/**
 * Check if user was blacklisted by a member.
 *
 * @param int $user_id User ID.
 * @param bool $me Set true to hide blocked user's content on your account.
 *
 * @return bool True if the user is blacklisted.
 */
function user_blacklisted($user_id, $me = FALSE) {

    if (column_exists('users', 'user_blacklist')) {
        $my_id = fusion_get_userdata('user_id');
        if ($me && !empty(fusion_get_userdata('user_blacklist'))) {
            $blacklist = explode(',', fusion_get_userdata('user_blacklist'));
            if (!empty($blacklist)) {
                foreach ($blacklist as $id) {
                    if ($id == $user_id) {
                        return TRUE;
                    }
                }
            }
        } else {
            $result = dbquery("SELECT user_id, user_level FROM " . DB_USERS . " WHERE " . in_group('user_blacklist', $my_id));
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    if ($user_id == $data['user_id']) {
                        return TRUE;
                    }
                }
            }
        }
    }

    return FALSE;
}
