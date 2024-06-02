<?php

use PHPFusion\Comments;

defined("IN_FUSION") || exit;
const BOOTSTRAP = 5;

/**
 * Get comment HTML
 */
function get_comments() {

    $obj = "";

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

                    $unique_key_id = $params['comment_key'].get('id', FILTER_VALIDATE_INT);

                    $obj = Comments::getInstance($params)->showCommentForm($unique_key_id);

                    if (!empty($obj)) {

                        $obj = json_encode(array(
                            "status" => 200,
                            "method" => "edit",
                            "unique_key" => $unique_key_id,
                            "parent_dom" => get("comment_id", FILTER_VALIDATE_INT), //!empty($comment_data['comment_cat']) ? "c" . $comment_data['comment_cat'] . "_r" : $params["comment_key"] . "-commentsContainer",
                            "dom" => $obj,
                        ));

                    } else {
                        $obj = json_encode(array("status"=>300));
                    }
                }
            } else {

                $obj = json_encode(array(
                    "status" => 200,
                    "method" => "display",
                    "parent_dom" => "c".get("id")."_r",
                    "dom" => Comments::getInstance($params)->showCommentPosts()
                ));

            }

            echo $obj;

        }
    }
}

fusion_add_hook("fusion_filters", "get_comments");
