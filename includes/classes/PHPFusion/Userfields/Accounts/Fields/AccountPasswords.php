<?php
namespace PHPFusion\Userfields\Accounts\Fields;

use PHPFusion\Userfields\UserFieldsForm;

class AccountPasswords extends UserFieldsForm {

    /**
     * @return array
     */
    public function profilePasswordField() {
        $info['page_title'] = 'Password management';
        $info['password_text'] = $this->showPasswordTip();
        $info['password_form_open'] = openform( 'passwordSettings', 'POST' );
        $info['password_form_close'] = closeform();
        $info['password_field'] = $this->passwordInput();
        $info['password_email_field'] = $this->accountEmail()->emailVerificationInput();
        $info['password_submit_button'] = form_button( 'user_pass_submit', 'Submit', 'user_pass_submit', ['class' => 'btn-primary'] );

        return $info;
    }

    /**
     * @return array
     */
    public function adminprofilePasswordField() {

        $info['page_title'] = 'Admin password management';
        $info['password_form_open'] = openform( 'passwordSettings', 'POST' );
        $info['password_form_close'] = closeform();
        $info['password_text'] = $this->accountPassword()->showPasswordTip();
        $info['password_field'] = $this->accountPassword()->adminPasswordInput();
        $info['password_email_field'] = $this->accountEmail()->emailVerificationInput();
        $info['password_submit_button'] = form_button( 'user_adminpass_submit', 'Submit', 'user_adminpass_submit', ['class' => 'btn-primary'] );

        return $info;
    }

    public function showPasswordTip() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $password_strength[] = sprintf( $locale['u147'], (int)$settings['password_length'] );

        if ( $settings['password_char'] or $settings['password_num'] or $settings['password_case'] ) {
            $strength_test = [];
            if ( $settings['password_case'] ) {
                $strength_test[] = $locale['u147b'];
            }
            if ( $settings['password_num'] ) {
                $strength_test[] = $locale['u147c'];
            }
            if ( $settings['password_char'] ) {
                $strength_test[] = $locale['u147d'];
            }
            $password_strength[] = sprintf( $locale['u147a'], format_sentence( $strength_test ) );
        }

        return format_sentence( $password_strength );
    }

    public function adminPasswordInput() {

        $locale = fusion_get_locale();

        return form_text( 'user_admin_password', $locale['u144a'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'class'            => 'm-b-15'
            ] )
            . form_text( 'user_admin_password1', $locale['u144'], '', [
                'type'              => 'password',
                'autocomplete_off'  => TRUE,
                'max_length'        => 64,
                'error_text'        => $locale['u133'],
                'tip'               => $locale['u147'],
                'password_strength' => TRUE,
                'class'             => 'm-b-15'
            ] )
            . form_text( 'user_admin_password2', $locale['u145'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'class'            => 'm-b-15'
            ] );
    }

    public function passwordInput() {
        $locale = fusion_get_locale();

        return $this->basePasswordInput()
            . form_text( 'user_password2', $locale['u135c'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'class'            => 'm-b-15'
            ] );
    }

    public function basePasswordInput() {
        $locale = fusion_get_locale();

        return form_text( 'user_password', $locale['u135b'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'class'            => 'm-b-15'
            ] );

    }
}
