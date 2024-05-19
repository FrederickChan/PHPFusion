<?php
namespace PHPFusion\Comments;

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
    public function displayAllComments($c_data, $index, $options) {
        $comments_html = '';

        foreach ($c_data[$index] as $comments_id => $data) {

            $data['comment_ratings'] = '';
            if (fusion_get_settings('ratings_enabled') && self::$parent->getParams('comment_allow_ratings')) {
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

            $data_api = \Defender::encode($options);

            $data += [
                "comment_id" => $data["comment_id"],
                "comment_list_id" => "c" . $data["comment_id"],
                "comment_cat_id" => $data["comment_cat"],
                "comment_date" => $data["comment_datestamp"],
                "comment_ratings" => $data["comment_ratings"],
                "comment_subject" => $data["comment_subject"],
                "comment_message" => $data["comment_message"],
                "comment_reply_link" => ($data["reply_link"] ? "<a href='" . $data["reply_link"] . "' class='comments-reply display-inline' data-id='" . $comments_id . "'>" . self::$locale["c112"] . "</a>" : ""),
                "comment_edit_link" => ($data["edit_link"] ? "<a href='" . $data["edit_link"]["link"] . "' class='edit-comment display-inline' data-id='" . $data["comment_id"] . "' data-api='" . $data_api . "' data-key='" . self::$parent->getParams("comment_key") . "'>" . $data["edit_link"]["name"] . "</a>" : ""),
                "comment_delete_link" => ($data["delete_link"] ? "<a href='" . $data["delete_link"]["link"] . "' class='delete-comment display-inline' data-id='" . $data["comment_id"] . "' data-api='" . $data_api . "' data-type='" . $options["comment_item_type"] . "' data-item='" . $options["comment_item_id"] . "' data-key='" . self::$parent->getParams("comment_key") . "'>" . $data["delete_link"]["name"] . "</a>" : ""),
                "comment_reply_form" => ($data["reply_form"] ?? ""),
                //"comment_reply_count" => (isset($c_data[$data["comment_id"]]) ? count($c_data[$data["comment_id"]]) : 0),
                //"comment_nested" => (isset($c_data[$data["comment_id"]]) ? $this->displayAllComments($c_data, $data["comment_id"], $options) : ""),
            ];

            $comments_html .= display_comments_list($data);
        }

        return $comments_html;
    }

    /**
     * Comment Form
     * @return string
     */
    public function displayCommentForm() {

        $settings = fusion_get_settings();

        $locale = fusion_get_locale();

        if ($settings["comments_enabled"] == TRUE) {

            /**
             * Forms
             */
            if (self::$parent->getParams("comment_allow_post")) {

                $message_input = $locale["c105"];

                $clink = self::$parent->getParams("clink");

                $edata = [
                    "comment_cat" => 0,
                    "comment_subject" => "",
                    "comment_message" => "",
                ];

                if (iMEMBER && (isset($_GET["c_action"]) && $_GET["c_action"] == "edit") && (isset($_GET["comment_id"]) && isnum($_GET["comment_id"]))) {

                    $dbquery = self::$parent->commentEditQuery();

                    if (dbrows($dbquery)) {

                        $edata = dbarray($dbquery);

                        if ((iADMIN && checkrights("C")) || (iMEMBER && $edata["comment_name"] == fusion_get_userdata("user_id") && isset($edata["user_name"]))) {
                            $clink = self::$parent->getParams("clink") . " &c_action=edit&comment_id=" . $edata["comment_id"];
                        }
                    }
                }

                $can_post = iMEMBER || fusion_get_settings("guestposts");

                // Comments form
                //$form_action = fusion_get_settings("site_path").str_replace(" ../", "", self::format_clink($clink));
                $form_action = self::$parent->formatClink($clink);

                // why would we need to split this?

                if ($can_post) {

                    // Filter ID
                    if (check_get("id")) {
                        $cid = ltrim(get("id"), "c");
                    }

                    // need to get the id.
                    $comments_form_open = openform("inputform", "POST", $form_action, ["form_id" => self::$parent->getParams("comment_key") . "-inputform"]);
                    $comments_form_open .= form_hidden("comment_id", "", "", ["input_id" => self::$parent->getParams("comment_key") . "-commentid"]);
                    $comments_form_open .= form_hidden("comment_cat", "", $cid ?? "", ["input_id" => self::$parent->getParams("comment_key") . "-commentcat"]);

                    // Ratings dropdown
                    if (fusion_get_settings("ratings_enabled") && self::$parent->getParams("comment_allow_ratings") && self::$parent->getParams("comment_allow_vote")) {

                        $ratings_input = form_select("comment_rating", $locale["r106"], "",
                            [
                                "input_id" => self::$parent->getParams("comment_key") . "-commentRating",
                                "options" => [
                                    5 => $locale["r120"],
                                    4 => $locale["r121"],
                                    3 => $locale["r122"],
                                    2 => $locale["r123"],
                                    1 => $locale["r124"],
                                ],
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
                            "input_id" => self::$parent->getParams("comment_key") . "-commentName",
                        ]) : "");

                    $subject_input = self::$parent->getParams("comment_allow_subject") ? form_text("comment_subject", $locale["c113"], $edata["comment_subject"], ["required" => TRUE, "input_id" => self::$parent->getParams("comment_key") . "-commentSubject"]) : "";

                    // Add support custom template
                    $message_input = form_textarea($edata["comment_cat"] ? "comment_message_reply" : "comment_message", "", $edata["comment_message"],
                        [
                            "input_id" => self::$parent->getParams("comment_key") . " - comment_message",
                            "required" => TRUE,
                            "autosize" => TRUE,
                            "form_name" => "inputform",
                            "wordcount" => TRUE,
                            "placeholder" => "What\"s on your mind, " . display_name($userdata) . "?",
                        ]
                    );

                    $button = form_button("post_comment", $edata["comment_message"] ? $locale["c103"] : $locale["c102"], ($edata["comment_message"] ? $locale["c103"] : $locale["c102"]),
                        [
                            "class" => "btn-primary spacer-sm post_comment",
                            "input_id" => self::$parent->getParams("comment_key") . "-post_comment",
                        ]
                    );

                    $comments_form_close = closeform();
                }
            }

            return display_comment_form([
                "comment_form_open" => $comments_form_open ?? '',
                "comment_form_close" => $comments_form_close ?? '',
                "comment_form_id" => self::$parent->getParams("comment_key") ."_edit_comment",
                "comment_postable" => $can_post ?? '',
                "comment_name_input" => $name_input ?? '',
                "comment_subject_input" => $subject_input ?? '',
                "comment_message_input" => $message_input ?? '',
                "comment_ratings_input" => $ratings_input ?? '',
                "comment_captcha" => [
                    "captcha" => $captcha['form'] ?? '',
                    "input" => $captcha['input'] ?? '',
                ],
                "comment_button" => $button ?? '',
                "comment_form_avatar" => display_avatar(fusion_get_userdata(), self::$parent->getParams("comment_form_avatar_size"), FALSE, FALSE),
            ]);

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
        $ratings_html = openform('remove_ratings_frm', 'POST', $this->getParams('clink'), [
                'class' => 'text-right',
                'form_id' => $this->getParams('comment_key') . "-remove_ratings_frm",
            ]
        );
        $ratings_html .= form_hidden('comment_type', '', $this->getParams('comment_item_type'));
        $ratings_html .= form_hidden('comment_item_id', '', $this->getParams('comment_item_id'));
        $ratings_html .= form_button('remove_ratings_vote', $this->locale['r102'], 'remove_ratings_vote', ['input_id' => $this->getParams('comment_key') . "-remove_ratings_vote", 'class' => 'btn-default btn-rmRatings']);
        $ratings_html .= closeform();

        return $ratings_html;
    }


}
