<?php
/*
 * -------------------------------------------------------+
 * | PHPFusion Content Management System
 * | Copyright (C) PHP Fusion Inc
 * | https://phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename: theme.php
 * | Author:  Meangczac (Chan)
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */

namespace PHPFusion\Userfields\Accounts;

use Defender;

use GoogleAuthenticator\GoogleAuthenticator;
use PHPFusion\Authenticate;
use PHPFusion\EmailAuth;
use PHPFusion\PasswordAuth;
use PHPFusion\QuantumFields;
use PHPFusion\Userfields\UserFieldsValidate;
use function Sodium\add;

class AccountsValidate extends UserFieldsValidate {

    /**
     * @var string
     */
    private $_userEmail;
    /**
     * @var bool
     */
    private $_isValidCurrentPassword;
    /**
     * @var false|mixed
     */
    private $_newUserPassword;
    /**
     * @var false|mixed
     */
    private $_newUserPassword2;
    /**
     * @var string
     */
    private $_username;

    private $_quantum;
    private $_data;

    /**
     * Handle Username Input and Validation
     */
    public function setUserName() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        if ( $settings['username_change'] or $this->userFieldsInput->_method == 'validate_insert' ) {

            $uban = explode( ',', $settings['username_ban'] );
            $this->_username = sanitizer( 'user_name', '', 'user_name' );

            if ( $this->_username != $this->userFieldsInput->userData['user_name'] ) {

                if ( !preg_match( '/^[-a-z\p{L}\p{N}_]*$/ui', $this->_username ) ) {
                    // Check for invalid characters
                    fusion_stop();
                    Defender::setInputError( 'user_name' );
                    Defender::setErrorText( 'user_name', $locale['u120'] );

                } else if ( in_array( $this->_username, $uban ) ) {

                    // Check for prohibited usernames
                    fusion_stop();
                    Defender::setInputError( 'user_name' );
                    Defender::setErrorText( 'user_name', $locale['u119'] );

                } else {

                    // Make sure the username is not used already
                    $name_active = dbcount( "(user_id)", DB_USERS, "user_name=:name", [':name' => $this->_username] );
                    $name_inactive = dbcount( "(user_code)", DB_NEW_USERS, "user_name=:name", [':name' => $this->_username] );

                    if ( $name_active == 0 && $name_inactive == 0 ) {

                        return $this->_username;

                    } else {
                        fusion_stop();
                        Defender::setInputError( 'user_name' );
                        Defender::setErrorText( 'user_name', $locale['u121'] );
                    }

                }
            } else if ( $this->userFieldsInput->_method == 'validate_update' ) {
                return $this->_username;
            }

            //            else {
            //                Defender::setErrorText( 'user_name', $locale['u122'] );
            //                Defender::setInputError( 'user_name' );
            //            }
        }

        return '';
    }

    /**
     * Update email changes
     */
    public function setUserEmailChange() {

        if ( $new_email = sanitizer( 'user_email', '', 'user_email' ) ) {

            $userdata = fusion_get_userdata();

            $locale = fusion_get_locale();

            $settings = fusion_get_settings();

            if ( $userdata['user_email'] != $new_email ) {

                if ( $validation_code = sanitizer( 'email_code', '', 'email_code' ) ) {

                    $emailer = ( new EmailAuth() );

                    $emailer->setCode( $validation_code );

                    if ( $emailer->verifyCode() === TRUE ) {

                        // Require a valid email account
                        if ( dbcount( "(blacklist_id)", DB_BLACKLIST, ":email like replace(if (blacklist_email like '%@%' or blacklist_email like '%\\%%', blacklist_email, concat('%@', blacklist_email)), '_', '\\_')", [':email' => $new_email] ) ) {
                            // this email blacklisted.
                            fusion_stop();
                            \Defender::setInputError( 'user_email' );
                            \Defender::setErrorText( 'user_email', $locale['u124'] );

                            addnotice( 'danger', 'This email has been blacklisted. Please use another email address.' );

                        } else {

                            $email_active = dbcount( "(user_id)", DB_USERS, "user_email=:email", [':email' => $new_email] );

                            $email_inactive = dbcount( "(user_code)", DB_NEW_USERS, "user_email=:email", [':email' => $new_email] );

                            if ( $email_active == 0 && $email_inactive == 0 ) {

                                if ( fusion_safe() ) {

                                    require_once INCLUDES . 'sendmail_include.php';

                                    $userCode = hash_hmac( "sha1", PasswordAuth::getNewPassword(), $userdata['user_email'] );

                                    $activationUrl = $settings['siteurl'] . "register.php?email=" . $userdata['user_email'] . "&code=" . $userCode;

                                    fusion_sendmail( 'E_CHANGE', $userdata['user_name'], $userdata['user_email'], [
                                        'subject' => $locale['email_change_subject'],
                                        'message' => $locale['email_change_message'],
                                        'replace' => [
                                            '[EMAIL_VERIFY_LINK]' => $activationUrl
                                        ]
                                    ] );
                                    // template has problems
                                    // this method is unreliable in localhost servers or webservers to produce accurate notice
                                    //if ( $sendmail === FALSE ) {
                                    //addnotice( 'info', $locale['u153'] . "\n" . $locale['u154'] );
                                    //}

                                    $email_rows = [
                                        'user_code'      => $userCode,
                                        'user_name'      => $userdata['user_name'],
                                        'user_email'     => $userdata['user_email'],
                                        'user_datestamp' => time(),
                                        'user_info'      => base64_encode( serialize( [
                                                'user_name' => $userdata['user_name'],
                                                'user_hash' => $userdata['user_password'],
                                                'user_id'   => $userdata['user_id']
                                            ] )
                                        )
                                    ];

                                    dbquery_insert( DB_NEW_USERS, $email_rows, 'save', ['primary_key' => 'user_name', 'no_unique' => TRUE] );
                                    // Log email change
                                    save_user_log( $userdata['user_id'], 'user_email', $new_email, $userdata['user_email'] );

                                    $emailer->reset();

                                    addnotice( 'success', $locale['u153c'] . "\n" . $locale['u153d'] );

                                    redirect( FUSION_SELF );

                                }

                            } else {
                                // email taken
                                fusion_stop();
                                \Defender::setInputError( 'user_email' );
                                \Defender::setErrorText( 'user_email', $locale['u125'] );
                                addnotice( 'danger', 'Email has not been updated' );

                            }
                        }

                    } else {
                        addnotice( 'danger', 'No change applied to account email.' );
                    }

                } else {
                    addnotice( 'danger', 'No change applied to your account email.' );
                }
            } else {
                addnotice( 'danger', 'No change applied to your account email.' );
            }
        }
    }

    /**
     * Update TOTP
     */
    public function setUserTOTP() {

        if ( $this->userFieldsInput->userData['user_totp'] ) {
            if ( $validation_code = sanitizer( 'email_code', '', 'email_code' ) ) {
                $email_auth = ( new EmailAuth() );
                $email_auth->setCode( $validation_code );
                if ( $email_auth->verifyCode() === TRUE ) {
                    dbquery( "UPDATE " . DB_USERS . " SET user_totp=:secret WHERE user_id=:uid", [
                        ':secret' => '',
                        ':uid'    => $this->userFieldsInput->userData['user_id']
                    ] );

                    $email_auth->reset();

                    addnotice( 'success', "Authentication has been unbound successfully.\nYour account is two factor authentication has been deactivated." );

                    redirect( FUSION_SELF );

                } else {

                    $response = $email_auth->getResponse();

                    if ( !empty( $response['title'] ) && !empty( $response['text'] ) ) {
                        addnotice( 'danger', $response['title'] . "\n" . $response['text'] );
                    }
                }
            }
        }


        if ( $code = sanitizer( 'totp_code', '', 'totp_code' ) ) {

            if ( $validation_code = sanitizer( 'email_code', '', 'email_code' ) ) {

                $email_auth = ( new EmailAuth() );

                $email_auth->setCode( $validation_code );

                if ( $email_auth->verifyCode() === TRUE ) {

                    $key = sanitizer( 'user_totp', '', 'user_totp' );

                    if ( fusion_safe() ) {

                        // now check code
                        $g = new GoogleAuthenticator();

                        if ( $g->checkCode( $key, $code ) ) {

                            $email_auth->reset();

                            dbquery( "UPDATE " . DB_USERS . " SET user_totp=:secret WHERE user_id=:uid", [
                                ':secret' => $key,
                                ':uid'    => $this->userFieldsInput->userData['user_id']
                            ] );

                            unset( $_SESSION['totp_secret'] );

                            addnotice( 'success', "Two factor authentication method has been activated successfully.\nYour account is now protected with a two factor authentication security measure." );

                            redirect( FUSION_SELF );
                        } else {

                            addnotice( 'danger', "The authenticator code is invalid or has expired.\nPlease try again with a new code." );
                        }
                    }
                } else {

                    if ( fusion_safe() ) {
                        addnotice( 'danger', "Two factor authenticator not bound.\nThe email security code provided is invalid." );
                    }
                }
            }
        }
    }

    /**
     * Email
     */
    public function setUserEmail() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        // has email posted
        $this->_userEmail = sanitizer( 'user_email', '', 'user_email' );

        if ( $this->_userEmail != $this->userFieldsInput->userData['user_email'] ) {

            /**
             * Checks for valid password requirements
             */
            // Password to change email address
            if ( $this->userFieldsInput->moderation && ( iADMIN && checkrights( 'M' ) ) ) {
                // Skips checking password
                $this->_isValidCurrentPassword = TRUE; // changing an email in administration panel

            } else if ( $this->userFieldsInput->_method == 'validate_update' ) {
                // For admin
                // Check password
                if ( $_userPassword = self::getPasswordInput( 'user_hash' ) ) {
                    /**
                     * Validation of Password
                     */
                    $passAuth = new PasswordAuth();
                    $passAuth->inputPassword = $_userPassword;
                    $passAuth->currentAlgo = $this->userFieldsInput->userData['user_algo'];
                    $passAuth->currentSalt = $this->userFieldsInput->userData['user_salt'];
                    $passAuth->currentPasswordHash = $this->userFieldsInput->userData['user_password'];

                    $passAuth->currentPassCheckLength = $settings['password_length'];
                    $passAuth->currentPassCheckSpecialchar = $settings['password_char'];
                    $passAuth->currentPassCheckNum = $settings['password_num'];;
                    $passAuth->currentPassCheckCase = $settings['password_case'];

                    if ( $passAuth->isValidCurrentPassword() ) {
                        $this->_isValidCurrentPassword = TRUE;
                    } else {
                        fusion_stop( $passAuth->getError() );
                        Defender::setInputError( 'user_email' );
                        Defender::setErrorText( 'user_email', $passAuth->getError() );
                    }
                }
            }

            if ( $this->_isValidCurrentPassword || $this->userFieldsInput->_method == 'validate_insert' ) {

                // Require a valid email account
                if ( dbcount( "(blacklist_id)", DB_BLACKLIST, ":email like replace(if (blacklist_email like '%@%' or blacklist_email like '%\\%%', blacklist_email, concat('%@', blacklist_email)), '_', '\\_')", [':email' => $this->_userEmail] ) ) {
                    // this email blacklisted.
                    fusion_stop();


                    Defender::setInputError( 'user_email' );
                    Defender::setErrorText( 'user_email', $locale['u124'] );

                } else {

                    $email_active = dbcount( "(user_id)", DB_USERS, "user_email=:email", [':email' => $this->_userEmail] );
                    $email_inactive = dbcount( "(user_code)", DB_NEW_USERS, "user_email=:email", [':email' => $this->_userEmail] );

                    if ( $email_active == 0 && $email_inactive == 0 ) {

                        if ( $this->userFieldsInput->emailVerification && !$this->userFieldsInput->_method == 'validate_update' && !iSUPERADMIN ) {

                            $this->userFieldsInput->verifyNewEmail();

                        } else {

                            return $this->_userEmail;
                        }

                    } else {
                        // email taken
                        fusion_stop();
                        Defender::setInputError( 'user_email' );
                        Defender::setErrorText( 'user_email', $locale['u125'] );
                    }
                }

            } else {
                // must have a valid password to change email
                fusion_stop();
                addnotice( 'danger', $locale['u156'] );
                Defender::setInputError( 'user_email' );
                Defender::setErrorText( 'user_email', $locale['u149'] );
            }
        }

        return $this->_userEmail;
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function getPasswordInput( $field ) {
        return isset( $_POST[$field] ) && $_POST[$field] != "" ? $_POST[$field] : '';
    }

    /**
     * Handle User Password Input and Validation
     *
     * @return array|false
     */
    public function setUserPassword() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $user_password = self::getPasswordInput( 'user_password' );

        $passAuth = new PasswordAuth();
        $passAuth->currentPassCheckLength = $settings['password_length'];
        $passAuth->currentPassCheckSpecialchar = $settings['password_char'];
        $passAuth->currentPassCheckNum = $settings['password_num'];;
        $passAuth->currentPassCheckCase = $settings['password_case'];

        if ( $this->userFieldsInput->_method == 'validate_insert' ) {

            if ( !empty( $user_password ) ) {

                $passAuth->inputNewPassword = $user_password;
                $passAuth->inputNewPassword2 = $user_password;

                if ( $passAuth->checkInputPassword( $user_password ) ) {

                    switch ( $passAuth->isValidNewPassword() ) {
                        case '0':
                            // New password is valid
                            $_newUserPasswordHash = $passAuth->getNewHash();
                            $_newUserPasswordAlgo = $passAuth->getNewAlgo();
                            $_newUserPasswordSalt = $passAuth->getNewSalt();

                            $this->_isValidCurrentPassword = 1;

                            if ( !$this->userFieldsInput->moderation && !$this->userFieldsInput->skipCurrentPass ) {

                                Authenticate::setUserCookie( $this->userFieldsInput->userData['user_id'], $passAuth->getNewSalt(), $passAuth->getNewAlgo() );
                            }

                            return [$_newUserPasswordAlgo, $_newUserPasswordSalt, $_newUserPasswordHash];

                        case '1':
                            // New Password equal old password
                            fusion_stop();
                            Defender::setInputError( 'user_password2' );
                            Defender::setInputError( 'user_password2' );
                            Defender::setErrorText( 'user_password', $locale['u134'] . $locale['u146'] . $locale['u133'] );
                            Defender::setErrorText( 'user_password2', $locale['u134'] . $locale['u146'] . $locale['u133'] );
                            break;
                        case '2':
                            // The two new passwords are not identical
                            fusion_stop();
                            Defender::setInputError( 'user_password1' );
                            Defender::setInputError( 'user_password2' );
                            Defender::setErrorText( 'user_password1', $locale['u148'] );
                            Defender::setErrorText( 'user_password2', $locale['u148'] );
                            break;
                        case '3':
                            // New password contains invalid chars / symbols
                            fusion_stop();
                            Defender::setInputError( 'user_password1' );
                            Defender::setErrorText( 'user_password1', $locale['u134'] . $locale['u142'] . "<br />" . $locale['u147'] );
                            break;
                    }
                } else {
                    fusion_stop();
                    set_input_error( 'user_password', $passAuth->getError() );
                }
            } else {
                fusion_stop( $locale['u134'] . $locale['u143a'] );
            }

        } else if ( $this->userFieldsInput->_method == 'validate_update' ) {

            if ( $validation_code = sanitizer( 'email_code', '', 'email_code' ) ) {

                $email_auth = new EmailAuth();

                $email_auth->setCode( $validation_code );

                if ( $email_auth->verifyCode() === TRUE ) {

                    $password_1 = self::getPasswordInput( 'user_password1' );
                    $password_2 = self::getPasswordInput( 'user_password2' );

                    if ( $this->userFieldsInput->moderation or $user_password or $password_1 or $password_2 ) {

                        /**
                         * Validation of Password
                         */
                        $passAuth->inputPassword = $user_password;
                        $passAuth->inputNewPassword = $password_1;
                        $passAuth->inputNewPassword2 = $password_2;

                        $passAuth->currentPasswordHash = $this->userFieldsInput->userData['user_password'];
                        $passAuth->currentAlgo = $this->userFieldsInput->userData['user_algo'];
                        $passAuth->currentSalt = $this->userFieldsInput->userData['user_salt'];

                        if ( $passAuth->checkInputPassword( $password_1 ) ) {

                            if ( $this->userFieldsInput->moderation or $passAuth->isValidCurrentPassword() ) {

                                $res = $passAuth->isValidNewPassword();

                                switch ( $res ) {
                                    case '0':

                                        // New password is valid
                                        $_newUserPasswordHash = $passAuth->getNewHash();
                                        $_newUserPasswordAlgo = $passAuth->getNewAlgo();
                                        $_newUserPasswordSalt = $passAuth->getNewSalt();

                                        //Update DB
                                        dbquery( "UPDATE " . DB_USERS . " SET user_password=:p1, user_algo=:p2, user_salt=:p3 WHERE user_id=:uid", [
                                            ':uid' => $this->userFieldsInput->userData['user_id'],
                                            ':p1'  => $_newUserPasswordHash,
                                            ':p2'  => $_newUserPasswordAlgo,
                                            ':p3'  => $_newUserPasswordSalt,
                                        ] );

                                        addnotice( 'success', "Password has been changed successfuly.\n As a security measure please log in again with your new password." );

                                        require_once INCLUDES . 'sendmail_include.php';
                                        // Send Email
                                        fusion_sendmail( 'U_PASS', display_name( $this->userFieldsInput->userData ), $this->userFieldsInput->userData['user_email'], [
                                            'subject' => $locale['email_passchange_subject'],
                                            'message' => $locale['email_passchange_message'],
                                            'replace' => [
                                                '[PASSWORD]' => $user_password
                                            ]
                                        ] );

                                        // Reset cookie for current session and logs out user
                                        if ( !$this->userFieldsInput->moderation && !$this->userFieldsInput->skipCurrentPass ) {
                                            Authenticate::setUserCookie( $this->userFieldsInput->userData['user_id'], $_newUserPasswordSalt, $_newUserPasswordAlgo );
                                            Authenticate::logOut();
                                            redirect( FUSION_SELF );
                                        }

                                    case '1':
                                        // New Password equal old password
                                        fusion_stop();
                                        set_input_error( 'user_password', $locale['u134'] . $locale['u146'] . $locale['u133'] );
                                        set_input_error( 'user_password1', $locale['u134'] . $locale['u146'] . $locale['u133'] );

                                        break;

                                    case '2':
                                        // The two new passwords are not identical
                                        fusion_stop();
                                        set_input_error( 'user_password1', $locale['u148'] );
                                        set_input_error( 'user_password2', $locale['u148'] );

                                        break;
                                    case '3':
                                        // New password contains invalid chars / symbols
                                        fusion_stop();
                                        set_input_error( 'user_password1', $locale['u134'] . $locale['u142'] . "\n" . $locale['u147'] );
                                        break;
                                }

                            } else {
                                fusion_stop();
                                set_input_error( 'user_password', $locale['u149'] );
                            }

                        } else {
                            fusion_stop();
                            set_input_error( 'user_password1', $passAuth->getError() );
                        }
                    }

                } else {

                    $response = $email_auth->getResponse();
                    if ( isset( $response['title'] ) && isset( $response['text'] ) ) {
                        addnotice( 'danger', $response['title'] . "\n" . $response['text'] );
                    }
                }
            }
        }

        return FALSE;
    }

    /**
     * Set admin password
     *
     * @return array
     */
    public function setAdminPassword() {

        if ( !$this->userFieldsInput->moderation ) {

            $locale = fusion_get_locale();
            $settings = fusion_get_settings();

            if ( self::getPasswordInput( 'user_admin_password' ) ) { // if submit current admin password

                if ( $validation_code = sanitizer( 'email_code', '', 'email_code' ) ) {

                    $email_auth = new EmailAuth();

                    $email_auth->setCode( $validation_code );

                    if ( $email_auth->verifyCode() === TRUE ) {

                        $_userAdminPassword = self::getPasswordInput( "user_admin_password" );      // var1
                        $_newUserAdminPassword = self::getPasswordInput( "user_admin_password1" );  // var2
                        $_newUserAdminPassword2 = self::getPasswordInput( "user_admin_password2" ); // var3

                        $adminpassAuth = new PasswordAuth();
                        $adminpassAuth->currentPassCheckLength = $settings['password_length'];
                        $adminpassAuth->currentPassCheckSpecialchar = $settings['password_char'];
                        $adminpassAuth->currentPassCheckNum = $settings['password_num'];;
                        $adminpassAuth->currentPassCheckCase = $settings['password_case'];


                        if ( !$this->userFieldsInput->userData['user_admin_password'] && !$this->userFieldsInput->userData['user_admin_salt'] ) {
                            // New Admin
                            $adminpassAuth->inputPassword = 'fake';
                            $adminpassAuth->inputNewPassword = $_userAdminPassword;
                            $adminpassAuth->inputNewPassword2 = $_newUserAdminPassword2;
                            $valid_current_password = TRUE;

                        } else {

                            // Old Admin changing password
                            $adminpassAuth->inputPassword = $_userAdminPassword;         // var1
                            $adminpassAuth->inputNewPassword = $_newUserAdminPassword;   // var2
                            $adminpassAuth->inputNewPassword2 = $_newUserAdminPassword2; // var3
                            $adminpassAuth->currentPasswordHash = $this->userFieldsInput->userData['user_admin_password'];
                            $adminpassAuth->currentAlgo = $this->userFieldsInput->userData['user_admin_algo'];
                            $adminpassAuth->currentSalt = $this->userFieldsInput->userData['user_admin_salt'];

                            $valid_current_password = $adminpassAuth->isValidCurrentPassword();
                        }

                        if ( $valid_current_password ) {

                            //$_isValidCurrentAdminPassword = 1;

                            // authenticated. now do the integrity check
                            $_isValidNewPassword = $adminpassAuth->isValidNewPassword();

                            switch ( $_isValidNewPassword ) {
                                case '0':
                                    // New password is valid
                                    $new_admin_password = $adminpassAuth->getNewHash();
                                    $new_admin_salt = $adminpassAuth->getNewSalt();
                                    $new_admin_algo = $adminpassAuth->getNewAlgo();

                                    return [$new_admin_algo, $new_admin_salt, $new_admin_password];

                                case '1':
                                    // new password is old password
                                    fusion_stop();
                                    Defender::setInputError( 'user_admin_password' );
                                    Defender::setInputError( 'user_admin_password1' );
                                    Defender::setErrorText( 'user_admin_password', $locale['u144'] . $locale['u146'] . $locale['u133'] );
                                    Defender::setErrorText( 'user_admin_password1', $locale['u144'] . $locale['u146'] . $locale['u133'] );
                                    break;
                                case '2':
                                    // The two new passwords are not identical
                                    fusion_stop();
                                    Defender::setInputError( 'user_admin_password1' );
                                    Defender::setInputError( 'user_admin_password2' );
                                    Defender::setErrorText( 'user_admin_password1', $locale['u144'] . $locale['u148a'] );
                                    Defender::setErrorText( 'user_admin_password2', $locale['u144'] . $locale['u148a'] );
                                    break;
                                case '3':
                                    // New password contains invalid chars / symbols
                                    fusion_stop();
                                    Defender::setInputError( 'user_admin_password1' );
                                    Defender::setErrorText( 'user_admin_password1', $locale['u144'] . $locale['u142'] . "<br />" . $locale['u147'] );
                                    break;
                            }
                        } else {
                            fusion_stop();
                            Defender::setInputError( 'user_admin_password' );
                            Defender::setErrorText( 'user_admin_password', $locale['u149a'] );
                        }
                    } else {
                        $response = $email_auth->getResponse();
                        if ( isset( $response['title'] ) && isset( $response['text'] ) ) {
                            addnotice( 'danger', $response['title'] . "\n" . $response['text'] );
                        }
                    }
                }

            } else {

                // check db only - admin cannot save profile page without password

                if ( iADMIN ) {
                    $require_valid_password = $this->userFieldsInput->userData['user_admin_password'];

                    if ( !$require_valid_password ) {
                        // 149 for admin
                        fusion_stop();
                        Defender::setInputError( 'user_admin_password' );
                        Defender::setErrorText( 'user_admin_password', $locale['u149a'] );
                    }
                }
            }
        }
        return [];
    }

    public function setAccountProfile() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $this->_data['user_id'] = $this->userFieldsInput->userData['user_id'];
        $this->_data['user_name'] = $this->setUserName();
        $this->_data['user_firstname'] = sanitizer( 'user_firstname', '', 'user_firstname' );
        $this->_data['user_lastname'] = sanitizer( 'user_lastname', '', 'user_lastname' );
        $this->_data['user_addname'] = sanitizer( 'user_addname', '', 'user_addname' );
        $this->_data['user_displayname'] = sanitizer( 'user_displayname', '0', 'user_displayname' );
        $this->_data['user_phonecode'] = sanitizer( 'user_phonecode', '', 'user_phonecode' );
        $this->_data['user_phone'] = sanitizer( 'user_phone', '', 'user_phone' );
        $this->_data['user_bio'] = sanitizer( 'user_bio', '', 'user_bio' );

        if ( $_input = $this->setCustomUserFields() ) {
            foreach ( $_input as $input_values ) {
                $this->_data += $input_values;
            }
        }

        // id request spoofing request
        if ( $this->userFieldsInput->checkUpdateAccess() ) {

            // Log username change
            if ( $settings['username_change'] && $this->_data['user_name'] !== $this->userFieldsInput->userData['user_name'] ) {
                save_user_log( $this->userFieldsInput->userData['user_id'], 'user_name', $this->_data['user_name'], $this->userFieldsInput->userData['user_name'] );
            }

            // Log all custom field changes
            $this->_quantum->logUserAction( DB_USERS, 'user_id' );

            // Update Table
            dbquery_insert( DB_USERS, $this->_data, 'update' );

            //dbquery_insert( DB_USER_SETTINGS, $this->data, 'update', ['primary_key' => 'user_id'] );
            addnotice( 'success', $locale['u163'] . "\nYour personal information has been successfully updated." );

            redirect( BASEDIR . 'edit_profile.php' );

        } else {

            fusion_stop();
            addnotice( 'danger', $locale['error_request'] );
        }
    }


    public function createAccount() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $this->_data = $this->userFieldsInput->setEmptyFields();

        $this->_data['user_name'] = $this->setUserName();

        if ( $pass = $this->setUserPassword() ) {
            if ( count( $pass ) === 3 ) {
                [$this->_data['user_algo'], $this->_data['user_salt'], $this->_data['user_password']] = $pass;
            }
        }

        $this->_data['user_email'] = $this->setUserEmail();

        if ( $_input = $this->setCustomUserFields() ) {
            foreach ( $_input as $input ) {
                $this->_data += $input;
            }
        }

        // we need a validation page
        //if ( $this->validation == 1 ) {
        //    $this->verifyCaptchas();
        //}
        //        print_p( $this->userData );
        //        print_p( 'Email verify: ' . $this->emailVerification );
        //        print_p( 'Admin verify: ' . $this->adminActivation );
        //        print_p( $this->data );
        if ( fusion_safe() ) {
            if ( $settings['email_verification'] ) {

                $this->userFieldsInput->sendEmailVerification();

            } else {

                $insert_id = dbquery_insert( DB_USERS, $this->_data, 'save' );

                dbquery_insert( DB_USER_SETTINGS, $this->userFieldsInput->setEmptySettingsField( $insert_id ), 'save', ['no_unique' => TRUE, 'primary_key' => 'user_id'] );

                /**
                 * Create user
                 */
                $notice = $locale['u160'] . " - " . $locale['u161'];
                //
                if ( $this->userFieldsInput->moderation == 1 ) {

                    $this->userFieldsInput->sendAdminRegistrationMail();

                } else {
                    // got admin activation and not
                    if ( $settings['admin_activation'] ) {
                        // Missing registration data?
                        $notice = $locale['u160'] . " - " . $locale['u162'];
                    }
                }

                addnotice( 'success', $notice, $settings['opening_page'] );
            }

            redirect( BASEDIR . $settings['opening_page'] );
        }
    }

    /**
     * @return array
     */
    public function setCustomUserFields() {

        $this->_quantum = new QuantumFields();
        $this->_quantum->setFieldDb( DB_USER_FIELDS );
        $this->_quantum->loadFields();
        $this->_quantum->loadFieldCats();
        $this->_quantum->setCallbackData( $this->_data );

        return $this->_quantum->returnFieldsInput( DB_USERS, 'user_id' );
    }

    public function setAccountPrivacy() {

        $rows = [
            'user_id'             => $this->userFieldsInput->userData['user_id'],
            'user_hide_phone'     => sanitizer( 'user_hide_phone', '0', 'user_hide_phone' ),
            'user_hide_email'     => sanitizer( 'user_hide_email', '0', 'user_hide_email' ),
            'user_hide_location'  => sanitizer( 'user_hide_location', '0', 'user_hide_location' ),
            'user_hide_birthdate' => sanitizer( 'user_hide_birthdate', '0', 'user_hide_birthdate' ),
        ];

        if ( fusion_safe() ) {

            dbquery_insert( DB_USER_SETTINGS, $rows, 'update' );
            addnotice( 'success', "Account profile updated.\nPrivacy settings has been updated successfully." );
            redirect( BASEDIR . 'edit_profile.php?ref=privacy' );
        }
    }

    public function setAccountMesssaging() {
        $locale = fusion_get_locale();

        $rows = [
            'user_id'           => $this->userFieldsInput->userData['user_id'],
            'user_pm_email'     => sanitizer( 'user_pm_email', '', 'user_pm_email' ),
            'user_pm_save_sent' => sanitizer( 'user_pm_save_sent', '', 'user_pm_save_sent' )
        ];
        if ( fusion_safe() ) {

            dbquery_insert( DB_USER_SETTINGS, $rows, 'update' );
            addnotice( 'success', "Account profile updated.\n" . $locale['u611'] );

            redirect( BASEDIR . 'edit_profile.php?ref=pm_options' );
        }
    }
}
