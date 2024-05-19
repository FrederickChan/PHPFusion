<?php

use PHPFusion\Comments;

defined("IN_FUSION") || exit;
const BOOTSTRAP = 5;

/**
 * @throws Exception
 */
function update_comments() {
    require_once INCLUDES . "plugins_include.php";
    require_once INCLUDES . "theme_functions_include.php";

    $params = fusion_decode(fusion_decrypt(post("params"), SECRET_KEY));
    if (!empty($params)) {
        $params["comment_cat_id"] = str_replace("c", "", get("id"));
    }

    if (!empty($params["comment_item_type"]) && !empty($params["comment_item_id"])) {
        // we need all these
        if (get("method") == "update") {
            echo Comments::getInstance($params)->();
        } elseif (get("method") == "remove") {
            echo Comments::getInstance($params)->();
        } else {
            throw new Exception("Comments method are invalid", E_NOTICE);
        }
    }
    print_p($params);

}

fusion_add_hook("fusion_filters", "update_comments");
