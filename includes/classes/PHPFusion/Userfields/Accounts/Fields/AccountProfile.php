<?php
namespace PHPFusion\Userfields\Accounts\Fields;

use PHPFusion\Geomap;
use PHPFusion\Userfields\UserFieldsForm;

class AccountProfile extends UserFieldsForm {

    /**
     * User Fields field page
     *
     * @return array
     */
    public function profileInputField() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $info['page_title'] = 'Details';

        $info['user_firstname'] = form_text( 'user_firstname', $locale['u010'], $this->userFields->userData['user_firstname'], [
        ] );
        $info['user_lastname'] = form_text( 'user_lastname', $locale['u011'], $this->userFields->userData['user_lastname'], [
        ] );
        $info['user_addname'] = form_text( 'user_addname', $locale['u012'], $this->userFields->userData['user_addname'], [
        ] );
        $info['user_name_display'] = form_select( 'user_displayname', 'Display username', '1', [
            'options' => [
                0 => 'Display as Username',
                1 => 'Display as Real name'
            ]
        ] );

        $info['user_name'] = form_text( 'user_name', 'Username', $this->userFields->userData['user_name'], [
            'deactivate' => !$settings['username_change'],
            'required'   => $settings['username_change'],
            'max_length' => 30,
            'error_text' => $locale['u122'],
        ] );

        /*
         *  const request = await fetch("https://ipinfo.io/json?token=you_token");
            const jsonResponse = await request.json();
            console.log(jsonResponse.ip, jsonResponse.country);
         */
        $info['user_phone'] = form_select( 'user_phonecode', $locale['u013'], '', [
            'options' => ( new Geomap() )->callingCodesOpts(),
            'stacked' => form_text( 'user_phone', '', $this->userFields->userData['user_phone'], [
                'placeholder' => '',
                'class'       => 'mb-0 ms-1 flex-fill',
            ] )
        ] );

        $info['user_bio'] = form_textarea( 'user_bio', $locale['u015'], $this->userFields->userData['user_bio'], [
            'wordcount' => TRUE, 'maxlength' => 255] );

        $info['form_open'] = openform( 'userSettingsFrm', 'POST' );
        $info['form_close'] = closeform();
        $info['button'] = $this->renderButton();

        return $info + $this->userFields->getUserFields();
    }


    /**
     * @return string
     */
    public function renderButton() {

        $disabled = $this->userFields->displayTerms == 1;

        $this->userFields->options += $this->userFields->defaultInputOptions;

        //        $html = (!$this->userFields->skipCurrentPass) ? form_hidden( 'user_hash', '', $this->userFields->userData['user_password'] ) : '';

        return
            form_hidden( $this->userFields->postName, '', 'submit' ) .
            form_button( $this->userFields->postName . '_btn', 'Save Profile', 'submit', [
                    "deactivate" => $disabled,
                    "class"      => $this->userFields->options['btn_post_class'] ?? 'btn-primary'
                ]
            );

    }
}
