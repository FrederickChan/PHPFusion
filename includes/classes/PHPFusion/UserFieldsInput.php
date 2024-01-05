<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: UserFieldsInput.php
| Author: Hans Kristian Flaatten (Starefossen), meangczac (Chan)
| Lead Developer PHPFusion, Core Developer Team
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

use PHPFusion\Userfields\Accounts\AccountsValidate;

/**
 * Class UserFieldsInput
 *
 * @package PHPFusion
 */
class UserFieldsInput {

    public $adminActivation = FALSE;

    public $emailVerification = FALSE;

    public $verifyNewEmail = FALSE;

    public $userData = ['user_name' => NULL];

    public $validation = 0;

    public $registration = FALSE;

    public $skipCurrentPass = FALSE; // FALSE to skip pass. True to validate password. New Register always FALSE.

    public $isAdminPanel = FALSE;

    public $_method;

    public $_userEmail;

    private $data = [];

    public $moderation = 0;

    /**
     * Create user account
     */
    public function saveInsert() {
        $this->_method = "validate_insert";
        ( new AccountsValidate( $this ) )->createAccount();
    }

    /**
     * Update User Fields
     */
    public function saveUpdate() {

        $this->_method = "validate_update";
        $this->data['user_id'] = $this->userData['user_id'];

        ( new AccountsValidate( $this ) )->updateAccount();
    }

    /**
     * @return array
     */
    private function createInitUserdata() {
        $data = [];
        if ( !empty( $filter = fusion_filter_hook( 'fusion_create_userdata' ) ) ) {
            foreach ( $filter as $values ) {
                $data += $values;
            }
        }
        return $data;
    }

    /**
     * Initialise empty fields
     *
     * @return array
     */
    public function setEmptyFields() {

        /** Prepare initial variables for settings */
        if ( $this->_method == 'validate_insert' ) {

            // Compulsory Core Fields
            $data = [
                    'user_id'         => 0,
                    'user_name'       => '',
                    'user_email'      => '',
                    'user_hide_email' => 1,
                    'user_avatar'     => '',
                    'user_posts'      => 0,
                    'user_threads'    => 0,
                    'user_joined'     => time(),
                    'user_lastvisit'  => 0,
                    'user_ip'         => USER_IP,
                    'user_ip_type'    => USER_IP_TYPE,
                    'user_rights'     => '',
                    'user_groups'     => '',
                    'user_level'      => USER_LEVEL_MEMBER,
                    'user_status'     => $this->adminActivation == 1 ? 2 : 0,
                    'user_theme'      => 'Default',
                    'user_language'   => LANGUAGE,
                    'user_timezone'   => fusion_get_settings( 'timeoffset' ),
                ] + $this->createInitUserdata();

            return $data;
        }

        return NULL;
    }

    /**
     * @param $user_id
     *
     * @return array
     */
    public function setEmptySettingsField( $user_id ) {

        $settings = fusion_get_settings();

        return [
            'user_id'                   => $user_id,
            'user_auth'                 => 0,
            'user_hide_email'           => 1,
            'user_hide_phone'           => 1,
            'user_hide_location'        => 0,
            'user_hide_birthdate'       => 0,
            'user_inbox'                => $settings['pm_inbox_limit'],
            'user_outbox'               => $settings['pm_outbox_limit'],
            'user_archive'              => $settings['pm_archive_limit'],
            'user_pm_email'             => $settings['pm_email_notify'],
            'user_pm_save_sent'         => $settings['pm_save_sent'],
            'user_notify_comments'      => 1,
            'user_notify_mentions'      => 1,
            'user_notify_subscriptions' => 1,
            'user_notify_birthdays'     => 1,
            'user_notify_groups'        => 1,
            'user_notify_events'        => 1,
            'user_notify_messages'      => 1,
            'user_notify_updates'       => 1,
            'user_language'             => LANGUAGE,
        ];
    }

    /**
     * Handle new email verification procedures
     */
    public function verifyNewEmail() {

        $settings = fusion_get_settings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();

        require_once INCLUDES . "sendmail_include.php";
        mt_srand( (double)microtime() * 1000000 );

        $salt = "";
        for ( $i = 0; $i <= 10; $i++ ) {
            $salt .= chr( rand( 97, 122 ) );
        }

        $user_code = md5( $this->_userEmail . $salt );

        $email_verify_link = $settings['siteurl'] . 'edit_profile.php?code=' . $user_code;

        $mailbody = str_replace( "[EMAIL_VERIFY_LINK]", $email_verify_link, $locale['u203'] );
        $mailbody = str_replace( "[SITENAME]", $settings['sitename'], $mailbody );
        $mailbody = str_replace( "[SITEUSERNAME]", $settings['siteusername'], $mailbody );
        $mailbody = str_replace( "[USER_NAME]", $userdata['user_name'], $mailbody );

        $mailSubject = str_replace( "[SITENAME]", $settings['sitename'], $locale['u202'] );

        sendemail( $this->data['user_name'], $this->data['user_email'], $settings['siteusername'], $settings['siteemail'], $mailSubject, $mailbody );

        addnotice( 'warning', strtr( $locale['u200'], ['(%s)' => $this->_userEmail] ) );
        dbquery( "DELETE FROM " . DB_EMAIL_VERIFY . " WHERE user_id=:uid", [":uid" => (int)$this->data['user_id']] );
        dbquery( "INSERT INTO " . DB_EMAIL_VERIFY . " (user_id, user_code, user_email, user_datestamp) VALUES (':uid', ':code', ':email', ':time')", [
            ':uid'   => (int)$this->data['user_id'],
            ':code'  => $user_code,
            ':email' => $this->data['user_email'],
            ':time'  => time()
        ] );
    }

    /**
     * Admin needs hash
     * User just need match own user_id
     * Only admin needs a userhash to update
     *
     * @return bool
     */
    public function checkUpdateAccess() {
        return fusion_safe() && ( ( iADMIN && checkrights( 'M' ) && ( $this->userData['user_password'] == sanitizer( 'user_hash', '', "user_hash" ) ) ) || ( $this->data['user_id'] == $this->userData['user_id'] ) );
    }

    /**
     * Set user avatar
     */
    public function setUserAvatar() {
        if ( isset( $_POST['delAvatar'] ) ) {
            if ( $this->userData['user_avatar'] != "" && file_exists( IMAGES . "avatars/" . $this->userData['user_avatar'] ) && is_file( IMAGES . "avatars/" . $this->userData['user_avatar'] ) ) {
                unlink( IMAGES . "avatars/" . $this->userData['user_avatar'] );
            }
            $this->data['user_avatar'] = '';
        }
        if ( isset( $_FILES['user_avatar'] ) && $_FILES['user_avatar']['name'] ) { // uploaded avatar
            if ( !empty( $_FILES['user_avatar'] ) && is_uploaded_file( $_FILES['user_avatar']['tmp_name'] ) ) {
                $upload = form_sanitizer( $_FILES['user_avatar'], '', 'user_avatar' );
                if ( isset( $upload['error'] ) && !$upload['error'] ) {
                    // ^ maybe use empty($upload['error']) also can but maybe low end php version has problem on empty.
                    $this->data['user_avatar'] = $upload['image_name'];
                }
            }
        }
    }

    /**
     * Returns userhash added userdata array
     *
     * @return array
     */
    public function setUserHash() {
        if ( !empty( $this->userData['user_password'] ) ) {
            // when edit profile
            $this->data['user_hash'] = $this->userData['user_password'];
        } else if ( isset( $_POST['user_hash'] ) ) {
            // when new registration
            $this->data['user_hash'] = sanitizer( 'user_hash', '', 'user_hash' );
        }

        return $this->data;
    }

    /**
     * @param string $value
     */
    public function verifyCode( $value ) {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        if ( !preg_check( "/^[0-9a-z]{32}$/i", $value ) ) {
            redirect( BASEDIR . 'index.php' );
        }

        $result = dbquery( "SELECT * FROM " . DB_EMAIL_VERIFY . " WHERE user_code=:code", [':code' => $value] );

        if ( dbrows( $result ) ) {

            $data = dbarray( $result );

            if ( $data['user_id'] == $userdata['user_id'] ) {

                if ( $data['user_email'] != $userdata['user_email'] ) {

                    $result = dbquery( "SELECT user_email FROM " . DB_USERS . " WHERE user_email=:email", [':email' => $data['user_email']] );
                    if ( dbrows( $result ) > 0 ) {
                        addnotice( "danger", $locale['u164'] . "<br />\n" . $locale['u121'] );
                    } else {
                        addnotice( 'success', $locale['u169'] );
                    }

                    dbquery( "UPDATE " . DB_USERS . " SET user_email=:email WHERE user_id=:uid", [
                        ':email' => $data['user_email'],
                        ':uid'   => $data['user_id']
                    ] );

                    dbquery( "DELETE FROM " . DB_EMAIL_VERIFY . " WHERE user_id=:uid", [
                        ':uid' => $data['user_id']
                    ] );
                }
            } else {
                redirect( BASEDIR . 'index.php' );
            }
        } else {
            redirect( BASEDIR . 'index.php' );
        }
    }
}
