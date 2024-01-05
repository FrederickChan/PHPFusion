<?php
namespace PHPFusion\Userfields\Accounts\Validation;

use PHPFusion\Authenticate;
use PHPFusion\EmailAuth;
use PHPFusion\PasswordAuth;
use PHPFusion\Userfields\UserFieldsValidate;

class AccountPassword  extends UserFieldsValidate {

    private int $_isValidCurrentPassword;

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

    /**
     * @param string $field
     *
     * @return string
     */
    private function getPasswordInput( $field ) {
        return isset( $_POST[$field] ) && $_POST[$field] != "" ? $_POST[$field] : '';
    }

}
