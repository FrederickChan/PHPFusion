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

use PHPFusion\Comments\CommentsAccess;
use PHPFusion\Comments\CommentsInput;
use PHPFusion\Comments\CommentsViewBuilder;

/**
 * Class Comments
 *
 * @package PHPFusion
 *          Rating is not working
 *          Edit is not working
 */
class Comments extends Comments\Comments {

    protected static $key = 'Default';

    private function __construct() {
        // Set Settings
        $this->settings = fusion_get_settings();

        // Set Global Locale
        $this->locale = fusion_get_locale("",
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
        $this->cpp = fusion_get_settings("comments_per_page");

        $this->comment_data = fusion_encrypt(fusion_encode(self::getParams()), SECRET_KEY);
    }

    /**
     * Get an instance by key
     *
     * @param array $params
     * @param string $key
     *
     * @return static
     */
    public static function getInstance(array $params = [], $key = "Default") {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
            self::$key = $key;
            $params["comment_key"] = $key;
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

        $obj->setComments();

        (new CommentsAccess($obj))->checkPermissions();

        (new CommentsInput($obj))->executeCommentUpdate();

        $obj->getComments();

        $obj->setJs();

    }

    public function setJs() {
        if (!defined("COMMENT_JS")) {
            define("COMMENT_JS", TRUE);
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
            var params = { id: $(this).data('comment-id'), params: '" . $this->comment_param_data . "' };
            $.get('" . INCLUDES . "api/?api=comment-get', params, function(e) {
                //console.log(e);
                nextContainer.html(e);
            });
        });
        
        $(document).on('click', 'a[data-comment-r=\"reply\"]', function(e) {
            e.preventDefault();   
            var tgt = $(this).data('comment-target'),
            formContainer = $(tgt);
            var params = { id: $(this).data('comment-id'), params: '" . $this->comment_param_data . "', 'type':'input' };            
             $.get('" . INCLUDES . "api/?api=comment-get', params, function(e) {
                //console.log(e);
                formContainer.html(e);
            });            
            $(this).closest('li.comment-item').addClass('r-open');
            formContainer.addClass('open');
        });        
        
        $(document).on('click', 'button[name=\"post_comment\"]', function(e) {
            e.preventDefault();
            var data = $(this).closest('form').serializeArray();
            data.push({name: 'params', value: '" . $this->comment_param_data . "'});
            $.post('" . INCLUDES . "api/?api=comment-update', data, function (e) {
                console.log(e);
            });
        });
        ");
        }
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
                        $stars .= '<i class="fas fa - star text - warning"></i> ';
                    }
                    if ($remainder) {
                        for ($i = 1; $i <= $remainder; $i++) {
                            $stars .= '<i class="far fa - star text - lighter"></i> ';
                        }
                    }

                    for ($i = 5; $i >= 1; $i--) {
                        $bal = 5 - $i;
                        $stars_ = '';
                        for ($x = 1; $x <= $i; $x++) {
                            $stars_ .= '<i class="fas fa - star text - warning"></i> ';
                        }

                        for ($b = 1; $b <= $bal; $b++) {
                            $stars_ .= '<i class="far fa - star text - lighter"></i> ';
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
                "comment_form_container_id" => $this->getParams("comment_key") . " - comments_form",
                "comment_ratings" => $ratings_html,
                "comment_form_title" => ($this->getParams("comment_form_title") ?: $this->locale["c111"]),
                "comment_type" => $this->getParams("comment_item_type"),
                "clink" => $this->getParams("clink"),
                "comment_item_id" => $this->getParams("comment_item_id"),
                "options" => $this->getParams(),
                // Comments
                "comment_pagenav" => ($this->c_arr["c_info"]["c_makepagenav"] ? " < div class='text-left' > " . $this->c_arr["c_info"]["c_makepagenav"] . " </div > \n" : ""),
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
        return (!empty($this->c_arr["c_con"]) ? (new CommentsViewBuilder($this))->displayAllComments($this->c_arr["c_con"], $this->getParams("comment_cat_id") ?? "0", $this->getParams()) : display_no_comments($this->locale["c101"]));
    }

    /**
     * @return string
     */
    public function showCommentForm() {
        return (new CommentsViewBuilder($this))->displayCommentForm();
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

        return $this->comment_params[self::$key] ?? NULL;
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
    protected function setComments() {
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

        $this->comment_param_data = fusion_encrypt(fusion_encode(self::getParams()), SECRET_KEY);
    }

    /**
     * @param string $clink
     *
     * @return string
     */
    public static function formatClink($clink) {
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
            $captcha = (new CommentsViewBuilder($this))->displayCaptchaInput();

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
    public function commentEditQuery() {

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
    public function displayRatingsForm() {
        return CommentsViewBuilder::getInstance($this, self::$key)->displayRatingsForm();
    }
}

require_once THEMES . "templates/global/comments.tpl.php";
