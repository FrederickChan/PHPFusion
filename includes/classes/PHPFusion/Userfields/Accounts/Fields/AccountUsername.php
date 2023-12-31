<?php
namespace PHPFusion\Userfields\Accounts\Fields;

use PHPFusion\Userfields\UserFieldsForm;

class AccountUsername extends UserFieldsForm {

    /**
     * User Name input field
     *
     * @return string
     */
    public function usernameInputField() {

        $locale = fusion_get_locale();

        if ( iADMIN || $this->userFields->username_change ) {

            return form_text( 'user_name', $locale['u127'], $this->userFields->userData['user_name'], [
                'max_length'     => 30,
                'required'       => TRUE,
                'floating_label' => defined( 'FLOATING_LABEL' ),
                'placeholder'    => defined( 'FLOATING_LABEL' ) ? $locale['u127'] : '',
                'error_text'     => $locale['u122'],
            ] );
        }

        return form_hidden( "user_name", "", $this->userFields->userData["user_name"] );
    }

}
