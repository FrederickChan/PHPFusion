<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: index.php
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

use PHPFusion\Database\DatabaseFactory;

require_once __DIR__ . "/../../maincore.php";

/**
 * Namespace Description
 *
 * fusion_register_hook_paths   - extend hook paths directory list
 * fusion_filters               - namespace to be executed
 */

/**
 * Get extended endpoint
 *
 * @return array
 */
function get_extended_endpoints() {
    if ($extended_endpoints = fusion_filter_hook("fusion_register_hook_paths")) {
        return flatten_array($extended_endpoints);
    }
    return [];
}

$endpoints = [
        "username-check" => "username_validation.php",
        "userpass-check" => "userpass_validation.php",
        "calling-codes" => "calling_codes.php", //get
        "geomap-states" => "states.php", //get
        "email_code" => "email_code.php", // post
        "avatar-upload" => "avatar_upload.php", // $_FILES
        "avatar-delete" => "avatar_delete.php", // post
        "user-search" => "search_users.php", // get or post
        "preview-text" => "text_preview.php", // post
        "notify-read" => "notify_read.php", // post
        "notify-readall" => "notify_readall.php", // post
        "error-update" => "errorlogs_update.php", // post
        "comment-get" => "comments_get.php", // post
        "comment-update" => "comments_update.php", // post
        "comment-emotes" => "comments_emotes.php" // post
    ]
    + get_extended_endpoints();

if ($api = get("api")) {

    if (isset($endpoints[$api])) {

        require $endpoints[$api];

        fusion_apply_hook("fusion_filters");

        // Close connection
        DatabaseFactory::getConnection()->close();

    } else {
        die("End point is faulty");
    }
} else {
    die("API is not specified");
}
die();
