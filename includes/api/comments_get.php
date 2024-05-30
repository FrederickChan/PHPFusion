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

        $params = fusion_decode(fusion_decrypt(get("comment_params"), SECRET_KEY));

        if (!empty($params) && check_get("id")) {
            $params["comment_cat_id"] = str_replace("c", "", get("id"));
        }

        if (!empty($params["comment_item_type"]) && !empty($params["comment_item_id"])) {

            if (get("type") == "input") {

                if (!empty($params["comment_cat_id"]) || get("method") == "edit") {

                    $obj = Comments::getInstance($params)->showCommentForm();

                    if (!empty($obj)) {

                        $obj = json_encode(array(
                            "status" => 200,
                            "method" => "edit",
                            "parent_dom" => get("comment_id", FILTER_VALIDATE_INT), //!empty($comment_data['comment_cat']) ? "c" . $comment_data['comment_cat'] . "_r" : $params["comment_key"] . "-commentsContainer",
                            "dom" => $obj,
                        ));

                    } else {

                        $obj = json_encode(array("status"=>300));
                    }
                }

            } else {
                $obj = Comments::getInstance($params)->showCommentPosts();

            }

            echo $obj;
        }
    }
}

fusion_add_hook("fusion_filters", "get_comments");
