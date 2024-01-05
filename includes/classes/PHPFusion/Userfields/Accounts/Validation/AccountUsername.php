<?php

namespace PHPFusion\Userfields\Accounts\Validation;

use PHPFusion\Userfields\UserFieldsValidate;

class AccountUsername extends UserFieldsValidate {

    /**
     * Handle Username Input and Validation
     */
    public function verifyUserName() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        if ( $settings['username_change'] or $this->userFieldsInput->_method == 'validate_insert' ) {

            $uban = explode( ',', $settings['username_ban'] );
            $username = sanitizer( 'user_name', '', 'user_name' );

            if ( $username != $this->userFieldsInput->userData['user_name'] ) {

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

                        return $username;

                    } else {
                        fusion_stop();
                        Defender::setInputError( 'user_name' );
                        Defender::setErrorText( 'user_name', $locale['u121'] );
                    }

                }
            } else if ( $this->userFieldsInput->_method == 'validate_update' ) {
                return $username;
            }

            //            else {
            //                Defender::setErrorText( 'user_name', $locale['u122'] );
            //                Defender::setInputError( 'user_name' );
            //            }
        }

        return '';
    }

}
