<?php
namespace PHPFusion\Userfields\Accounts;

use PHPFusion\Authenticate;
use PHPFusion\Userfields\UserFieldsValidate;

class CloseValidate extends UserFieldsValidate {

    public function validate() {

        $settings = fusion_get_settings();

        if ( check_post( 'confirm_close' ) ) {

            if ( $option = sanitizer( 'close_options', '', 'close_options' ) ) {

                if ($this->userFieldsInput->userData['user_level'] > iSUPERADMIN) {

                    $acc_action = new AccountActions($this->userFieldsInput->userData['user_id']);

                    switch ( $option ) {
                        case 'deactivate':
                            if ($acc_action->userDeactivateUser()) {
                                Authenticate::logOut();
                                redirect(BASEDIR.$settings['opening_page']);
                            }
                            break;
                        case 'delete':
                            if ($acc_action->deleteUser()) {
                                Authenticate::logOut();
                                redirect(BASEDIR.$settings['opening_page']);
                            }
                            break;
                        default:
                            redirect( BASEDIR . 'edit_profile.php' );
                    }
                } else {
                    addnotice('danger', "Action could not be performed.\nA super administrator account cannot be deactivated nor deleted.");
                    redirect( BASEDIR . 'edit_profile.php' );
                }
            }
        }

        return FALSE;
    }

}
