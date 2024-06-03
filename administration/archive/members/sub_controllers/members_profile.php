<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: members_profile.php
| Author: Core Development Team
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

use Administration\Members\Members_Admin;
use PHPFusion\UserFields;
use PHPFusion\Userfields\Accounts\AccountActions;
use PHPFusion\UserFieldsInput;

/**
 * Class Members_Profile
 * Controller for View, Add, Edit and Delete Users Account
 *
 * @package Administration\Members\Sub_Controllers
 */
class Members_Profile extends Members_Admin {

    /*
     * Displays new user form
     */
    public static function display_new_user_form() {
        $settings = fusion_get_settings();
        if ( check_post( 'add_new_user' ) ) {
            $userInput = new UserFieldsInput();
            $userInput->validation = FALSE;
            $userInput->emailVerification = FALSE;
            $userInput->adminActivation = FALSE;
            $userInput->registration = TRUE;
            $userInput->skipCurrentPass = TRUE;
            $userInput->saveInsert();
            unset( $userInput );
            if ( fusion_safe() ) {
                redirect( FUSION_SELF . fusion_get_aidlink() );
            }
        }
        $userFields = new UserFields();
        $userFields->postName = "add_new_user";
        $userFields->postValue = self::$locale['ME_450'];
        $userFields->displayValidation = $settings['display_validation'];
        $userFields->plugin_folder = [INCLUDES . "user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE . LOCALESET . "user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->registration = TRUE;
        $userFields->method = 'input';
        $userFields->displayProfileInput();
    }

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
        $userFields->plugin_folder = [INCLUDES . "user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE . LOCALESET . "user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->registration = FALSE;
        $userFields->userData = self::$user_data;
        $userFields->method = 'display';
        $userFields->displayProfileOutput();
    }

    public static function edit_user_profile() {
        if ( check_post( 'savechanges' ) ) {
            $userInput = new \UserFieldsInput();
            $userInput->userData = self::$user_data; // full user data
            $userInput->adminActivation = 0;
            $userInput->registration = FALSE;
            $userInput->emailVerification = 0;
            $userInput->isAdminPanel = TRUE;
            $userInput->skipCurrentPass = TRUE;
            $userInput->saveUpdate();
            self::$user_data = $userInput->setUserHash(); // data overridden on error.
            unset( $userInput );
            if ( fusion_safe() ) {
                redirect( FUSION_SELF . fusion_get_aidlink() );
            }
        }
        $userFields = new UserFields();
        $userFields->postName = 'savechanges';
        $userFields->postValue = self::$locale['ME_437'];
        $userFields->displayValidation = 0;
        $userFields->displayTerms = FALSE;
        $userFields->plugin_folder = [INCLUDES . "user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE . LOCALESET . "user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->userData = self::$user_data;
        $userFields->method = 'input';
        $userFields->moderation = TRUE;
        $userFields->displayProfileInput();
    }

    public static function delete_user() {
        if ( check_post( 'delete_user' ) ) {

            try {

                $result = dbquery( "SELECT user_id, user_avatar FROM " . DB_USERS . " WHERE user_id=:user_id AND user_level >:user_level",
                    [
                        ':user_id'    => self::$user_id,
                        ':user_level' => USER_LEVEL_SUPER_ADMIN
                    ]
                );
                $rows = dbrows( $result );

                if ( $rows != '0' ) {
                    $data = dbarray( $result );
                    $user_id = $data['user_id'];
                    $accountClass = new AccountActions( $user_id );
                    $accountClass->deleteUser();
                }

                redirect( FUSION_SELF . fusion_get_aidlink() );

            } catch ( \Exception $e ) {
                addnotice( 'danger', $e->getMessage() );
            }
        }

        echo "<div class='well'>\n";
        echo "<h4>" . self::$locale['ME_454'] . "</h4>";
        echo "<p>" . nl2br( sprintf( self::$locale['ME_455'], "<strong>" . self::$user_data['user_name'] . "</strong>" ) ) . "</p>\n";
        echo openform( 'mod_form', 'post', FUSION_SELF . fusion_get_aidlink() . "&amp;ref=delete&amp;lookup=" . self::$user_id . "" );
        echo "<div class='spacer-sm'>\n";
        echo form_button( 'delete_user', self::$locale['ME_456'], self::$locale['ME_456'], ['class' => 'btn-danger m-r-10'] );
        echo form_button( 'cancel', self::$locale['cancel'], self::$locale['cancel'] );
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
    }

    public static function delete_unactivated_user() {

        if ( check_post( 'delete_newuser' ) ) {
            dbquery( "DELETE FROM " . DB_NEW_USERS . " WHERE user_name=:user_name", [':user_name' => get( 'lookup' )] );
            redirect( clean_request( '', ['ref', 'lookup', 'newuser'], FALSE ) );
        }

        echo "<div class='well'>\n";
        echo "<h4>" . self::$locale['ME_454'] . "</h4>";
        echo "<p>" . nl2br( sprintf( self::$locale['ME_457'], "<strong>" . get( 'lookup' ) . "</strong>" ) ) . "</p>\n";
        echo openform( 'mod_form', 'post', FUSION_REQUEST );
        echo "<div class='spacer-sm'>\n";
        echo form_button( 'delete_newuser', self::$locale['ME_456'], self::$locale['ME_456'], ['class' => 'btn-danger m-r-10'] );
        echo form_button( 'cancel', self::$locale['cancel'], self::$locale['cancel'] );
        echo "</div>\n";
        echo closeform();
        echo "</div>\n";
    }

    public static function activate_user() {

        $result = dbquery( "SELECT * FROM " . DB_NEW_USERS . " WHERE user_code=:code AND user_name=:name", [':code' => get( 'code' ), ':name' => get( 'lookup' )] );

        $data = dbarray( $result );
        $user_info = unserialize( base64_decode( $data['user_info'] ) );
        dbquery_insert( DB_USERS, $user_info, 'save' );
        dbquery( "DELETE FROM " . DB_NEW_USERS . " WHERE user_code=:code", [':code' => get( 'code' )] );

        addnotice( 'success', self::$locale['ME_469'] );
        redirect( clean_request( '', ['ref', 'lookup', 'code'], FALSE ) );
    }

    public static function reactivate_user() {
        $result = dbquery( "SELECT * FROM " . DB_USERS . " WHERE user_id=:user_id", [':user_id' => get( 'lookup' )] );

        $data = dbarray( $result );
        $data['user_status'] = 0;
        $data['user_lastvisit'] = 0;
        $data['user_actiontime'] = 0;
        dbquery_insert( DB_USERS, $data, 'update' );

        addnotice( 'success', self::$locale['ME_469'] );
        redirect( clean_request( '', ['ref', 'lookup'], FALSE ) );
    }

    public static function resend_email() {
        if ( check_get( 'lookup' ) && !isnum( get( 'lookup' ) ) ) {

            $dbquery = dbquery( "SELECT * FROM " . DB_NEW_USERS . " WHERE user_name=:uid", [':uid' => get( 'lookup' )] );

            if ( dbrows( $dbquery ) ) {
                require_once INCLUDES . "sendmail_include.php";
                self::$user_data = dbarray( $dbquery );
                $activationUrl = fusion_get_settings( 'siteurl' ) . "register.php?email=" . self::$user_data['user_email'] . "&code=" . self::$user_data['user_code'];
                $message = str_replace( "[USER_NAME]", self::$user_data['user_name'], self::$locale['email_resend_message'] );
                $message = str_replace( "[SITENAME]", self::$settings['sitename'], $message );
                $message = str_replace( "[ACTIVATION_LINK]", $activationUrl, $message );
                $subject = str_replace( "[SITENAME]", self::$settings['sitename'], self::$locale['email_resend_subject'] );

                if ( !sendemail( self::$user_data['user_name'], self::$user_data['user_email'], self::$settings['siteusername'], self::$settings['siteemail'], $subject, $message ) ) {
                    addnotice( 'warning', self::$locale['u153'], 'all' );
                }

                if ( fusion_safe() ) {
                    dbquery( "UPDATE " . DB_NEW_USERS . " SET user_datestamp = '" . time() . "' WHERE user_name=:user_name", [':user_name' => get( 'lookup' )] );
                    addnotice( 'success', self::$locale['u165'] );
                    redirect( clean_request( '', ['ref', 'lookup'], FALSE ) );
                }

            } else {
                redirect( FUSION_SELF . fusion_get_aidlink() );
            }

        }

    }
}
