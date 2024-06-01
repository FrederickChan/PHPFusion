<?php

use PHPFusion\Comments;

defined("IN_FUSION") || exit;
const BOOTSTRAP = 5;

function update_comment_emotes() {

    if (iMEMBER) {
        $user_id = fusion_get_userdata("user_id");

        $_emotes = ["😀", "😭", "😰", "🤭", "😵", "😮‍💨", "🤡", "💩", "😡"];

        if ($comment_id = post("id", FILTER_VALIDATE_INT)) {

            if ($emote = post("emotes")) {

                if (in_array($emote, $_emotes) && fusion_safe()) {

                    // check exist, if yes remove, if no add.
                    $result = dbquery("SELECT comment_id, comment_emotes 
                        FROM " . DB_COMMENTS . " WHERE comment_id=:commentID", array(
                        ":commentID" => $comment_id,
                    ));

                    if (dbrows($result)) {

                        $rows = dbarray($result);

                        $_emoji_lists = fusion_decode($rows["comment_emotes"]);

                        // has emoji by this user
                        if (isset($_emoji_lists[$user_id])) {

                            if ($_emoji_lists[$user_id] != $emote) {
                                $_emoji_lists[$user_id] = $emote;
                            } else {
                                unset($_emoji_lists[$user_id]);
                            }
                        } else {
                            $_emoji_lists[$user_id] = $emote;
                        }

                        $rows["comment_emoji"] = fusion_encode($_emoji_lists);

                        dbquery("UPDATE " . DB_COMMENTS . " SET comment_emotes=:emotes WHERE comment_id=:commentID", array(
                            ":commentID" => $rows["comment_id"],
                            ":emotes" => $rows["comment_emoji"],
                        ));

                        $emote_arr = array_filter(array_unique($_emoji_lists));
                        $emote_count = count($emote_arr);

                        // @todo: tpl
                        echo json_encode(array(
                            "status" => 200,
                            "dom" => $emote_count ? '<li><span class="m-0 me-3"><small>' . $emote_count . '</small></span></li><li>' . implode('</li><li>', $emote_arr) . '</li>' : '',
                            "target" => "c_em" . $rows["comment_id"],
                        ));

                    } else {

                        echo json_encode(array(
                            "status" => 300,
                        ));
                    }

                } else {
                    echo json_encode(array(
                        "status" => 301,
                    ));
                }
            } else {
                echo json_encode(array(
                    "status" => 500,
                ));
            }
        } else {
            echo json_encode(array(
                "status" => 501,
            ));
        }
    } else {
        echo json_encode(array(
            "status" => 400,
        ));
    }
}

/**
 * @see update_comment_emotes()
 */
fusion_add_hook("fusion_filters", "update_comment_emotes");
