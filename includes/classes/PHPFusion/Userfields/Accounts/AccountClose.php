<?php
namespace PHPFusion\Userfields\Accounts;

use PHPFusion\Userfields\UserFieldsForm;

class AccountClose extends UserFieldsForm {

    public function displayInputFields() {

        return [
            'close_openform'  => openform( 'closeAccountFrm', 'POST' ),
            'close_closeform' => closeform(),
            'close_options'   => form_checkbox( 'close_options', '', '', [
                'type'          => 'radio',
                'reverse_label' => TRUE,
                'class'         => 'list-group',
                'options'       => [
                    'deactivate' => '<h6 class="m-0">Deactivate Account</h6><p class="pt-2"><strong>Deactivating your account is temporary.</strong>
                                Your account will be deactivated and your name and contents will be anonymized from most things you have shared.</p>',
                    'delete'     => '<h6 class="m-0">Delete Account</h6><p class="pt-2"><strong>Deleting your account is permanent.</strong>
                                When you delete your account, you will not be able to retrieve the content or information. Your profile and your messages will be deleted.</p>'
                ]
            ] ),
            'cancel_button' => '<a href="'.BASEDIR.'edit_profile.php" class="btn btn-success-soft">Keep my account</a>',
            'close_button'    => form_button( 'confirm_close', 'Confirm', 'confirm_close', [
                'class' => 'btn-primary',
            ] )
        ];
    }


}
