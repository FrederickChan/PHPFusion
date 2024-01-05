<?php
namespace PHPFusion\Userfields\Accounts\Validation;

use PHPFusion\Userfields\UserFieldsValidate;

class AccountMessaging extends UserFieldsValidate {

    public function setAccountMesssaging() {
        $locale = fusion_get_locale();

        $rows = [
            'user_id'           => $this->userFieldsInput->userData['user_id'],
            'user_pm_email'     => sanitizer( 'user_pm_email', '', 'user_pm_email' ),
            'user_pm_save_sent' => sanitizer( 'user_pm_save_sent', '', 'user_pm_save_sent' )
        ];
        if ( fusion_safe() ) {

            dbquery_insert( DB_USER_SETTINGS, $rows, 'update' );
            addnotice( 'success', "Account profile updated.\n" . $locale['u611'] );

            redirect( BASEDIR . 'edit_profile.php?ref=pm_options' );
        }
    }
}
