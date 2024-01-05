<?php
namespace PHPFusion\Userfields\Accounts\Validation;

use PHPFusion\Userfields\UserFieldsValidate;

class AccountCaptchas extends UserFieldsValidate {

    /**
     * Set validation error
     */
    public function validate() {

        $locale = fusion_get_locale();

        if ( isset( $_SESSION['validation'] ) ) {

            $_CAPTCHA_IS_VALID = FALSE;

            include( INCLUDES . "captchas/" . fusion_get_settings( 'captcha' ) . "/captcha_check.php" );

            if ( !$_CAPTCHA_IS_VALID ) {
                fusion_stop( $locale['u194'] );
            }

            unset( $_SESSION['validation'] );

            return $_CAPTCHA_IS_VALID;
        }

        return TRUE;
    }

}
