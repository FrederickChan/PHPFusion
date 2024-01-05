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

use PHPFusion\Userfields\Notifications\NotificationsValidate;
use PHPFusion\Userfields\UserFieldsValidate;

class AccountsValidate extends UserFieldsValidate {

    /**
     * Account creation
     */
    public function createAccount() {

        $settings = fusion_get_settings();

        if ( check_get( 'validation' ) ) {
            if ( $this->accountCaptchas()->validate() ) {
                $this->_data = unserialize( base64_decode( $_SESSION['account_data'] ) );
            }
        } else {

            // register without validation
            $this->_data = $this->userFieldsInput->setEmptyFields();
            $this->_data['user_name'] = $this->accountUsername()->verifyUserName();

            if ( $pass = $this->accountPassword()->setUserPassword() ) {
                if ( count( $pass ) === 3 ) {
                    [$this->_data['user_algo'], $this->_data['user_salt'], $this->_data['user_password']] = $pass;
                }
            }

            $this->_data['user_email'] = $this->accountEmail()->setUserEmail();

            // Can optimize?
            if ( $_input = $this->accountProfile()->setCustomUserFields() ) {
                $custom_field = [];
                foreach ( $_input as $input ) {
                    $custom_field[] = $input;
                }
                $this->_data['user_custom'] = implode( '', $custom_field );
            }

            if ( $settings['display_validation'] ) {
                $_SESSION['account_data'] = base64_encode( serialize( $this->_data ) );
                redirect( clean_request( 'validation=true', ['validation'], FALSE ) );
            }
        }

        if ( !empty( $this->_data ) ) {

            $settings['email_verification'] ? $this->accountUpdate()->sendEmailVerification( $this->_data ) : $this->accountUpdate()->createAccount( $this->_data );

            redirect( BASEDIR . $settings['opening_page'] );
        }

    }

    public function updateAccount() {

        switch ( get( 'section' ) ) {
            case 'notifications':
                ( new NotificationsValidate( $this->userFieldsInput ) )->validate();
                break;
            //case 'privacy':
            //( new PrivacyValidate( $this ) )->validate();
            //break;
            case 'close':
                $this->accountClose()->validate();
                break;
            default:

                if ( check_post( 'user_email_submit' ) ) {

                    $this->accountEmail()->setUserEmailChange();

                } else if ( check_post( 'user_totp_submit' ) ) {

                    $this->accountTwoFactor()->validate();

                } else if ( check_post( 'user_pass_submit' ) ) {

                    $this->accountPassword()->setUserPassword();

                } else if ( check_post( 'user_adminpass_submit' ) ) {

                    $this->accountPassword()->setAdminPassword();

                } else if ( check_post( 'user_privacy_submit' ) ) {

                    $this->accountPrivacy()->setAccountPrivacy();

                } else if ( check_post( 'user_pm_submit' ) ) {

                    $this->accountMessaging()->setAccountMesssaging();

                } else if ( check_post( 'update_profile_btn' ) ) {

                    $this->accountProfile()->setAccountProfile();
                }
        }

    }
}
