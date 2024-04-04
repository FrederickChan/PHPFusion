<?php
namespace PHPFusion\Userfields\Accounts\Fields;

use Google\Authenticator\GoogleAuthenticator;
use Google\Authenticator\GoogleQrUrl;
use PHPFusion\Userfields\UserFieldsForm;

class AccountTwoFactor extends UserFieldsForm {

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
            // New setup
            if ( empty( $_SESSION['totp_secret'] ) ) {

                $user_secret = $g->generateSecret();
                $_SESSION['totp_secret'] = $user_secret;

            } else {

                $user_secret = $_SESSION['totp_secret'];
            }

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


        return $info;
    }

}
