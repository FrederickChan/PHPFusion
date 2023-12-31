<?php
namespace PHPFusion\Userfields\Accounts\Fields;

use PHPFusion\Userfields\UserFieldsForm;

class AccountEmail extends UserFieldsForm {

    /**
     * @return string
     */
    public function emailVerificationInput() {

        return form_text( 'email_code', 'Email verification code', '', [
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

    }

    /**
     * Email input field modal
     *
     * @return array
     */
    public function emailInputField() {

        $locale = fusion_get_locale();

        $ext_tip = '';
        if ( !$this->userFields->registration ) {

            $ext_tip = ( iADMIN && checkrights( 'M' ) ) ? '' : $locale['u100'];

            $info['user_email_form_open'] = openform( 'email_change_frm', 'POST' );
            $info['user_email_form_close'] = closeform();
            $info['user_email_change'] = form_text( 'email_code', 'Email verification code', '', [
                'append'            => TRUE,
                'append_id'         => 'SendEmailCode',
                'append_class'      => 'btn-text text-primary',
                'append_type'       => 'button',
                'append_button'     => TRUE,
                'append_form_value' => 'send_code',
                'append_value'      => 'Send Code',
                'required'          => TRUE,
                'error_text'        => 'Validation code error'
            ] );

            $info['user_email_submit'] = form_button( 'user_email_submit', 'Confirm', 'confirm', [
                'class' => 'btn-primary'
            ] );

        }

        // now we need to add js code to send email
        // need to prevent this from breaking from other broken js
        $info['user_email'] = form_text( 'user_email', $locale['u128'], $this->userFields->userData['user_email'], [
            'type'           => 'email',
            "required"       => TRUE,
            'floating_label' => defined( 'FLOATING_LABEL' ),
            'max_length'     => '100',
            'error_text'     => $locale['u126'],
            'ext_tip'        => $ext_tip,
            'placeholder'    => defined( 'FLOATING_LABEL' ) ? 'john.doe@mail.com' : '',
        ] );

        return $info;
    }



}
