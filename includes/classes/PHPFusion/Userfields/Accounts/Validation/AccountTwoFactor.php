<?php
namespace PHPFusion\Userfields\Accounts\Validation;

use Google\Authenticator\GoogleAuthenticator;
use PHPFusion\EmailAuth;
use PHPFusion\Userfields\UserFieldsValidate;

class AccountTwoFactor extends UserFieldsValidate {
    /**
     * Update TOTP
     */
    public function validate() {

        if ( $this->userFieldsInput->userData['user_totp'] ) {

            if ( $validation_code = sanitizer( 'email_code', '', 'email_code' ) ) {

                $email_auth = ( new EmailAuth() );

                $email_auth->setCode( $validation_code );

                if ( $email_auth->verifyCode() === TRUE ) {

                    dbquery( "UPDATE " . DB_USERS . " SET user_totp=:secret WHERE user_id=:uid", [
                        ':secret' => $this->userFieldsInput->userData['user_totp'],
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

}
