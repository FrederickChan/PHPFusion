<?php

namespace PHPFusion\Comments;

use Defender;

/**
 * Class CommentsViewBuilder
 * @package PHPFusion\Comments
 */
class CommentsViewBuilder {

    private static CommentsViewBuilder $comments;
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

    /**
     * Comments Listing
     *
     * @param array $c_data
     * @param int $index
     * @param string|array $options
     *
     * @return string
     */
    public function displayAllComments($c_data, $index, $param) {
        $comments_html = '';
        if (!empty($c_data)) {
            foreach ($c_data[$index] as $data) {
                $comments_html .= $this->displaySingleComment($data, $param);

//            $data['comment_ratings'] = '';
//            if (fusion_get_settings('ratings_enabled') && self::$parent->getParams('comment_allow_ratings')) {
//                $remainder = 5 - (int)$data['ratings'];
//                for ($i = 1; $i <= $data['ratings']; $i++) {
//                    $data['comment_ratings'] .= '<i class="fas fa-star text-warning"></i> ';
//                }
//                if ($remainder) {
//                    for ($i = 1; $i <= $remainder; $i++) {
//                        $data['comment_ratings'] .= '<i class="far fa-star text-lighter"></i> ';
//                    }
//                }
//            }
            }
        }

        return $comments_html;
    }

    public function displaySingleComment($data, $options) {

        $data['comment_ratings'] = '';
        if (fusion_get_settings("ratings_enabled") && self::$parent->getParams("comment_allow_ratings")) {
            $remainder = 5 - (int)$data['ratings'];
            for ($i = 1; $i <= $data['ratings']; $i++) {
                $data['comment_ratings'] .= '<i class="fas fa-star text-warning"></i> ';
            }
            if ($remainder) {
                for ($i = 1; $i <= $remainder; $i++) {
                    $data['comment_ratings'] .= '<i class="far fa-star text-lighter"></i> ';
                }
            }
        }

        $data += array(
            "comment_id" => $data["comment_id"],
            "comment_list_id" => "c" . $data["comment_id"],
            "comment_cat_id" => $data["comment_cat"],
            "comment_date" => $data["comment_datestamp"],
            "comment_ratings" => $data["comment_ratings"],
            "comment_subject" => $data["comment_subject"],
            "comment_message" => $data["comment_message"],
            "comment_reply_link" => ($data["reply_link"] ? "<a href='" . $data["reply_link"] . "' class='comments-reply display-inline' data-id='" . $data["comment_id"] . "'>" . self::$locale["c112"] . "</a>" : ""),
            "comment_reply_form" => ($data["reply_form"] ?? ""),
        );


        return display_comments_list($data);
    }

    /**
     * Comment Form
     * @return string
     */
    public function displayCommentForm($unique_key) {

        $settings = fusion_get_settings();

        $locale = fusion_get_locale();

        if ($settings["comments_enabled"] == TRUE) {

            /**
             * Forms
             */
            if (self::$parent->getParams("comment_allow_post")) {

                $message_input = $locale["c105"];

//                $clink = self::$parent->getParams("clink");

                $edata = [
                    "comment_cat" => 0,
                    "comment_subject" => "",
                    "comment_message" => "",
                ];

                if (iMEMBER && (get("method") == "edit") && (check_get("comment_id"))) {
                    $dbquery = self::$parent->commentEditQuery();
                    if (dbrows($dbquery)) {
                        $edata = dbarray($dbquery);
//                        if ((iADMIN && checkrights("C")) || (iMEMBER && $edata["comment_name"] == fusion_get_userdata("user_id") && isset($edata["user_name"]))) {
//                            $clink = self::$parent->getParams("clink") . " &c_action=edit&comment_id=" . $edata["comment_id"];
//                        }
                    } else {
                        return "";
                    }
                }

                if (iMEMBER || fusion_get_settings("guestposts")) {

                    // When initiated new reply
                    if (check_get("id")) {
                        $edata["comment_cat"] = ltrim(get("id"), "c");
                    }

                    // need to get the id.
                    $comments_form_open = openform("inputform", "POST", FORM_REQUEST, array("form_id" => $unique_key . "-inputform"));

                    $comments_form_open .= form_hidden("comment_params", "", self::$parent->comment_param_data, ["input_id" => $unique_key . "_params"]);

                    $comments_form_open .= form_hidden("comment_id", "", $edata["comment_id"] ?? 0, ["input_id" => $unique_key . "-commentid"]);

                    $comments_form_open .= form_hidden("comment_cat", "", $edata["comment_cat"] ?? "0", ["input_id" => $unique_key . "-commentcat"]);

                    // Ratings dropdown
                    if (fusion_get_settings("ratings_enabled") && self::$parent->getParams("comment_allow_ratings") && self::$parent->getParams("comment_allow_vote")) {

                        $ratings_input = form_select("comment_rating", $locale["r106"], "",
                            [
                                "input_id" => $unique_key . "-commentRating",
                                "options" => array(
                                    5 => $locale["r120"],
                                    4 => $locale["r121"],
                                    3 => $locale["r122"],
                                    2 => $locale["r123"],
                                    1 => $locale["r124"],
                                ),
                            ]
                        );
                    }

                    // Captcha for Guest... turn this into a modal pop up
                    $captcha = $this->displayCaptchaInput();

                    $userdata = fusion_get_userdata();

                    $name_input = (iGUEST ?
                        form_text("comment_name", $locale["c104"], "", [
                            "max_length" => 30,
                            "required" => TRUE,
                            "input_id" => $unique_key . "-commentName",
                        ]) : "");

                    $subject_input = self::$parent->getParams("comment_allow_subject") ? form_text("comment_subject", $locale["c113"], $edata["comment_subject"], array(
                        "required" => TRUE, "input_id" => $unique_key . "-commentSubject")) : "";

                    // Add support custom template
                    $message_input = form_textarea("comment_message", "", $edata["comment_message"],
                        [
                            "input_id" => $unique_key . "-commentMessage",
                            "required" => TRUE,
                            "autosize" => TRUE,
                            "form_name" => "inputform",
                            "wordcount" => TRUE,
                            "placeholder" => "What\"s on your mind, " . display_name($userdata) . "?",
                        ]
                    );

                    // is for editing
                    $button = (!empty($edata["comment_id"]) || isset($edata["comment_id"]) ?
                        form_button("cancel_comment", "Cancel", "cancel",
                            array(
                                "input_id" => $unique_key . ($edata["comment_id"] ?? $edata["comment_cat"]) . "-cancelComment",
                                "data" => array(
                                    "comment-id" => ($edata["comment_cat"] ?? $edata["comment_id"]),
                                ),
                            )) :
                        "") .
                        form_button("post_comment", $edata["comment_message"] ? $locale["c103"] : $locale["c102"], ($edata["comment_message"] ? $locale["c103"] : $locale["c102"]),
                            array(
                                "class" => "btn-primary spacer-sm",
                                "input_id" => $unique_key . "-post_comment",
                            )
                        );

                    $comments_form_close = closeform();

                    return display_comment_form(array(
                        "comment_form_id" => $unique_key,
                        "comment_form_open" => $comments_form_open ?? '',
                        "comment_form_close" => $comments_form_close ?? '',
                        "comment_postable" => $can_post ?? '',
                        "comment_name_input" => $name_input ?? '',
                        "comment_subject_input" => $subject_input ?? '',
                        "comment_message_input" => $message_input ?? '',
                        "comment_ratings_input" => $ratings_input ?? '',
                        "comment_captcha" => array(
                            "captcha" => $captcha['form'] ?? '',
                            "input" => $captcha['input'] ?? '',
                        ),
                        "comment_button" => $button ?? '',
                        "comment_form_avatar" => display_avatar(fusion_get_userdata(), self::$parent->getParams("comment_form_avatar_size"), FALSE, FALSE),
                    ));
                }
            }
        }

        return "";
    }


    /**
     * @param false $c_reply
     * @return array
     */
    public function displayCaptchaInput($c_reply = FALSE) {

        $captcha = [];
        if (iGUEST && fusion_get_settings('guestposts') == TRUE && (!isset($_CAPTCHA_HIDE_INPUT) || (!$_CAPTCHA_HIDE_INPUT))) {
            $_CAPTCHA_HIDE_INPUT = FALSE;

            include INCLUDES . 'captchas/' . fusion_get_settings('captcha') . '/captcha_display.php';

            $captcha['html'] = display_captcha([
                'captcha_id' => ($c_reply ? 'reply_captcha_' : 'captcha_') . self::$parent->getParams('comment_key'),
                'input_id' => ($c_reply ? 'reply_captcha_code_' : 'captcha_code_') . self::$parent->getParams('comment_key'),
                'image_id' => ($c_reply ? 'reply_captcha_image_' : 'captcha_image_') . self::$parent->getParams('comment_key'),
            ]);
            if (!$_CAPTCHA_HIDE_INPUT) {
                $captcha['input'] = form_text('captcha_code', self::$locale['global_151'], '', ['required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => 'captcha_code_' . self::$parent->getParams("comment_key")]);
            }
        }

        return $captcha;
    }


    /**
     * @return string
     */
    public function displayRatingsForm() {

        return openform('remove_ratings_frm', 'POST', self::$parent->getParams('clink'), [
                    'class' => 'text-end',
                    'form_id' => self::$parent->getParams('comment_key') . "-remove_ratings_frm",
                ]
            ) .
            form_hidden('comment_type', '', self::$parent->getParams('comment_item_type')) .
            form_hidden('comment_item_id', '', self::$parent->getParams('comment_item_id')) .
            form_button('remove_ratings_vote', self::$locale['r102'], 'remove_ratings_vote', ['input_id' => self::$parent->getParams('comment_key') . "-removeRatings", 'class' => 'btn-default btn-rmRatings']) .
            closeform();

    }

}
