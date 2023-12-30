<?php
namespace PHPFusion;

class EmailAuth {

    private $_code;
    private $_response;

    public function setCode( $value ) {
        $this->_code = $value;
    }

    public function setEmail( $value ) {
        $this->_email = $value;
    }

    /***
     * @return int|bool
     */
    public function verifyCode() {

        if ( iMEMBER ) {
            $userdata = fusion_get_userdata();
            if ( $this->_code == $userdata['user_auth_pin'] ) {
                if ( time() <= $userdata['user_auth_actiontime'] ) {
                    return TRUE;
                } else {
                    // Expired
                    $this->_response = 301;
                }
            } else {
                // Incorrect
                $this->_response = 302;
            }
        }

        return FALSE;
    }


    /**
     * Resets Emailer upon Success
     *
     * @return bool
     */
    public function reset() {

        if ( iMEMBER ) {
            $userdata = fusion_get_userdata();
            // Remove posisble auth
            dbquery( "UPDATE " . DB_USERS . " SET user_auth_pin='', user_auth_actiontime='' WHERE user_id=:uid", [':uid' => $userdata['user_id']] );
            // Remove session timer
            unset( $_SESSION['new_otp_time'] );
            return TRUE;
        }

        return FALSE;
    }

    public function sendCode() {

        $settings = fusion_get_settings();

        if ( iMEMBER ) {
            $userdata = fusion_get_userdata();

            $locale = fusion_get_locale( '', [LOCALE . LOCALESET . 'admin/members_email.php'] );

            if ( $userdata['user_auth_actiontime'] >= time() && isset( $_SESSION['new_otp_time'] ) && $_SESSION['new_otp_time'] <= time() ) {

                // Resend code within 30 seconds
                $_SESSION['new_otp_time'] = time() + 30;

                require_once INCLUDES . 'sendmail_include.php';

                fusion_sendmail( 'E_AUTH', $userdata['user_name'], $userdata['user_email'], [
                    'subject' => $locale['email_auth_subject'],
                    'message' => $locale['email_auth_message'],
                    'replace' => [
                        '[SITENAME]' => $settings['sitename'],
                        '[OTP]'      => $userdata['user_auth_pin']
                    ]
                ] );

                $this->_response = 200;

            } else if ( $userdata['user_auth_actiontime'] <= time() ) {

                $_SESSION['new_otp_time'] = time() + 30;

                $random_pin = Authenticate::generateOTP( $settings['auth_login_length'] );

                $auth_actiontime = time() + $settings['auth_login_expiry'];

                dbquery( "UPDATE " . DB_USERS . " SET user_auth_pin=:pin, user_auth_actiontime=:time WHERE user_id=:uid", [":pin" => $random_pin, ":time" => $auth_actiontime, ':uid' => $userdata['user_id']] );

                require_once INCLUDES . 'sendmail_include.php';

                fusion_sendmail( 'E_AUTH', $userdata['user_name'], $userdata['user_email'], [
                    'subject' => $locale['email_auth_subject'],
                    'message' => $locale['email_auth_message'],
                    'replace' => [
                        '[SITENAME]' => $settings['sitename'],
                        '[OTP]'      => $random_pin
                    ]
                ] );

                $this->_response = 200;
            } else {
                $this->_response = 202;
            }

        } else {

            $this->_response = 203;
        }
    }


    /**
     * @return array
     */
    public function getResponse() {

        switch ( $this->_response ) {
            case 300:
                return [
                    'response' => 300,
                    'title'    => 'Email verification successful',
                    'text'     => 'The email verification is successful',
                ];
            case 301:
                return [
                    'response' => 301,
                    'title'    => 'Email verification code has expired',
                    'text'     => 'The email verification code has already expired',
                ];
            case 302:
                return [
                    'response' => 302,
                    'title'    => 'Email verification code is incorrect',
                    'text'     => 'The email verification code is invalid',
                ];
            case 200:
                return [
                    'response' => 200,
                    'title'    => 'Authorization code has been sent to your registered email address.',
                    'text'     => 'We have sent an authorization passcode to your current registered email address for the authentication. Please check your spam folder if the email is still missing from your inbox.'];

            case 202:
                return ['response' => 202,
                        'title'    => 'Error sending email verification code.',
                        'text'     => 'You cannot request for another authorization pin code until the time has expired'
                ];
            case 203:
                return [
                    'response' => 203,
                    'title'    => 'Illegal access.',
                    'text'     => 'Actions could not be performed due to illegal access.'];
            default:

        }

    }

}
