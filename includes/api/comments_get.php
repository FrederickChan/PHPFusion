<?php

use PHPFusion\Comments;

defined("IN_FUSION") || exit;
const BOOTSTRAP = 5;

/**
 * Get comment HTML
 */
function get_comments() {

    if (fusion_safe()) {

        require_once INCLUDES . "plugins_include.php";
        require_once INCLUDES . "theme_functions_include.php";

        $params = fusion_decode(fusion_decrypt(get("params"), SECRET_KEY));

        if (!empty($params) && check_get("id")) {
            $params["comment_cat_id"] = str_replace("c", "", get("id"));
        }

        if (!empty($params["comment_item_type"]) && !empty($params["comment_item_id"])) {

            if (get("type") == "input") {
                if (!empty($params["comment_cat_id"])) {
                    $obj = Comments::getInstance($params)->showCommentForm();
                }
            } else {

                $obj = Comments::getInstance($params)->showCommentPosts();

            }

            echo $obj;
        }
    }
}

fusion_add_hook("fusion_filters", "get_comments");
