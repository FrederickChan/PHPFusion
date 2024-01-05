<?php
namespace PHPFusion\Userfields\Accounts\Validation;

use PHPFusion\EmailAuth;
use PHPFusion\PasswordAuth;
use PHPFusion\Userfields\UserFieldsValidate;

class AccountEmail extends UserFieldsValidate {

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

}
