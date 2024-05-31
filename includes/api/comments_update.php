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

    if (get("method") == "remove") {

        $response = array(
            "method" => "rm_dialog",
            "status" => 200,
            "html" => openmodal("commentDelete", "Delete Comment?", array(
                    "static" => TRUE,
                    "centered" => TRUE,
                    "header_class" => "justify-content-center",
                )) .
                'Are you sure you want to delete the comment?' .
                modalfooter(
                    form_button("commentCancelDel", "No", "comment_cancel_del", array(
                        "data" => array("bs-dismiss" => "modal"),
                        "type" => "button",
                    )) .
                    form_button("commentDel", "Delete", "comment_del", [
                        "data" => array(
                            "comment-id" => get("id"),
                            "bs-dismiss" => "modal"
                        ),
                        "type" => "button",
                        "class" => "btn-primary"]))
                . closemodal(),
        );

        echo json_encode($response);

    } else {

        $params = fusion_decode(fusion_decrypt(post("comment_params"), SECRET_KEY));

//        if (!empty($params["comment_cat_id"])) {
//            $params["comment_cat_id"] = str_replace("c", "", get("id"));
//        }

        if (!empty($params["comment_item_type"]) && !empty($params["comment_item_id"])) {

            $obj = Comments::getInstance($params, '');

            if (post("method") == "update") {

                $response = (new Comments\CommentsInput($obj))->update();

                echo json_encode($response);

            } elseif (post("method") == "remove") {

                $response = (new Comments\CommentsInput($obj))->remove();

                echo json_encode($response);

            } else {
                throw new Exception("Comments method are invalid", E_NOTICE);
            }
        }
    }


}

fusion_add_hook("fusion_filters", "update_comments");
