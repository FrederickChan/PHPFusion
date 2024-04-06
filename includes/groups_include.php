<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: groups_include.php
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
 * Check if user is assigned to the specified user group(s).
 *
 * @param int $group The group number you want to check for the user.
 * @param string $delim Delimiter.
 *
 * @return bool True if the user is in the group.
 */
function checkgroup($group, $delim = ',') {

    if (strpos($group, $delim) !== FALSE) {
        foreach (explode($delim, $group) as $group_) {
            if (iSUPERADMIN) {
                return TRUE;
            } else if (iADMIN && ($group_ == 0 || $group_ == USER_LEVEL_MEMBER || $group_ == USER_LEVEL_ADMIN)) {
                return TRUE;
            } else if (iMEMBER && ($group_ == 0 || $group_ == USER_LEVEL_MEMBER)) {
                return TRUE;
            } else if (iGUEST && $group_ == 0) {
                return TRUE;
            } else if (iMEMBER && $group_ && in_array($group_, explode(".", iUSER_GROUPS))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    if (iSUPERADMIN) {
        return TRUE;
    } else if (iADMIN && ($group == 0 || $group == USER_LEVEL_MEMBER || $group == USER_LEVEL_ADMIN)) {
        return TRUE;
    } else if (iMEMBER && ($group == 0 || $group == USER_LEVEL_MEMBER)) {
        return TRUE;
    } else if (iGUEST && $group == 0) {
        return TRUE;
    } else if (iMEMBER && $group && in_array($group, explode('.', iUSER_GROUPS))) {
        return TRUE;
    }

    return FALSE;
}

/**
 * Check if user is assigned to the specified user group(s) and has the required user level.
 *
 * @param int $group The group number(s) you want to check for the user.
 * @param int $user_level User level.
 * @param string $user_groups Assigned groups to the user.
 * @param string $delim Delimiter.
 *
 * @return bool True if the user has access.
 */
function checkusergroup($group, $user_level, $user_groups, $delim = ',') {

    if (strpos($group, $delim) !== FALSE) {
        foreach (explode($delim, $group) as $group_) {
            if ($user_level == USER_LEVEL_SUPER_ADMIN) {
                return TRUE;
            } else if ($user_level == USER_LEVEL_ADMIN && ($group_ == 0 || $group_ == USER_LEVEL_MEMBER || $group_ == USER_LEVEL_ADMIN)) {
                return TRUE;
            } else if ($user_level == USER_LEVEL_MEMBER && ($group_ == 0 || $group_ == USER_LEVEL_MEMBER)) {
                return TRUE;
            } else if ($user_level == USER_LEVEL_PUBLIC && $group_ == 0) {
                return TRUE;
            } else if ($user_level == USER_LEVEL_MEMBER && $group_ && in_array($group_, explode('.', $user_groups))) {
                return TRUE;
            }
        }
    } else {
        if ($user_level == USER_LEVEL_SUPER_ADMIN) {
            return TRUE;
        } else if ($user_level == USER_LEVEL_ADMIN && ($group == 0 || $group == USER_LEVEL_MEMBER || $group == USER_LEVEL_ADMIN)) {
            return TRUE;
        } else if ($user_level == USER_LEVEL_MEMBER && ($group == 0 || $group == USER_LEVEL_MEMBER)) {
            return TRUE;
        } else if ($user_level == USER_LEVEL_PUBLIC && $group == 0) {
            return TRUE;
        } else if ($user_level == USER_LEVEL_MEMBER && $group && in_array($group, explode('.', $user_groups))) {
            return TRUE;
        }
    }

    return NULL;
}

/**
 * Cache of all user groups from the database.
 *
 * @return array Array of all user groups.
 */
function cache_groups() {
    static $groups_cache = NULL;
    if ($groups_cache === NULL) {
        $groups_cache = [];
        $result = dbquery("SELECT * FROM " . DB_USER_GROUPS . " ORDER BY group_id");
        while ($data = dbarray($result)) {
            $groups_cache[$data["group_id"]] = $data;
        }
    }

    return $groups_cache;
}

/**
 * Gets all access levels and user groups and make one array out of them for easy access and usage.
 *
 * @return array  Array of all access levels and user groups.
 */
function getusergroups() {

    $locale = fusion_get_locale();
    $groups_array = [[USER_LEVEL_PUBLIC, $locale['user0'], $locale['user0'], 'fa fa-user'], [USER_LEVEL_MEMBER, $locale['user1'], $locale['user1'], 'fa fa-user'], [USER_LEVEL_ADMIN, $locale['user2'], $locale['user2'], 'fa fa-user'], [USER_LEVEL_SUPER_ADMIN, $locale['user3'], $locale['user3'], 'fa fa-user']];
    $groups_cache = cache_groups();
    foreach ($groups_cache as $group) {
        $group_icon = !empty($group['group_icon']) ? $group['group_icon'] : '';
        $group_user_count = format_word($group['group_user_count'], $locale['fmt_user']);
        $groups_array[] = [$group['group_id'], $group['group_name'], $group['group_description'], $group_icon, $group_user_count];
    }

    return $groups_array;
}

/**
 * Get the name of the access level or user group.
 *
 * @param int $group_id The ID of the group or access level to which you want to get a name.
 * @param bool $return_desc If true, description will be returned instead of name.
 * @param bool $return_icon If true, icon will be returned next to name.
 *
 * @return string The name or icon or description of the given group, null if it does not exist.
 */
function getgroupname($group_id, $return_desc = FALSE, $return_icon = FALSE) {

    foreach (getusergroups() as $group) {
        if ($group_id == $group[0]) {
            return ($return_desc ? ($group[2] ?: '-') : (!empty($group[3]) && $return_icon ? "<i class='" . $group[3] . "'></i> " : "") . $group[1]);
        }
    }

    return NULL;
}

/**
 * Gets array of all access levels and user groups.
 *
 * @param array $remove Array of groups you want to exclude from output.
 *
 * @return array Array of all access levels and user groups.
 */
function fusion_get_groups($remove = []) {
    $visibility_opts = [];
    $groups = array_diff_key(getusergroups(), array_flip($remove));

    foreach ($groups as $group) {
        $visibility_opts[$group[0]] = $group[1];
    }

    return $visibility_opts;
}

/**
 * Check if user has access to the group.
 *
 * @param int $group_id The ID of the group.
 *
 * @return bool True if the user has access.
 */
function users_groupaccess($group_id) {

    if (preg_match("(^\.$group_id$|\.$group_id\.|\.$group_id$)", fusion_get_userdata('user_groups'))) {
        return TRUE;
    }

    return FALSE;
}

/**
 * Getting the access levels used when asking the database for data.
 *
 * @param string $field MySQL's field from which you want to check access.
 * @param string $delim Delimiter.
 *
 * @return string The part of WHERE clause, always returns a condition.
 */
function groupaccess($field, $delim = ',') {

    $res = '';
    if (iGUEST) {
        $res = $field . " in (" . USER_LEVEL_PUBLIC . ")";
    } else if (iSUPERADMIN) {
        $res = "1 = 1";
    } else if (iADMIN) {
        $res = $field . " in (" . USER_LEVEL_PUBLIC . ", " . USER_LEVEL_MEMBER . ", " . USER_LEVEL_ADMIN . ")";
    } else if (iMEMBER) {
        $res = $field . " in (" . USER_LEVEL_PUBLIC . ", " . USER_LEVEL_MEMBER . ")";
    }

    if (iUSER_GROUPS != "" && !iSUPERADMIN) {
        $groups = explode('.', iUSER_GROUPS);
        $groups_ = [];
        foreach ($groups as $group) {
            $groups_[] = in_group($field, $group, $delim);
        }
        $group_sql = implode(' OR ', $groups_);
        $res = "(" . $res . " OR " . $group_sql . ")";
    }

    return $res;
}

/**
 * Get the data of the access level or user group.
 *
 * @param int $group_id The ID of the group.
 *
 * @return array
 */
function getgroupdata($group_id) {

    foreach (getusergroups() as $group) {
        if ($group_id == $group[0]) {
            return $group;
        }
    }

    return NULL;
}
