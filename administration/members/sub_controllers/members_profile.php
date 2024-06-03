<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: members_profile.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Administration\Members\Sub_Controllers;

use PHPFusion\UserFields;
use Pro\Admin\Members\Helper;

/**
 * Class Members_Profile
 * Controller for View, Add, Edit and Delete Users Account
 * @deprecated
 * @package Administration\Members\Sub_Controllers
 */
class MembersProfile extends Helper {

    /*
     * Displays user profile
     */
    public static function display_user_profile() {
        $settings = fusion_get_settings();
        $userFields = new UserFields();
        $userFields->postName = "register";
        $userFields->postValue = self::$locale['u101'];
        $userFields->displayValidation = $settings['display_validation'];
        $userFields->displayTerms = $settings['enable_terms'];
        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->registration = FALSE;
        $userFields->userData = self::$user_data;
        $userFields->method = 'display';
        $userFields->display_profile_output();
    }

    public static function edit_user_profile() {
        if (isset($_POST['savechanges'])) {
            $userInput = new \UserFieldsInput();
            $userInput->userData = self::$user_data; // full user data
            $userInput->adminActivation = 0;
            $userInput->registration = FALSE;
            $userInput->emailVerification = 0;
            $userInput->isAdminPanel = TRUE;
            $userInput->skipCurrentPass = TRUE;
            $userInput->saveUpdate();
            self::$user_data = $userInput->getData(); // data overridden on error.
            unset($userInput);
            if (\defender::safe()) {
                redirect(FUSION_SELF.fusion_get_aidlink());
            }
        }
        $userFields = new UserFields();
        $userFields->postName = 'savechanges';
        $userFields->postValue = self::$locale['ME_437'];
        $userFields->displayValidation = 0;
        $userFields->displayTerms = FALSE;
        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->userData = self::$user_data;
        $userFields->method = 'input';
        $userFields->admin_mode = TRUE;
        $userFields->display_profile_input();
    }



}
