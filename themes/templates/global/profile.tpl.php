<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: profile.tpl.php
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

use PHPFusion\Panels;

defined('IN_FUSION') || exit;


if (!function_exists('display_profile_form')) {
    /**
     * Edit Profile Form Template
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     */
    function display_profile_form(array $info = []) {
        $opentab = '';
        $closetab = '';
        if (!empty($info['tab_info'])) {
            $opentab = opentab($info['tab_info'], check_get('section') ? get('section') : 1, 'user-profile-form', TRUE);
            $closetab = closetab();
        }
        opentable('');
        echo $opentab;

        echo "<!--editprofile_pre_idx--><div id='profile_form' class='spacer-sm'>";
        echo openform('profileFrm', 'POST', FORM_REQUEST, ['enctype' => TRUE]);
        echo $info['user_id'];
        echo $info['user_name'];
        echo $info['user_firstname'];
        echo $info['user_lastname'];
        echo $info['user_addname'];
        echo $info['user_phone'];
        echo $info['user_email'];
        echo $info['user_hide_email'];
        echo $info['user_avatar'];
        echo $info['user_password'];
        echo $info['user_admin_password'];
        echo $info['user_custom'];
        echo $info['user_bio'];
        echo $info['validate'];
        echo $info['terms'];
        echo $info['button'];
        echo closeform();
        echo "</div><!--editprofile_sub_idx-->";

        echo $closetab;
        closetable();
    }
}


// New User Profile Proposals
if (!function_exists('display_up_settings')) {

    function navigation_panel($info) {

        if (!empty($info)) {
            $menu = '';
            $_get = get('section');
            $i = 0;
            foreach ($info as $key => $rows) {

                $active = (!$i && !$_get || $_get == $key ? ' active' : '');

                $menu .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">'
                    . '<a class="nav-link d-flex mb-0' . $active . '" href="' . $rows['link'] . '" aria-selected="true" role="tab">' .
                    '<span>' . get_image($rows['icon'], $rows['title'], '', '', 'class="icon me-2"') . '</span>' .
                    $rows['title'] . '
                    </a>'
                    . '</li>';

                $i++;
            }
        }

        return fusion_get_function('openside', '')
            . '<ul class="nav flex-column fw-bold gap-2 border-0" role="tablist">'
            . ($menu ?? '')
            . '</ul>'
            . fusion_get_function('closeside');
    }

    require_once __DIR__ . '/profile_settings/settings.tpl.php';
}

/**
 * Profile display view
 *
 * @param $info (array) - prepared responsive fields
 *              To get information of the current raw userData
 *              global $userFields; // profile object at profile.php
 *              $current_user_info = $userFields->getUserData(); // returns array();
 *              print_p($current_user_info); // debug print
 */
if (!function_exists('display_user_profile')) {

    function display_user_profile($info) {
        Panels::getInstance()->hideAll();

        add_to_css('.cat-field .field-title > img{max-width:25px;}');

        echo fusion_get_template('profile', $info);
    }
}

