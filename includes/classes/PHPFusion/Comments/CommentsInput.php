<?php

namespace PHPFusion\Comments;

use Exception;

/**
 * Class CommentsInput
 * @package PHPFusion\Comments
 */
class CommentsInput {

    private static CommentsInput $comments;
    private static array $locale;
    private static \PHPFusion\Comments $parent;

    /**
     * CommentsViewBuilder constructor.
     * @param \PHPFusion\Comments $comments
     */
    public function __construct(\PHPFusion\Comments $comments) {
        self::$parent = $comments;
        self::$locale = fusion_get_locale();
    }

//Ratings Removal Update
    public function removeRatings() {
        if (iMEMBER && self::$parent->getParams("comment_allow_ratings") &&
            !self::$parent->getParams("comment_allow_vote")) {
            if (isset($_POST["remove_ratings_vote"])) {

                $my_id = fusion_get_userdata("user_id");

                $delete_ratings = "DELETE FROM " . DB_RATINGS . " WHERE rating_item_id=:item_id AND rating_type =:item_type AND rating_user = :uid";

                $result = dbquery($delete_ratings, [
                    ":item_id" => self::$parent->getParams("comment_item_id"),
                    ":item_type" => self::$parent->getParams("comment_item_type"),
                    ":uid" => $my_id,
                ]);

                if ($result) {
                    redirect(self::$parent::formatClink($this->getParams("clink")));
                }
            }
        }
    }

    /**
     * Old comment delete actions
     */
    public function delete() {
        /** Delete */
        if (isset($_GET['c_action']) && iMEMBER) {

            if ($_GET['c_action'] == 'delete') {

                $delete_query = "
                SELECT tcm.*, tcu.user_name
                FROM " . DB_COMMENTS . " tcm
                LEFT JOIN " . DB_USERS . " tcu ON tcm.comment_name=tcu.user_id
                WHERE comment_id=:comment_id AND comment_hidden=:comment_hidden
                ";

                $delete_param = [
                    ":comment_id" => intval(stripinput($_GET["comment_id"])),
                    ":comment_hidden" => 0,
                ];

                $eresult = dbquery($delete_query, $delete_param);
                if (dbrows($eresult)) {
                    $edata = dbarray($eresult);
                    $redirect_link = $this->getParams('clink') . ($this->settings['comments_sorting'] == "ASC" ? "" : "&c_start=0") . "#c" . $_GET['comment_id'];
                    $child_query = "SELECT comment_id FROM " . DB_COMMENTS . " WHERE comment_cat=:comment_cat_id";
                    $child_param = [':comment_cat_id' => intval($_GET['comment_id'])];
                    $result = dbquery($child_query, $child_param);
                    if (dbrows($result)) {
                        while ($child = dbarray($result)) {
                            dbquery("UPDATE " . DB_COMMENTS . " SET comment_cat='" . $edata['comment_cat'] . "' WHERE comment_id='" . $child['comment_id'] . "'");
                        }
                    }
                    dbquery("DELETE FROM " . DB_COMMENTS . " WHERE comment_id='" . $edata['comment_id'] . "'" . (iADMIN ? "" : "AND comment_name='" . $this->userdata['user_id'] . "'"));
                    $func = self::$parent->getParams('comment_delete_callback_function');
                    if (is_callable($func)) {
                        $func(self::$parent->getParams());
                    }

                    redirect($redirect_link);
                }
            }
        }

    }

    /**
     * Remove Comment Actions
     */
    public function remove() {

        $comment_data = [
            "comment_id" => post("comment_id", FILTER_VALIDATE_INT) ?? 0,
            "comment_item_id" => self::$parent->getParams("comment_item_id"),
            "comment_type" => self::$parent->getParams("comment_item_type"),
        ];

        $res = self::$parent->commentCheckQuery($comment_data["comment_id"]);

        if (dbrows($res)) {

            $rows = dbarray($res);

            if (self::$parent->isOwner($rows["comment_name"])) {

                self::$parent->shiftChildComment($comment_data["comment_id"]);

                self::$parent->deleteComment($comment_data["comment_id"]);

                return array(
                    "status" => 200,
                    "method" => "rm",
                    "parent_dom" => !empty($comment_data['comment_cat']) ? "c" . $comment_data['comment_cat'] . "_r" : self::$parent->getParams("comment_key") . "-commentsContainer",
                    "alt_parent_dom" => !empty($comment_data['comment_cat']) ? "c" . $comment_data['comment_cat'] . "_p" : self::$parent->getParams("comment_key") . "-commentsContainer",
                    "dom" => "c" . $comment_data["comment_id"],
                );
            }

            // Not owner
            return array(
                "status" => 300,
                "method" => "rm",
            );
        }

        // No result
        return array(
            "status" => 400,
            "method" => "rm",
        );
    }


    /**
     * Execute comment update
     * @throws Exception
     */
    public function update() {

        $settings = fusion_get_settings();
        $message = "";

//        $this->replaceParam("comment_user", $this->userdata['user_id']);

        /**
         * Post Comment, Reply Comment
         */
        if ((iMEMBER || $settings['guestposts'])) {

            if (!iMEMBER && $settings['guestposts']) {
                // Process Captchas
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES . "captchas/" . $settings['captcha'] . "/captcha_check.php";
                if (!$_CAPTCHA_IS_VALID) {
                    fusion_stop();
                    $message = self::$locale["u194"];
                }
            }

            // do not use get, otherwise remote api will have integration problem
            $comment_data = [
                "comment_id" => post("comment_id", FILTER_VALIDATE_INT) ?? 0,
                "comment_name" => iMEMBER ? self::$parent->userdata["user_id"] : form_sanitizer($_POST["comment_name"], "", "comment_name"),
                "comment_subject" => !post("comment_cat", FILTER_VALIDATE_INT) && self::$parent->getParams("comment_allow_subject") ? sanitizer("comment_subject", "", "comment_subject") : "",
                "comment_item_id" => self::$parent->getParams("comment_item_id"),
                "comment_type" => self::$parent->getParams("comment_item_type"),
                "comment_cat" => post("comment_cat", FILTER_VALIDATE_INT),
                "comment_message" => post("comment_message"),
                "comment_ip" => USER_IP,
                "comment_ip_type" => USER_IP_TYPE,
                "comment_hidden" => 0,
            ];

            $ratings_query = "SELECT rating_id FROM " . DB_RATINGS . " WHERE rating_item_id=:id AND rating_type=:type AND rating_user=:name";

            $ratings_id = dbresult(dbquery($ratings_query, [
                ":id" => $comment_data["comment_item_id"],
                ":type" => $comment_data["comment_type"],
                ":name" => $comment_data["comment_name"],
            ]), 0);


            // Ratings
            $ratings_data = [];
            if (self::$parent->getParams("comment_allow_ratings") && self::$parent->getParams("comment_allow_vote") && check_post("comment_rating")) {
                $ratings_data = [
                    "rating_id" => $ratings_id,
                    "rating_item_id" => self::$parent->getParams("comment_item_id"),
                    "rating_type" => self::$parent->getParams("comment_item_type"),
                    "rating_user" => $comment_data["comment_name"],
                    "rating_vote" => post("comment_rating"), //sanitizer("comment_rating", 0, "comment_rating"),
                    "rating_datestamp" => time(),
                    "rating_ip" => USER_IP,
                    "rating_ip_type" => USER_IP_TYPE,
                ];
            }

            if (fusion_safe()) {

                $user = fusion_get_userdata();

                if (iMEMBER && $comment_data["comment_id"]) {

                    // Update comment
                    if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id=:commentId
                        AND comment_item_id=:itemID AND comment_type=:itemType AND comment_name=:name AND comment_hidden='0'", [
                                ":commentId" => (int)$comment_data["comment_id"],
                                ":itemID" => self::$parent->getParams("comment_item_id"),
                                ":itemType" => self::$parent->getParams("comment_item_type"),
                                ":name" => self::$parent->userdata["user_id"],
                            ]))) {

                        $comment_data["comment_name"] = dbresult(dbquery("SELECT comment_name FROM " . DB_COMMENTS . " WHERE comment_id=:commentId", [
                            ":commentId" => (int)$comment_data["comment_id"],
                        ]), 0);

                        $comment_data["comment_edited"] = time();

                       dbquery_insert(DB_COMMENTS, $comment_data, "update");

                        self::$parent->comment_params[self::$parent->getParams("comment_key")]["post_id"] = $comment_data["comment_id"];
                        $func = self::$parent->getParams("comment_edit_callback_function");
                        if (is_callable($func)) {
                            $func(self::$parent->getParams());
                        }

                        if (iMEMBER && self::$parent->getParams("comment_allow_ratings") && self::$parent->getParams("comment_allow_vote")) {
                            dbquery_insert(DB_RATINGS, $ratings_data, ($ratings_data["rating_id"] ? "update" : "save"));
                        }

                        $message .= self::$locale["c114"];

                        if (iMEMBER) {
                            $user = fusion_get_user($comment_data["comment_name"]);
                            $comment_data += array(
                                "user_id" => $user["user_id"] ?? 0,
                                "user_name" => $user["user_name"] ?? "",
                                "user_firstname" => $user["user_firstname"] ?? "",
                                "user_lastname" => $user["user_lastname"] ?? "",
                                "user_displayname" => $user["user_displayname"] ?? "",
                                "user_avatar" => $user["user_avatar"] ?? "",
                                "user_status" => $user["user_status"] ?? "",
                            );
                        }

                        // For DOM rendering only
                        $comment_data["comment_datestamp"] = time();

                        //Adds $comment_data
                        $rows = self::$parent->parseCommentsData($comment_data, TRUE);

                        return array(
                            "status" => 200,
                            "method" => "update",
                            "message" => $message,
                            "current_dom" => "c" . $comment_data["comment_id"],
                            "parent_dom" => !empty($comment_data["comment_cat"]) ? "c" . $comment_data["comment_cat"] . "_r" : self::$parent->getParams("comment_key") . "-commentsContainer",
                            "alt_parent_dom" => !empty($comment_data["comment_cat"]) ? "c" . $comment_data["comment_cat"] . "_p" : self::$parent->getParams("comment_key") . "-commentsContainer",
                            "dom" => (new CommentsViewBuilder(self::$parent))->displaySingleComment($rows, self::$parent->getParams()),
                        );

                    }

                } else {

                    $comment_data["comment_datestamp"] = time();

                    if ($comment_data['comment_name'] && $comment_data['comment_message']) {

                        require_once INCLUDES . "flood_include.php";

                        if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='" . USER_IP . "'")) {

                            $comment_data["comment_id"] = dbquery_insert(DB_COMMENTS, $comment_data, "save");

                            if (iMEMBER && fusion_get_settings("ratings_enabled") && self::$parent->getParams("comment_allow_ratings") && self::$parent->getParams("comment_allow_vote")) {
                                dbquery_insert(DB_RATINGS, $ratings_data, ($ratings_data["rating_id"] ? "update" : "save"));
                            }

                            self::$parent->replaceParam("comment_id", $comment_data["comment_id"]);

                            $func = self::$parent->getParams("comment_post_callback_function");
                            if (is_callable($func)) {
                                $func(self::$parent->getParams());
                            }

                            $comment_data += array(
                                "user_id" => $user["user_id"] ?? 0,
                                "user_name" => $user["user_name"] ?? "",
                                "user_firstname" => $user["user_firstname"] ?? "",
                                "user_lastname" => $user["user_lastname"] ?? "",
                                "user_displayname" => $user["user_displayname"] ?? "",
                                "user_avatar" => $user["user_avatar"] ?? "",
                                "user_status" => $user["user_status"] ?? "",
                            );

                            //Adds $comment_data
                            $rows = self::$parent->parseCommentsData($comment_data, TRUE);

                            // return the post data.
                            // if there is a modal then press the delete to return
                            return array(
                                "status" => 200,
                                "message" => "",
                                "method" => "ins",
                                "parent_dom" => !empty($comment_data['comment_cat']) ? "c" . $comment_data['comment_cat'] . "_r" : self::$parent->getParams("comment_key") . "-commentsContainer",
                                "alt_parent_dom" => !empty($comment_data['comment_cat']) ? "c" . $comment_data['comment_cat'] . "_p" : self::$parent->getParams("comment_key") . "-commentsContainer",
                                "dom" => (new CommentsViewBuilder(self::$parent))->displaySingleComment($rows, self::$parent->getParams()),
                            );
                        }
                    }
                }
            } else {
                set_error(E_NOTICE, 'Comments Token Error', FUSION_SELF, 260);
            }
        }

        return array("status"=>300);
    }

}
