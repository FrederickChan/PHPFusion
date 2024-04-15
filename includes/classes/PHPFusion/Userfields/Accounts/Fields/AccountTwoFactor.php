<?php
namespace PHPFusion\Userfields\Accounts\Fields;

use Google\Authenticator\GoogleAuthenticator;
use Google\Authenticator\GoogleQrUrl;
use PHPFusion\Userfields\UserFieldsForm;

/**
 * Class AccountTwoFactor
 * @package PHPFusion\Userfields\Accounts\Fields
 */
class AccountTwoFactor extends UserFieldsForm {

    /**
     * @return array
     */
    public function profileTOTPField() {

        $settings = fusion_get_settings();

        $g = new GoogleAuthenticator();

        $info['page_title'] = 'Two factor authentication';
        $info['totp_form_open'] = openform( 'userTOTPSettings', 'POST' );
        $info['totp_form_close'] = closeform();
        $info['totp_submit_button'] = form_button( 'user_totp_submit', 'Submit', '', [
            'class' => 'btn-primary'
        ] );
        $info['totp_email_field'] = form_text( 'email_code', 'Email verification code', '', [
            'append'            => TRUE,
            'append_id'         => 'SendEmailCode',
            'append_class'      => 'btn-text text-primary',
            'append_type'       => 'button',
            'append_button'     => TRUE,
            'append_form_value' => 'send_code',
            'append_value'      => 'Send Code',
            'required'          => TRUE,
            'error_text'        => 'Validation code error',
            'ext_tip'           => 'Send the verification code to ' . censortext( $this->userFields->userData['user_email'] ) . ' and the code will be valid for 10 minutes'
        ] );


        if ( !fusion_get_userdata( 'user_totp' ) ) {

            $info['totp_time'] = showdate('Y-m-d h:i:s Z', time());

            // New setup
            if ( empty( $_SESSION['totp_secret'] ) ) {

                $user_secret = $g->generateSecret();
                $_SESSION['totp_secret'] = $user_secret;

            } else {

                $user_secret = $_SESSION['totp_secret'];
            }
            // Authenticator download links
            $info['qrcode_apple'] = show_qrcode('https://apps.apple.com/au/app/google-authenticator/id388497605');
            $info['qrcode_google'] = show_qrcode('https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en&gl=US');

            //Optionally, you can use $g->generateSecret() to generate your secret
            //$secret = $g->generateSecret();
            $info['totp_key'] = $user_secret;
            $info['totp_form_open'] = openform( 'userTOTPSettings', 'POST' );
            $info['totp_form_close'] = closeform();
            $info['totp_qr_image'] = GoogleQrUrl::generate( $this->userFields->userData['user_email'], $user_secret, $settings['sitename'] );
            $info['totp_code_field'] = form_text( 'totp_code', 'Authenticator Code (TOTP)', '', [
                    'required'    => TRUE,
                    'placeholder' => 'Please enter',
                    'ext_tip'     => '6 digits code on your google authenticator'
                ] ) . form_hidden( 'user_totp', '', $user_secret );
            $info['totp_submit_button'] = form_button( 'user_totp_submit', 'Submit', '', [
                'class' => 'btn-primary'
            ] );
        }

        add_to_jquery("
        var mailButtonId = $('#email_code-append-btn');
        mailButtonId.on('click', function() {
            mailButtonId.addClass('disabled');
            var sendVerificationCode = $.post('" . INCLUDES . "api/?api=email_code', {
                'user_id': '" . $this->userFields->userData['user_id'] . "',
                'user_hash': '" . $this->userFields->userData['user_password'] . "',
            });
            sendVerificationCode.done(function( data ) {
                var json_response = $.parseJSON(data);
                var cssClass = '';
                if (json_response.response > 200) {
                    cssClass = 'error';
                }
                addNotice(json_response.title, json_response.text, cssClass);
            });
            var sec = 30
            var timer = setInterval(function() {
               mailButtonId.text(sec-- + 's').removeClass('text-primary');
               if (sec == -1) {
                  mailButtonId.text('Second Code').removeClass('disabled').addClass('text-primary');
                  clearInterval(timer);
               }
            }, 1000);
        });
        ");

        return $info;
    }

}
