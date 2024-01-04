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

        $info['page_title'] = 'Public profile settings';

        $info['user_avatar_upload'] = form_fileinput( 'user_avatar[]', 'Profile picture', $this->userFields->userData['user_avatar'], [
            'upload_path'   => IMAGES . 'avatars/',
            'template'      => 'thumbnail',
            'jsonurl'       => INCLUDES . 'api/?api=avatar-upload',
            'hide_remove'   => FALSE,
            'default_photo' => IMAGES . 'avatars/avatar_default.svg',
        ] );

        if ( !empty( $this->userFields->userData['user_avatar'] ) ) {
            $info['user_avatar_remove'] = form_button( 'remove_avatar', 'Remove Avatar', 'remove_avatar', ['class' => 'btn-remove'] );
        }

        // Jquery to handle photo upload.
        add_to_jquery( "
        $('#user_avatar').on('filebatchselected', function(event, files) {
           var upload = $('#user_avatar').fileinput('upload');            
        });
        
        $('#user_avatar').on('filebatchuploaderror', function(event, data, previewId, index) {
            var form = data.form, files = data.files, extra = data.extra, response = data.response, reader = data.reader;
        });
        
        $('#user_avatar').on('filebatchuploadsuccess', function(event, data, previewId, index) {
            var form = data.form, files = data.files, extra = data.extra, response = data.response, reader = data.reader;
             addNotice('Profile picture updated.', 'Your profile picture has beeen successfully updated.', 'success');
        });

        $('#remove_avatar').on('click', function(e) {
            e.preventDefault();
            var del = $.post('" . INCLUDES . "api/?api=avatar-delete', {'user_hash':'" . $this->userFields->userData['user_password'] . "', 'user_id':'" . $this->userFields->userData['user_id'] . "'});
            del.done(function(e) {
                if (e['response'] == 200) {
                    $('#user_avatar').fileinput('clear');
                    $('#remove_avatar').remove();
                    addNotice('Profile picture removed.', 'Your profile picture has beeen successfully deleted.', 'success');
                }
            });
        });
        " );

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

        $info['button'] =  form_button( $this->userFields->postName . '_btn', 'Update profile', 'submit', [
                "deactivate" => $this->userFields->displayTerms == 1,
                "class"      => 'btn-primary'
            ]
        );

        return $info + $this->userFields->getUserFields();
    }

}
