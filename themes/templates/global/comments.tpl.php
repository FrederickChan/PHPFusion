<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: comments.tpl.php
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

if (!function_exists('display_comments_ui')) {
    function display_comments_ui($info) {
        return fusion_get_template('comments', $info);
    }
}

if (!function_exists('display_comment_form')) {
    function display_comment_form($info) {
        return fusion_get_template('comment-form', $info);
    }
}

if (!function_exists('display_no_comments')) {
    function display_no_comments($text) {
        return '<li class="no-comments-text"><p class="text-center">' . $text . '</p></li>';
    }
}

if (!function_exists('display_comments_list')) {
    function display_comments_list($info = []) {

        return fusion_get_template('comment-list', $info);

//        $html = "<li id='".$info['comment_list_id']."' class='m-b-15'>";
//
//        if (fusion_get_settings('comments_avatar')) {
//            $html .= "<div class='pull-left text-center m-r-15'>".$info['user_avatar']."</div>";
//        }
//
//        $html .= "<div class='overflow-hide'><div class='comment_name display-inline-block m-r-10'>".$info['user_name']."</div>";
//
//        if (!empty($info['comment_ratings'])) {
//            $html .= '<div class="ratings">'.$info['comment_ratings'].'</div>';
//        }
//
//        if ($info['comment_subject']) {
//            $html .= "<div class='comment_title'>".$info['comment_subject']."</div>";
//        }
//
//        $html .= "<div class='comment_message'>".$info['comment_message']."</div>";
//        $html .= "<div><small>";
//        $html .= !empty($info['reply_link']) ? $info['comment_reply_link'] : '';
//        $html .= !empty($info['edit_link']) ? ' &middot; '.$info['comment_edit_link'] : '';
//        $html .= !empty($info['delete_link']) ? ' &middot; '.$info['comment_delete_link'] : '';
//        $html .= " - <span class='comment_date'>".$info['comment_date']."</span>";
//        $html .= "</small></div>";
//
//        $html .= $info['comment_reply_form'];
//
//        $html .= "<ul class='sub_comments list-style-none'>".$info['comment_sub_comments']."</ul>";
//
//        $html .= "</li>";
//
//        return $html;
    }
}

if (!function_exists('display_comments_reply_form')) {
    function display_comments_reply_form($info) {
        $html = "<div class='comments_reply_form m-t-20 m-b-20'>";
        $html .= $info['comment_name'];
        $html .= $info['comment_message'];

        if (!empty($info['comment_captcha']['captcha'])) {
            $html .= '<div class="row">';
            $html .= '<div class="col-xs-12 col-sm-8 col-md-6">';

            $html .= $info['comment_captcha']['captcha'];

            $html .= '</div>';
            $html .= '<div class="col-xs-12 col-sm-4 col-md-6">';
            if (!empty($info['comment_captcha']['input'])) {
                $html .= $info['comment_captcha']['input'];
            }
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= $info['comment_post'];
        $html .= "</div>";

        return $html;
    }
}

if (!function_exists('display_comments_form')) {
    function display_comments_form($info) {

        return fusion_get_template('comments', $info);
    }
}

if (!function_exists('display_comments_ratings')) {
    function display_comments_ratings($info) {
        $html = '
        <div class="ratings overflow-hide m-b-10">
            <div class="well">
                <div class="row">
                    <div class="col-xs-12 col-xs-6">
                        ' . $info['stars'] . '
                        <span class="text-lighter m-l-5">' . $info['reviews'] . '</span>
                    </div>

                    <div class="col-xs-12 col-xs-6">';

        foreach ($info['ratings'] as $rating) {
            $html .= '<div>';
            $html .= '<span class="m-r-5">' . $rating['stars'] . ' (' . $rating['stars_count'] . ')</span>';
            $html .= '<div class="display-inline-block m-l-5" style="width:50%;">' . $rating['progressbar'] . '</div>';
            $html .= '</div>';
        }

        $html .= '</div>
                </div>
            </div>
            ' . $info['ratings_remove_button'] . '
        </div>';

        return $html;
    }
}
