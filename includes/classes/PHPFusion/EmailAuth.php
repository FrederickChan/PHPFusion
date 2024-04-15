<?php

namespace PHPFusion;

class EmailAuth {

    private $_code;
    private $_response;

    public function setCode($value) {
        $this->_code = $value;
    }

    public function setEmail($value) {
        $this->_email = $value;
    }

    /***
     * @return int|bool
     */
    public function verifyCode() {

        if (iMEMBER) {

            if ($this->_code == $_SESSION['auth_pin']) {
                if (time() <= $_SESSION['user_auth_actiontime']) {
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

        if (iMEMBER) {
            // Remove session timer
            unset($_SESSION['new_otp_time']);
            unset($_SESSION['auth_pin']);
            unset($_SESSION['user_auth_actiontime']);
            return TRUE;
        }

        return FALSE;
    }

    public function sendCode() {

        $settings = fusion_get_settings();

        if (iMEMBER) {
            $userdata = fusion_get_userdata();

            $locale = fusion_get_locale('', [LOCALE . LOCALESET . 'admin/members_email.php']);

            if ((empty($_SESSION['user_auth_actiontime']) && empty($_SESSION['new_otp_time'])) ||
                (isset($_SESSION['user_auth_actiontime']) && $_SESSION['user_auth_actiontime'] >= time()) &&
                isset($_SESSION['new_otp_time']) && $_SESSION['new_otp_time'] <= time()) {

                // Resend code within 30 seconds
                $_SESSION['new_otp_time'] = time() + 30;

                require_once INCLUDES . 'sendmail_include.php';

                fusion_sendmail('E_AUTH', $userdata['user_name'], $userdata['user_email'], [
                    'subject' => $locale['email_auth_subject'],
                    'message' => $locale['email_auth_message'],
                    'replace' => [
                        '[SITENAME]' => $settings['sitename'],
                        '[OTP]' => $_SESSION['auth_pin'],
                    ],
                ]);

                $this->_response = 200;

            } else if (!empty($_SESSION['user_auth_actiontime']) && $_SESSION['user_auth_actiontime'] <= time()) {

                $_SESSION['new_otp_time'] = time() + 30;

                $_SESSION['auth_pin'] = Authenticate::generateOTP($settings['auth_login_length']);
                $_SESSION['auth_actiontime'] = time() + $settings['auth_login_expiry'];

                require_once INCLUDES . 'sendmail_include.php';

                fusion_sendmail('E_AUTH', $userdata['user_name'], $userdata['user_email'], [
                    'subject' => $locale['email_auth_subject'],
                    'message' => $locale['email_auth_message'],
                    'replace' => [
                        '[SITENAME]' => $settings['sitename'],
                        '[OTP]' => $_SESSION['auth_pin'],
                    ],
                ]);

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

        switch ($this->_response) {
            case 300:
                return [
                    'response' => 300,
                    'title' => 'Email verification successful',
                    'text' => 'The email verification is successful',
                ];
            case 301:
                return [
                    'response' => 301,
                    'title' => 'Email verification code has expired',
                    'text' => 'The email verification code has already expired',
                ];
            case 302:
                return [
                    'response' => 302,
                    'title' => 'Email verification code is incorrect',
                    'text' => 'The email verification code is invalid',
                ];
            case 200:
                return [
                    'response' => 200,
                    'title' => 'Authorization code has been sent to your registered email address.',
                    'text' => 'We have sent an authorization passcode to your current registered email address for the authentication. Please check your spam folder if the email is still missing from your inbox.'];

            case 202:
                return ['response' => 202,
                    'title' => 'Error sending email verification code.',
                    'text' => 'You cannot request for another authorization pin code until the time has expired',
                ];
            case 203:
                return [
                    'response' => 203,
                    'title' => 'Illegal access.',
                    'text' => 'Actions could not be performed due to illegal access.'];
            default:

        }

    }

}
