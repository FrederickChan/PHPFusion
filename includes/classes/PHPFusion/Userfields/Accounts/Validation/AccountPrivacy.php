<?php
namespace PHPFusion\Userfields\Accounts\Validation;

use PHPFusion\Userfields\UserFieldsValidate;

class AccountPrivacy  extends UserFieldsValidate {

    public function setAccountPrivacy() {

        $rows = [
            'user_id'             => $this->userFieldsInput->userData['user_id'],
            'user_hide_phone'     => sanitizer( 'user_hide_phone', '0', 'user_hide_phone' ),
            'user_hide_email'     => sanitizer( 'user_hide_email', '0', 'user_hide_email' ),
            'user_hide_location'  => sanitizer( 'user_hide_location', '0', 'user_hide_location' ),
            'user_hide_birthdate' => sanitizer( 'user_hide_birthdate', '0', 'user_hide_birthdate' ),
        ];

        if ( fusion_safe() ) {

            dbquery_insert( DB_USER_SETTINGS, $rows, 'update' );
            addnotice( 'success', "Account profile updated.\nPrivacy settings has been updated successfully." );
            redirect( BASEDIR . 'edit_profile.php?ref=privacy' );
        }
    }

}
