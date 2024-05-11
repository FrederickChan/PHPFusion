<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Comments.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

/**
 * Class Comments
 *
 * @package PHPFusion
 *          Rating is not working
 *          Edit is not working
 */
class Comments {

    protected static $instances = NULL;
    protected static $key = 'Default';

    /**
     * @var array
     * comment_item_type -
     * comment_db -
     * comment_item_id -
     * clink -
     * comment_allow_reply - enable or disable reply of others comments
     * comment_allow_post - enable or disable posting of comments
     * comment_allow_ratings - enable or disable ratings
     * comment_allow_vote - enable or disable voting
     * comment_once - each user can only comment once (replying a comment is unaffected)
     * comment_echo - to echo the output if true
     * comment_title - display the comment block title
     * comment_count - display the current comment count
     */
    protected static $params = [
        'comment_user' => '',
        'comment_item_type' => '',
        'comment_db' => '',
        'comment_col' => '',
        'comment_item_id' => '',
        'clink' => '',
        'comment_allow_subject' => TRUE,
        'comment_allow_reply' => TRUE,
        'comment_allow_post' => TRUE,
        'comment_allow_ratings' => FALSE,
        'comment_allow_vote' => TRUE,
        'comment_once' => FALSE,
        'comment_echo' => FALSE,
        'comment_title' => '',
        'comment_form_title' => '',
        'comment_count' => TRUE,
        'comment_bbcode' => TRUE,
        'comment_tinymce' => FALSE,
        'comment_tinymce_skin' => 'lightgray',
        'comment_custom_script' => FALSE,
        'comment_post_callback_function' => '', // trigger custom functions during post comment event
        'comment_edit_callback_function' => '',  // trigger custom functions during reply event
        'comment_delete_callback_function' => '', // trigger custom functions during delete event
        "comment_avatar_size" => "32px",
        "comment_form_avatar_size" => "40px",
    ];

    protected $locale;
    protected $userdata;
    protected $settings;

    private $post_link;

    protected $c_arr = [
        'c_con' => [],
        'c_info' => [
            'c_makepagenav' => FALSE,
            'admin_link' => FALSE,
        ],
    ];
    protected $comment_params = [];
    private $comment_data = [];
    protected $cpp;

    /**
     * Removes comment reply
     *
     * @param string $clink
     *
     * @return string
     */
    private static $clink = [];

    protected static $c_start = 0;

    private function __construct() {
        // Set Settings
        $this->settings = fusion_get_settings();
        // Set Global Locale
        $this->locale = fusion_get_locale('',
            [
                LOCALE . LOCALESET . "comments.php",
                LOCALE . LOCALESET . "user_fields.php",
                LOCALE . LOCALESET . "ratings.php",
            ]
        );

        // Set current userdata
        $this->userdata = fusion_get_userdata();
        // Post link?
        $this->post_link = FUSION_SELF . (FUSION_QUERY ? "?" . FUSION_QUERY : "");
        $this->post_link = preg_replace("^(&|\?)c_action=(edit|delete)&comment_id=\d*^", "", $this->post_link);
        // Comments Per Page
        $this->cpp = fusion_get_settings('comments_per_page');
    }

    /**
     * Get an instance by key
     *
     * @param array $params
     * @param string $key
     *
     * @return static
     */
    public static function getInstance(array $params = [], $key = 'Default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
            self::$key = $key;
            $params['comment_key'] = $key;
            self::$params = $params + self::$params;
            self::setInstance($key);
        }

        return self::$instances[$key];
    }

    /**
     * @param string $key
     */
    private static function setInstance($key) {
        $obj = self::getInstance([], $key);
        $obj->setParams(self::$params);
        $obj->setEmptyCommentData();
        $obj->checkPermissions();
        $obj->executeCommentUpdate();
        $obj->getComments();
        if (!defined('COMMENT_JS')) {
            define('COMMENT_JS', TRUE);
            $obj->setJs();
        }
    }

    public function setJs() {
        $params = fusion_encrypt(fusion_encode(self::getParams()), SECRET_KEY);

        add_to_jquery("
        $(document).on('focus', '.plchr > input', function(e) {
            var pom = $(this).closest('.plchr'),
            tom = pom.closest('.plchw').find('.plchx'),
            tomText = tom.find('textarea');
            // let us do emoji buttons
            pom.hide();
            tom.show();
            tomText.focus();
        });
        
        $(document).on('click', 'a[data-comment-r=\"view\"]', function(e) {
            e.preventDefault();
            $(this).closest('ul').hide();
            var nextContainer = $(this).closest('ul').next('ul');
            nextContainer.show();
            var params = { id: $(this).data('comment-id'), params: '$params' };
            $.get('" . INCLUDES . "api/?api=comment-get', params, function(e) {
                //console.log(e);
                nextContainer.html(e);
            });
        });
        
        $(document).on('click', 'a[data-comment-r=\"reply\"]', function(e) {
            e.preventDefault();   
            var tgt = $(this).data('comment-target'),
            formContainer = $(tgt);
            var params = { id: $(this).data('comment-id'), params: '$params', 'type':'input' };            
             $.get('" . INCLUDES . "api/?api=comment-get', params, function(e) {
                //console.log(e);
                formContainer.html(e);
            });            
            $(this).closest('li.comment-item').addClass('r-open');
            formContainer.addClass('open');
        });
        
        ");
    }


    /**
     * Displays Comments
     */
    public function showComments() {

        $html = '';
        if ($this->settings['comments_enabled'] == TRUE) {

            /**
             * Comments
             */
            $ratings_html = "";
            $c_info = $this->c_arr["c_info"];
            if (fusion_get_settings('ratings_enabled') && $this->getParams('comment_allow_ratings')) {
                if (!empty($c_info['ratings_count'])) {
                    $stars = '';
                    $ratings = [];

                    $remainder = 5 - (int)$c_info['ratings_count']['avg'];
                    for ($i = 1; $i <= $c_info['ratings_count']['avg']; $i++) {
                        $stars .= '<i class="fas fa-star text-warning"></i> ';
                    }
                    if ($remainder) {
                        for ($i = 1; $i <= $remainder; $i++) {
                            $stars .= '<i class="far fa-star text-lighter"></i> ';
                        }
                    }

                    for ($i = 5; $i >= 1; $i--) {
                        $bal = 5 - $i;
                        $stars_ = '';
                        for ($x = 1; $x <= $i; $x++) {
                            $stars_ .= '<i class="fas fa-star text-warning"></i> ';
                        }

                        for ($b = 1; $b <= $bal; $b++) {
                            $stars_ .= '<i class="far fa-star text-lighter"></i> ';
                        }

                        $progress_num = $c_info['ratings_count'][$i] == 0 ? 0 : round((($c_info['ratings_count'][$i] / $c_info['ratings_count']['total']) * 100), 1);
                        $progressbar = progress_bar($progress_num, '', ['height' => '10px', 'hide_info' => TRUE, 'progress_class' => 'm-0']);
                        $ratings[] = [
                            'stars' => $stars_,
                            'stars_count' => $c_info['ratings_count'][$i] ?: 0,
                            'progressbar' => $progressbar,
                        ];
                    }

                    $ratings_html = display_comments_ratings([
                        'stars' => $stars,
                        'reviews' => format_word($c_info['ratings_count']['total'], $this->locale['fmt_review']),
                        'ratings' => $ratings,
                        'ratings_remove_button' => $c_info['ratings_remove_form'] ?: '',
                    ]);
                }
            }

            // @bug: Split the array into page chunks. [0] for page 1, [1] for page 2
            // Display comments
            $html .= display_comments_ui([
                "comment_title" => $this->getParams("comment_title"),
                "comment_count" => ($this->getParams("comment_count") ? $this->c_arr["c_info"]["comments_count"] : ""),
                "comment_container_id" => $this->getParams("comment_key"),
                "comment_form_container_id" => $this->getParams("comment_key") . "-comments_form",
                "comment_ratings" => $ratings_html,
                "comment_form_title" => ($this->getParams("comment_form_title") ?: $this->locale["c111"]),
                "comment_type" => $this->getParams("comment_item_type"),
                "clink" => $this->getParams("clink"),
                "comment_item_id" => $this->getParams("comment_item_id"),
                "options" => $this->getParams(),
                // Comments
                "comment_pagenav" => ($this->c_arr["c_info"]["c_makepagenav"] ? "<div class='text-left'>" . $this->c_arr["c_info"]["c_makepagenav"] . "</div>\n" : ""),
                "comment_posts" => $this->showCommentPosts(),
                "comment_admin_link" => $this->c_arr["c_info"]["admin_link"],
                "comment_form" => $this->showCommentForm(),
            ]);
        }

        if ($this->getParams('comment_echo')) {
            echo $html;
        }

        return $html;
    }

    /**
     * @return string
     */
    public function showCommentPosts() {
       return (!empty($this->c_arr["c_con"]) ? $this->displayAllComments($this->c_arr["c_con"], $this->getParams("comment_cat_id") ?? "0", $this->getParams()) : display_no_comments($this->locale["c101"]));
     }

    /**
     * @return string
     */
    public function showCommentForm() {

        if ($this->settings['comments_enabled'] == TRUE) {
            /**
             * Forms
             */
            if ($this->getParams('comment_allow_post')) {

                $message_input = $this->locale['c105'];
                $clink = $this->getParams('clink');

                $edata = [
                    "comment_cat" => 0,
                    "comment_subject" => "",
                    "comment_message" => "",
                ];
                if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
                    $dbquery = $this->commentEditQuery();
                    if (dbrows($dbquery)) {
                        $edata = dbarray($dbquery);
                        if ((iADMIN && checkrights("C")) || (iMEMBER && $edata['comment_name'] == fusion_get_userdata('user_id') && isset($edata['user_name']))) {
                            $clink = $this->getParams('clink') . "&c_action=edit&comment_id=" . $edata['comment_id'];
                        }
                    }
                }

                $can_post = iMEMBER || fusion_get_settings('guestposts');
                // Comments form
                //$form_action = fusion_get_settings('site_path').str_replace('../', '', self::format_clink($clink));
                $form_action = self::formatClink($clink);

                // why would we need to split this?
                $comments_form_open = openform("inputform", "POST", $form_action, ["form_id" => $this->getParams("comment_key") . "-inputform"]);

                if ($can_post) {

                    $comments_form_open .= form_hidden("comment_id", "", "", ["input_id" => $this->getParams("comment_key") . "-comment_id"]);
                    $comments_form_open .= form_hidden("comment_cat", "", $edata["comment_cat"], ["input_id" => $this->getParams("comment_key") . "-comment_cat"]);

                    // Ratings dropdown
                    if (fusion_get_settings("ratings_enabled") && $this->getParams("comment_allow_ratings") && $this->getParams("comment_allow_vote")) {
                        $ratings_input = form_select("comment_rating", $this->locale["r106"], "",
                            [
                                "input_id" => $this->getParams("comment_key") . "-comment_rating",
                                "options" => [
                                    5 => $this->locale["r120"],
                                    4 => $this->locale["r121"],
                                    3 => $this->locale["r122"],
                                    2 => $this->locale["r123"],
                                    1 => $this->locale["r124"],
                                ],
                            ]
                        );
                    }

                    // Captcha for Guest... turn this into a modal pop up
                    $captcha = $this->displayCaptchaInput();

                    $userdata = fusion_get_userdata();

                    $name_input = (iGUEST ?
                        form_text("comment_name", $this->locale["c104"], "", [
                            "max_length" => 30,
                            "required" => TRUE,
                            "input_id" => $this->getParams("comment_key") . "-comment_name",
                        ]) : "");

                    $subject_input = $this->getParams("comment_allow_subject") ? form_text("comment_subject", $this->locale["c113"], $edata["comment_subject"], ["required" => TRUE, "input_id" => $this->getParams("comment_key") . "-comment_subject"]) : "";

                    // Add support custom template
                    $message_input = form_textarea($edata['comment_cat'] ? 'comment_message_reply' : 'comment_message', '', $edata['comment_message'],
                        [
                            "input_id" => $this->getParams("comment_key") . "-comment_message",
                            "required" => 1,
                            "autosize" => TRUE,
                            "form_name" => "inputform",
                            "tinymce" => "simple",
                            "wordcount" => TRUE,
                            "placeholder" => "What\"s on your mind, " . display_name($userdata) . "?",
                            "type" => $this->getParams("comment_bbcode") ? "bbcode" : ($this->getParams("comment_tinymce") ? "tinymce" : "text"),
                        ]
                    );

                    $button = form_button("post_comment", $edata['comment_message'] ? $this->locale['c103'] : $this->locale['c102'], ($edata['comment_message'] ? $this->locale['c103'] : $this->locale['c102']),
                        [
                            "class" => "btn-primary spacer-sm post_comment",
                            "input_id" => $this->getParams("comment_key") . "-post_comment"
                        ]
                    );
                }

                $comments_form_close = closeform();
            }

            return display_comment_form([
                "comment_form_open" => $comments_form_open ?? '',
                "comment_form_close" => $comments_form_close ?? '',
                "comment_form_id" => $this->getParams('comment_key') . '_edit_comment',
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
                "comment_form_avatar" => display_avatar(fusion_get_userdata(), $this->getParams("comment_form_avatar_size"), FALSE, FALSE),
            ]);

        }

        return '';
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
    private function displayAllComments($c_data, $index, $options) {
        $comments_html = '';
        foreach ($c_data[$index] as $comments_id => $data) {

            $data['comment_ratings'] = '';
            if (fusion_get_settings('ratings_enabled') && $this->getParams('comment_allow_ratings')) {
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
                'comment_id' => $data['comment_id'],
                'comment_list_id' => 'c' . $data['comment_id'],
                'comment_cat_id' => $data['comment_cat'],
                'comment_date' => $data['comment_datestamp'],
                'comment_ratings' => $data['comment_ratings'],
                'comment_subject' => $data['comment_subject'],
                'comment_message' => $data['comment_message'],
                'comment_reply_link' => ($data['reply_link'] ? "<a href='" . $data['reply_link'] . "' class='comments-reply display-inline' data-id='$comments_id'>" . $this->locale['c112'] . "</a>" : ''),
                'comment_edit_link' => ($data['edit_link'] ? "<a href='" . $data['edit_link']['link'] . "' class='edit-comment display-inline' data-id='" . $data['comment_id'] . "' data-api='$data_api' data-key='" . $this->getParams('comment_key') . "'>" . $data['edit_link']['name'] . "</a>" : ''),
                'comment_delete_link' => ($data['delete_link'] ? "<a href='" . $data['delete_link']['link'] . "' class='delete-comment display-inline' data-id='" . $data['comment_id'] . "' data-api='$data_api' data-type='" . $options['comment_item_type'] . "' data-item='" . $options['comment_item_id'] . "' data-key='" . $this->getParams('comment_key') . "'>" . $data['delete_link']['name'] . "</a>" : ''),
                'comment_reply_form' => ($data['reply_form'] ?? ''),
                //'comment_reply_count' => (isset($c_data[$data['comment_id']]) ? count($c_data[$data['comment_id']]) : 0),
                //'comment_nested' => (isset($c_data[$data['comment_id']]) ? $this->displayAllComments($c_data, $data['comment_id'], $options) : ''),
            ];

            $comments_html .= display_comments_list($data);
        }

        return $comments_html;
    }

    /**
     * Check permissions
     */
    protected function checkPermissions() {
        $my_id = fusion_get_userdata('user_id');
        if (dbcount("(rating_id)", DB_RATINGS, "
            rating_user='" . $my_id . "'
            AND rating_item_id='" . $this->getParams('comment_item_id') . "'
            AND rating_type='" . $this->getParams('comment_item_type') . "'
            "
        )
        ) {
            $this->replaceParam('comment_allow_vote', FALSE); // allow ratings
        }
        if (dbcount("(comment_id)", DB_COMMENTS, "
            comment_name='" . $my_id . "' AND comment_cat='0'
            AND comment_item_id='" . $this->getParams('comment_item_id') . "'
            AND comment_type='" . $this->getParams('comment_item_type') . "'
            "
            )
            && $this->getParams('comment_once')
        ) {
            $this->replaceParam('comment_allow_post', FALSE); // allow post
        }
    }

    /**
     * Get Comment Object Parameter
     *
     * @param null $key null for all array
     *
     * @return null
     */
    public function getParams($key = NULL) {
        if ($key !== NULL) {
            return $this->comment_params[self::$key][$key] ?? NULL;
        }

        return $this->comment_params[self::$key];
    }

    /**
     * Replace Comment Object Parameter
     *
     * @param $param
     * @param $value
     */
    public function replaceParam($param, $value) {
        if (isset($this->comment_params[self::$key][$param])) {
            $this->comment_params[self::$key][$param] = $value;
        }
    }

    /**
     * Set Comment Object Parameters
     *
     * @param array $params
     */
    protected function setParams(array $params = []) {
        $this->comment_params[self::$key] = $params;
    }

    /**
     * Set empty comment data
     */
    protected function setEmptyCommentData() {
        $this->comment_data = [
            'comment_id' => get('comment_id', FILTER_VALIDATE_INT) ?? '0', // isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : 0,
            'comment_name' => '',
            'comment_subject' => '',
            'comment_message' => '',
            'comment_datestamp' => time(),
            'comment_item_id' => $this->getParams('comment_item_id'),
            'comment_type' => $this->getParams('comment_item_type'),
            'comment_cat' => 0,
            'comment_ip' => USER_IP,
            'comment_ip_type' => USER_IP_TYPE,
            'comment_hidden' => 0,
            "comment_child_count" => 0,
        ];
    }

    /**
     * Execute comment update
     */
    protected function executeCommentUpdate() {

        $this->replaceParam('comment_user', $this->userdata['user_id']);

        // Non Jquery Actions
        if (isset($_GET['comment_reply'])) {
            add_to_jquery("scrollTo('comments_reply_form');");
        }

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
                    ':comment_id' => intval(stripinput($_GET['comment_id'])),
                    ':comment_hidden' => 0,
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
                    $func = $this->getParams('comment_delete_callback_function');
                    if (is_callable($func)) {
                        $func($this->getParams());
                    }

                    redirect($redirect_link);
                }
            }
        }

        /** Update & Save */
        // Ratings Removal Update
        // post comment_type, comment_item_id, remove_ratings_vote;
        if (iMEMBER && $this->getParams('comment_allow_ratings') && !$this->getParams('comment_allow_vote')) {
            if (isset($_POST['remove_ratings_vote'])) {
                $my_id = fusion_get_userdata('user_id');
                $delete_ratings = "DELETE FROM " . DB_RATINGS . "
                WHERE rating_item_id='" . $this->getParams('comment_item_id') . "'
                AND rating_type = '" . $this->getParams('comment_item_type') . "'
                AND rating_user = '$my_id'";
                $result = dbquery($delete_ratings);
                if ($result) {
                    redirect(self::formatClink($this->getParams('clink')));
                }
            }
        }

        /**
         * Post Comment, Reply Comment
         */
        if ((iMEMBER || $this->settings['guestposts']) && check_post('post_comment')) {

            if (!iMEMBER && $this->settings['guestposts']) {
                // Process Captchas
                $_CAPTCHA_IS_VALID = FALSE;
                include INCLUDES . "captchas/" . $this->settings['captcha'] . "/captcha_check.php";
                if (!$_CAPTCHA_IS_VALID) {
                    fusion_stop();
                    addnotice("danger", $this->locale['u194']);
                }
            }

            // do not use get, otherwise remote api will have integration problem
            $comment_data = [
                'comment_id' => post('comment_id', FILTER_VALIDATE_INT) ?? 0,
                'comment_name' => iMEMBER ? $this->userdata['user_id'] : form_sanitizer($_POST['comment_name'], '', 'comment_name'),
                'comment_subject' => !post('comment_cat', FILTER_VALIDATE_INT) && $this->getParams('comment_allow_subject') ? sanitizer('comment_subject', '', 'comment_subject') : '',
                'comment_item_id' => $this->getParams('comment_item_id'),
                'comment_type' => $this->getParams('comment_item_type'),
                'comment_cat' => sanitizer('comment_cat', '0', 'comment_cat'),
                'comment_message' => sanitizer('comment_message', '', 'comment_message'),
                'comment_ip' => USER_IP,
                'comment_ip_type' => USER_IP_TYPE,
                'comment_hidden' => 0,
            ];

            $ratings_query = "
            SELECT rating_id FROM " . DB_RATINGS . " WHERE rating_item_id='" . $comment_data['comment_item_id'] . "'
            AND rating_type='" . $comment_data['comment_type'] . "' AND rating_user='" . $comment_data['comment_name'] . "'
            ";

            $ratings_data = [];

            $ratings_id = dbresult(dbquery($ratings_query), 0);

            if ($this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote') && isset($_POST['comment_rating'])) {
                $ratings_data = [
                    'rating_id' => $ratings_id,
                    'rating_item_id' => $this->getParams('comment_item_id'),
                    'rating_type' => $this->getParams('comment_item_type'),
                    'rating_user' => $comment_data['comment_name'],
                    'rating_vote' => form_sanitizer($_POST['comment_rating'], 0, 'comment_rating'),
                    'rating_datestamp' => time(),
                    'rating_ip' => USER_IP,
                    'rating_ip_type' => USER_IP_TYPE,
                ];
            }

            if (iMEMBER && $comment_data['comment_id']) {

                // Update comment
                if ((iADMIN && checkrights("C")) ||
                    (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='" . $comment_data['comment_id'] . "'
                        AND comment_item_id='" . $this->getParams('comment_item_id') . "'
                        AND comment_type='" . $this->getParams('comment_item_type') . "'
                        AND comment_name='" . $this->userdata['user_id'] . "'
                        AND comment_hidden='0'")) && fusion_safe()
                ) {

                    $c_name_query = "SELECT comment_name FROM " . DB_COMMENTS . " WHERE comment_id='" . $comment_data['comment_id'] . "'";
                    $comment_data['comment_name'] = dbresult(dbquery($c_name_query), 0);

                    dbquery_insert(DB_COMMENTS, $comment_data, 'update');
                    $this->comment_params[self::$key]['post_id'] = $comment_data['comment_id'];

                    $func = $this->getParams('comment_edit_callback_function');
                    if (is_callable($func)) {
                        $func($this->getParams());
                    }

                    if (iMEMBER && $this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote')) {
                        dbquery_insert(DB_RATINGS, $ratings_data, ($ratings_data['rating_id'] ? 'update' : 'save'));
                    }

                    if ($this->settings['comments_sorting'] == "ASC") {
                        $c_operator = "<=";
                    } else {
                        $c_operator = ">=";
                    }

                    $c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_id" . $c_operator . "'" . $comment_data['comment_id'] . "'
                            AND comment_item_id='" . $this->getParams('comment_item_id') . "'
                            AND comment_type='" . $this->getParams('comment_item_type') . "'");

                    $c_start = (ceil($c_count / $this->settings['comments_per_page']) - 1) * $this->settings['comments_per_page'];
                    if (fusion_safe()) {
                        addnotice("success", $this->locale['c114']);
                        $_c = (isset($c_start) && isnum($c_start) ? $c_start : "");
                        $c_link = $this->getParams('clink');
                        redirect(self::formatClink("$c_link&c_start=$_c"));
                    }
                }

            } else {

                $comment_data['comment_datestamp'] = time();

                if (fusion_safe()) {

                    $c_start = 0;

                    if ($comment_data['comment_name'] && $comment_data['comment_message']) {

                        require_once INCLUDES . "flood_include.php";

                        if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='" . USER_IP . "'")) {

                            $id = dbquery_insert(DB_COMMENTS, $comment_data, 'save');

                            $this->comment_params[self::$key]['post_id'] = $id;

                            $func = $this->getParams('comment_post_callback_function');

                            if (is_callable($func)) {
                                $func($this->getParams());
                            }

                            if (iMEMBER && fusion_get_settings('ratings_enabled') && $this->getParams('comment_allow_ratings') && $this->getParams('comment_allow_vote')) {
                                dbquery_insert(DB_RATINGS, $ratings_data, ($ratings_data['rating_id'] ? 'update' : 'save'));
                            }

                            if ($this->settings['comments_sorting'] == "ASC") {
                                $c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='" . $this->getParams('comment_item_id') . "' AND comment_type='" . $this->getParams('comment_item_type') . "'");
                                $c_start = (ceil($c_count / $this->settings['comments_per_page']) - 1) * $this->settings['comments_per_page'];
                            }

                            redirect(self::formatClink($this->getParams('clink')) . "&c_start=" . $c_start . "#c" . $id);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $clink
     *
     * @return string
     */
    protected static function formatClink($clink) {
        if (empty(self::$clink[$clink])) {
            $fusion_query = [];
            $url = ((array)parse_url(htmlspecialchars_decode($clink))) + [
                    'path' => '',
                    'query' => '',
                ];
            if ($url['query']) {
                parse_str($url['query'], $fusion_query); // this is original.
            }
            $fusion_query = array_diff_key($fusion_query, array_flip(["comment_reply"]));
            $prefix = $fusion_query ? '?' : '';
            self::$clink[$clink] = $url['path'] . $prefix . http_build_query($fusion_query);
        }

        return (string)self::$clink[$clink];
    }

    /**
     * @param $rows
     * @return float|int|mixed
     */
    protected function commentStart($rows) {
        // Pagination control string
        self::$c_start = isset($_GET['c_start_' . $this->getParams('comment_key')]) && isnum($_GET['c_start_' . $this->getParams('comment_key')]) ? $_GET['c_start_' . $this->getParams('comment_key')] : 0;

        // Only applicable if sorting is Ascending. If descending, the default $c_start is always 0 as latest.
        if (fusion_get_settings('comments_sorting') == 'ASC') {
            $getname = 'c_start_' . $this->getParams('comment_key');
            if (!isset($_GET[$getname]) && $rows > $this->cpp) {
                self::$c_start = (ceil($rows / $this->cpp) - 1) * $this->cpp;
            }
        }

        return self::$c_start;
    }

    /*
     * Fetches comment data
     */
    protected function getComments() {

        if (fusion_get_settings('comments_enabled')) {

            $this->c_arr['c_info']['comments_count'] = format_word(0, $this->locale['fmt_comment']);
            $this->c_arr['c_info']['total_comments'] = 0;

            if ($this->getParams('comment_allow_ratings')) {
                $this->c_arr['c_info']['ratings_count'] = dbarray($this->ratingsQuery());
                $this->c_arr['c_info']['ratings_remove_form'] = ($this->getParams('comment_allow_ratings') && !$this->getParams('comment_allow_vote')) ? $this->displayRatingsForm() : '';
            }

            $c_rows = $this->totalCommentsCount();

            $this->c_arr['c_info']['total_comments'] = $c_rows;

            $root_comment_rows = $this->rootCommentsCount();

            if ($root_comment_rows) {

                self::$c_start = $this->commentStart($root_comment_rows);

                $dbquery = $this->commentsQuery($this->getParams('comment_cat_id'));

                if (dbrows($dbquery)) {

                    // might not be useful any longer
                    $i = ($this->settings['comments_sorting'] == "ASC" ? self::$c_start + 1 : $root_comment_rows - self::$c_start);

                    if ($root_comment_rows > $this->cpp) {
                        // make into load more comments using JS
                        $this->c_arr['c_info']['c_makepagenav'] = makepagenav(self::$c_start, $this->cpp, $root_comment_rows, 3, $this->getParams('clink') . (stristr($this->getParams('clink'), '?') ? "&" : '?'), "c_start_" . $this->getParams('comment_key'));
                    }

                    if (iADMIN && checkrights('C')) {
                        $this->c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
                        $this->c_arr['c_info']['admin_link'] .= "<a href='" . ADMIN . "comments.php" . fusion_get_aidlink() . "&ctype=" . $this->getParams('comment_item_type') . "&comment_item_id=" . $this->getParams('comment_item_id') . "'>" . $this->locale['c106'] . "</a>";
                    }

                    while ($row = dbarray($dbquery)) {

                        $this->parseCommentsData($row);
                        // this might not be useful any longer
                        $this->settings['comments_sorting'] == "ASC" ? $i++ : $i--;
                    }

                    $this->c_arr['c_info']['comments_per_page'] = $this->cpp;

                    $this->c_arr['c_info']['comments_count'] = format_word(number_format($this->c_arr['c_info']['total_comments']), $this->locale['fmt_comment']);
                }
            }
        }
    }

    /*
     * Parse comment results
     */
    protected function parseCommentsData($row) {

        $can_reply = iMEMBER || fusion_get_settings('guestposts');

        //        $garray = [];
//
//        if (!isnum($row['comment_name'])) {
//            $garray = [
//                'user_id' => 0,
//                'user_name' => $row['comment_name'],
//                'user_avatar' => '',
//                'user_status' => 0,
//            ];
//        }


        // get the user? no need.
//        $row = array_merge($row, isnum($row['comment_name']) ? fusion_get_user($row['comment_name']) : $garray);

        if ((iADMIN && checkrights("C")) || (iMEMBER && $row['comment_name'] == $this->userdata['user_id'] && isset($row['user_name']))) {
            $actions = [
                "edit_link" => [
                    //clean_request('c_action=edit&comment_id='.$row['comment_id'], array('c_action', 'comment_id'),FALSE)."#edit_comment";
                    'link' => $this->getParams('clink') . "&c_action=edit&comment_id=" . $row['comment_id'] . "#edit_comment",
                    'name' => $this->locale['edit']],
                "delete_link" => [
                    //clean_request('c_action=delete&comment_id='.$row['comment_id'], array('c_action', 'comment_id'), FALSE);
                    'link' => $this->getParams('clink') . "&c_action=delete&comment_id=" . $row['comment_id'],
                    'name' => $this->locale['delete']],
            ];
        }

        // Reply Form
        if ($this->getParams('comment_allow_reply') && $can_reply) {

            // Adjust this to array instead of string
            $captcha = $this->displayCaptchaInput();

            $comments_form_open = openform('inputform-' . $row['comment_id'], 'POST', self::formatClink($this->getParams('clink'))) .
                form_hidden("comment_cat", "", $this->comment_data['comment_cat'], ['input_id' => 'comment_cat-' . $row['comment_id']]);

            $name_input = (iGUEST ? form_text('comment_name', fusion_get_locale('c104'), $this->comment_data['comment_name'],
                [
                    'max_length' => 30,
                    'input_id' => 'comment_name-' . $row['comment_id'],
                    'form_name' => 'comments_reply_frm-' . $row['comment_id'],
                ]
            ) : '');

            $message_input = form_textarea("comment_message", "", $this->comment_data['comment_message'],
                [
                    "tinymce" => "simple",
                    "autosize" => TRUE,
                    "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "bbcode",
                    "input_id" => "comment_message-" . $row['comment_id'],
                    "form_name" => "inputform-" . $this->comment_data['comment_cat'],
                    "required" => TRUE,
                ]);

            $button = form_button("post_comment", fusion_get_locale('c102'), "post_" . $row['comment_id'], [
                    "class" => "btn-primary",
                    "input_id" => "post_comment-" . $row['comment_id'],
                ]
            );

            $comments_form_close = closeform();
        }

        /** formats $row */
        $row = [
            //            "i" => $i,
            "comment_id" => $row['comment_id'],
            "comment_cat" => $row['comment_cat'],
            "user_avatar_display" => display_avatar($row, $this->getParams("comment_avatar_size")), // isnum($row['comment_name']) ? display_avatar($row, self::$avatar_size) : display_avatar([], self::$avatar_size),
            "user_name_display" => display_name($row),
            "reply_link" => $can_reply == TRUE ? self::formatClink($this->getParams('clink')) . '&comment_reply=' . $row['comment_id'] . '#c' . $row['comment_id'] : '',
            // Comments Form
            "comment_form_open" => $comments_form_open ?? '',
            "comment_form_close" => $comments_form_close ?? '',
            "comment_name_input" => $name_input ?? '',
            "comment_message_input" => $message_input ?? '',
            "comment_captcha" => [
                "captcha" => $captcha['form'] ?? '',
                "input" => $captcha['input'] ?? '',
            ],
            "comment_button" => $button ?? '',
            // end form
            "ratings" => $row['ratings'] ?? '',
            "datestamp" => $row['comment_datestamp'],
            "comment_datestamp" => showdate('longdate', $row['comment_datestamp']),
            "comment_time" => timer($row['comment_datestamp']),
            "comment_subject" => $row['comment_subject'],
            "comment_message" => parse_text($row['comment_message'], ['decode' => FALSE, 'add_line_breaks' => TRUE]),
            "comment_name" => isnum($row['comment_name']) ? display_name($row) : $row['comment_name'],
            "edit_link" => $actions['edit_link'] ?? [],
            "delete_link" => $actions['delete_link'] ?? [],
            "comment_child_count" => dbcount("(comment_id)", DB_COMMENTS, "comment_cat=:cat_id AND comment_hidden=0", [':cat_id' => $row['comment_id']]),
        ];

//        if ($row['comment_child_count']) {
//            $c_result = $this->commentsQuery($row['comment_id']);
//            if (dbrows($c_result)) {
//                $x = 1;
//                while ($rows = dbarray($c_result)) {
//                    $this->parseCommentsData($rows);
//                    $this->settings['comments_sorting'] == "ASC" ? $x++ : $x--;
//                }
//            }
//        }


        $id = $row['comment_id'];
        $parent_id = $row['comment_cat'] === NULL ? "0" : $row['comment_cat'];

        $this->c_arr['c_con'][$parent_id][$id] = $row;

    }

    /**
     * @return mixed
     */
    protected function ratingsQuery() {

        return dbquery("SELECT
                COUNT(rating_id) 'total',
                IF(avg(rating_vote), avg(rating_vote), 0) 'avg',
                SUM(IF(rating_vote='5', 1, 0)) '5',
                SUM(IF(rating_vote='4', 1, 0)) '4',
                SUM(IF(rating_vote='3', 1, 0)) '3',
                SUM(IF(rating_vote='2', 1, 0)) '2',
                SUM(IF(rating_vote='1', 1, 0)) '1'
                FROM " . DB_RATINGS . "
                WHERE rating_type=:ratings_type AND rating_item_id=:ratings_item_id", [
            ':ratings_type' => $this->getParams('comment_item_type'),
            ':ratings_item_id' => $this->getParams('comment_item_id'),
        ]);

    }

    /**
     * @return int
     */
    protected function totalCommentsCount() {
        return dbcount("('comment_id')", DB_COMMENTS, "comment_item_id=:comment_item_id AND comment_type=:comment_item_type AND comment_hidden=:comment_hidden",
            [
                ':comment_item_id' => $this->getParams('comment_item_id'),
                ':comment_item_type' => $this->getParams('comment_item_type'),
                ':comment_hidden' => 0,
            ]
        );
    }

    /**
     * @return int
     */
    protected function rootCommentsCount() {
        return dbcount("(comment_id)", DB_COMMENTS, "comment_item_id=:comment_item_id AND comment_type=:comment_item_type AND comment_cat=:zero AND comment_hidden=:zero2",
            [
                ':comment_item_type' => $this->getParams('comment_item_type'),
                ':comment_item_id' => $this->getParams('comment_item_id'),
                ':zero' => $this->getParams('comment_cat_id') ?? '0',
                ':zero2' => '0',
            ]);
    }

    /**
     * @param int $cat_id
     * @return mixed
     */
    protected function commentsQuery($cat_id = 0) {
        $comment_query = "SELECT c.*, u.user_id, u.user_name, u.user_firstname, u.user_lastname, u.user_displayname, u.user_avatar, u.user_status " . ($this->getParams('comment_allow_ratings') && fusion_get_settings('ratings_enabled') ? ", r.rating_vote 'ratings'" : '') . "
            FROM " . DB_COMMENTS . " c
            LEFT JOIN " . DB_USERS . " u ON u.user_id=c.comment_name 
            " . ($this->getParams('comment_allow_ratings') && fusion_get_settings('ratings_enabled') ? "LEFT JOIN " . DB_RATINGS . " r ON r.rating_item_id=c.comment_item_id AND r.rating_type=c.comment_type AND r.rating_user=c.comment_name" : '') . "
            WHERE c.comment_item_id=:itemId AND c.comment_type=:itemType AND c.comment_hidden=:hiddenNum AND c.comment_cat=:cid
            ORDER BY c.comment_datestamp " . $this->settings['comments_sorting'] . ", c.comment_id ASC, c.comment_cat ASC 
            LIMIT " . self::$c_start . ", " . $this->cpp;

        $comment_bind = [
            ':itemId' => $this->getParams('comment_item_id'),
            ':itemType' => $this->getParams('comment_item_type'),
            ':hiddenNum' => 0,
            ':cid' => $cat_id ?? '0',
        ];


        return dbquery($comment_query, $comment_bind);
    }

    /**
     * @return mixed
     */
    protected function commentEditQuery() {

        return dbquery("SELECT tcm.*
                        FROM " . DB_COMMENTS . " tcm
                        WHERE comment_id=:comment_id AND comment_item_id=:comment_item_id AND comment_type=:comment_type AND comment_hidden=:comment_hidden", [
            ':comment_id' => get('comment_id', FILTER_VALIDATE_INT),
            ':comment_item_id' => $this->getParams('comment_item_id'),
            ':comment_type' => $this->getParams('comment_item_type'),
            ':comment_hidden' => 0,
        ]);
    }

    /**
     * @return string
     */
    protected function displayRatingsForm() {
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

    /**
     * @param false $c_reply
     * @return array
     */
    protected function displayCaptchaInput($c_reply = FALSE) {
        $captcha = [];
        if (iGUEST && fusion_get_settings('guestposts') == TRUE && (!isset($_CAPTCHA_HIDE_INPUT) || (!$_CAPTCHA_HIDE_INPUT))) {
            $_CAPTCHA_HIDE_INPUT = FALSE;
            include INCLUDES . 'captchas/' . fusion_get_settings('captcha') . '/captcha_display.php';
            $captcha['html'] = display_captcha([
                'captcha_id' => ($c_reply ? 'reply_captcha_' : 'captcha_') . $this->getParams('comment_key'),
                'input_id' => ($c_reply ? 'reply_captcha_code_' : 'captcha_code_') . $this->getParams('comment_key'),
                'image_id' => ($c_reply ? 'reply_captcha_image_' : 'captcha_image_') . $this->getParams('comment_key'),
            ]);
            if (!$_CAPTCHA_HIDE_INPUT) {
                $captcha['input'] = form_text('captcha_code', $this->locale['global_151'], '', ['required' => TRUE, 'autocomplete_off' => TRUE, 'input_id' => 'captcha_code_' . $this->getParams('comment_key')]);
            }
        }

        return $captcha;
    }

}

require_once THEMES . 'templates/global/comments.tpl.php';
