<?php

namespace PHPFusion\Userfields\Accounts;

use PHPFusion\LegalDocs;
use PHPFusion\Userfields\UserFieldsForm;

/**
 * Class AccountsInput
 * Model
 *
 * @package PHPFusion\Userfields\Accounts
 */
class AccountsForm extends UserFieldsForm {
    /**
     * Edit profile
     * @return array
     */
    public function displayInputFields() {

        $ref = get('ref');

        // we go with email first
        //$this->info = $this->setEmptyInput() + $this->info;

        $info = [
            'ref' => $ref,
            'user_name' => $this->accountUsername()->usernameInputField(),
            'user_totp_status' => !empty($this->userData['user_totp']),
            'user_password_changed' => $this->userFields->userData['user_password_changed'],
            'user_admin_password_changed' => $this->userFields->userData['user_admin_password_changed'],
        ];

        if ($ref == 'details') {

            $info = array_merge($info, $this->accountProfile()->profileInputField());

        } else if ($ref == 'password') {

            $info = array_merge($info, $this->accountPassword()->profilePasswordField());

        } else if ($ref == 'admin_password') {

            $info = array_merge($info, $this->accountPassword()->adminprofilePasswordField());

        } else {

            $info = array_merge($info, $this->accountEmail()->emailInputField());
            //$this->info['user_password'] = $input->passwordInputField();
            //$this->info['user_admin_password'] = $input->adminpasswordInputField();
            //$this->info['user_avatar'] = $input->avatarInput();
        }

        add_to_jquery("
        var mailButtonId = $('#email_code-append-btn');        
        mailButtonId.on('click', function() {
            mailButtonId.addClass('disabled');
            var sendVerificationCode = $.post('" . INCLUDES . "api/?api=email_code', {
            'user_id': '" . $this->userFields->userData['user_id'] . "',
            'user_hash': '" . $this->userFields->userData['user_password'] . "',
            });
            sendVerificationCode.done(function( data ) {
                var json_response = $.parseJSON(data);
                var cssClass = '';
                if (json_response.response > 200) {
                    cssClass = 'error';
                }
                addNotice(json_response.title, json_response.text, cssClass);
            });
            var sec = 30
            var timer = setInterval(function() {
               mailButtonId.text(sec-- + 's').removeClass('text-primary');
               if (sec == -1) {
                  mailButtonId.text('Second Code').removeClass('disabled').addClass('text-primary');
                  clearInterval(timer);
               }
            }, 1000);
        });
        ");


        //$this->info['user_password'] = form_para( $locale['u132'], 'password', 'profile_category_name' );
        //$this->info['user_admin_password'] = $locale['u131'];

        //if ( $this->method == 'validate_update' ) {
        //    // User Password Verification for Email Change
        //    $footer = openmodal( 'verifyPassword', 'Verify password', ['hidden' => TRUE] )
        //        . '<p class="small">Your password is required to proceed. Please enter your current password to update your profile.</p>'
        //        . form_text( 'user_verify_password', $locale['u135a'], '', ['required' => TRUE, 'type' => 'password', 'autocomplete_off' => TRUE, 'max_length' => 64, 'error_text' => $locale['u133'], 'placeholder' => $locale['u100'],] )
        //        . modalfooter( form_button( 'confirm_password', $locale['save_changes'], 'confirm_password', ['id' => 'updateProfilePass', 'class' => 'btn-primary'] ) )
        //        . closemodal();
        //
        //    add_to_footer( $footer );
        //
        //    // Port to edit profile.js
        //    add_to_jquery( "
        //    var submitCallModal = function(dom) {
        //       var form = dom.closest('form'), hashInput = form.find('input[name=\"user_hash\"]');
        //        $('button[name=\"" . $this->postName . "_btn\"]').on('click', function(e) {
        //           e.preventDefault();
        //           $(this).prop('disabled', true);
        //           $('#verifyPassword-Modal').modal('show');
        //           $('#user_verify_password').on('input propertychange paste', function() {
        //                hashInput.val( $(this).val() );
        //           });
        //           $('button[name=\"confirm_password\"]').on('click', function() {
        //                $('#verifyPassword-Modal').modal('hide');
        //                form[0].submit();
        //           });
        //        });
        //    };
        //
        //    var email = $('#user_email').val();
        //    $('#user_email').on('input propertychange paste', function() {
        //        var requireModal = false;
        //        if ($(this).val() != email) {
        //            requireModal = true;
        //        } else {
        //            requireModal = false;
        //        }
        //        if (requireModal) {
        //            // when postname button is clicked, require the modal.
        //            submitCallModal($(this));
        //        }
        //    });
        //    " );
        //
        //} else {
        //
        //    //$info['validate'] = $input->captchaInput();
        //    //$info['terms'] = $input->termInput();
        //}

        return $info;
    }

    /**
     * Register
     * @return array
     */
    public function displaySimpleInputFields() {

        $settings = fusion_get_settings();

        if ($settings['display_validation'] && check_get('validation')) {

            return [
                'form_open' => openform('validateFrm', 'POST'),
                'form_close' => closeform(),
                'validation' => $this->accountCaptchas()->captchaInput(),
                'button' => form_button($this->userFields->postName, 'Validate', 'validate', [
                        "class" => 'btn-primary btn-block btn-lg',
                    ]
                ),
            ];
        }

        return [
                'form_open' => openform('registerFrm', 'POST'),
                'form_close' => closeform(),
                'user_name' => $this->accountUsername()->usernameInputField(),
                'user_password' => $this->accountPassword()->basePasswordInput(),
                'button' => form_button($this->userFields->postName, 'Sign up', 'register', [
                        "class" => 'btn-primary btn-block btn-lg',
                    ]
                ),
                'terms' => $this->termInput(),
            ] + $this->accountEmail()->emailInputField();
    }


    /**
     * Admin Password - not available for everyone except edit profile.
     *
     * @return string
     */
    public function adminpasswordInputField() {
        $locale = fusion_get_locale();

        if (!$this->userFields->registration && iADMIN && !defined('ADMIN_PANEL')) {

            //$this->userFields->info['user_admin_password'] = form_para( $locale['u131'], 'adm_password', 'profile_category_name' );

            if ($this->userFields->userData['user_admin_password']) {
                // This is for changing password

                return
                    form_text('user_admin_password', $locale['u144a'], '', [
                            'type' => 'password',
                            'autocomplete_off' => TRUE,

                            'max_length' => 64,
                            'error_text' => $locale['u136'],
                            'class' => 'm-b-15',
                        ]
                    )
                    . form_text('user_admin_password1', $locale['u144'], '', [
                            'type' => 'password',
                            'autocomplete_off' => TRUE,

                            'max_length' => 64,
                            'error_text' => $locale['u136'],
                            'tip' => $locale['u147'],
                            'password_strength' => TRUE,
                            'class' => 'm-b-15',
                        ]
                    )
                    . form_text('user_admin_password2', $locale['u145'], '', [

                            'type' => 'password',
                            'autocomplete_off' => TRUE,

                            'max_length' => 64,
                            'error_text' => $locale['u136'],
                            'class' => 'm-b-15',
                        ]
                    );
            }

            // This is just setting new password off blank records
            return form_text('user_admin_password', $locale['u144'], '', [
                        'type' => 'password',
                        'autocomplete_off' => TRUE,
                        'password_strength' => TRUE,

                        'max_length' => 64,
                        'error_text' => $locale['u136'],
                        'ext_tip' => $locale['u147'],
                        'class' => 'm-b-15',

                    ]
                ) .
                form_text('user_admin_password2', $locale['u145'], '', [
                        'type' => 'password',
                        'autocomplete_off' => TRUE,

                        'max_length' => 64,
                        'error_text' => $locale['u136'],
                        'class' => 'm-b-15',
                    ]
                );
        }
        return '';
    }


    public function getCustomFields() {
        $user_fields = '';
        if (!empty($this->info['user_field']) && is_array($this->info['user_field'])) {
            foreach ($this->info['user_field'] as $catID => $fieldData) {
                if (!empty($fieldData['title'])) {
                    $user_fields .= form_para($fieldData['title'], 'fieldcat' . $catID);
                }
                if (!empty($fieldData['fields'])) {
                    $user_fields .= implode('', $fieldData['fields']);
                }
            }
        }

        return $user_fields;
    }

    /**
     * Avatar input
     *
     * @return string
     */
    public function avatarInput() {

        // Avatar Field
        if (!$this->userFields->registration) {
            $locale = fusion_get_locale();

            if (isset($this->userFields->userData['user_avatar']) && $this->userFields->userData['user_avatar'] != "") {

                return " < div class='row' ><div class='col-xs-12 col-sm-3' >
                        <strong > " . $locale['u185'] . "</strong></div>
                        <div class='col-xs-12 col-sm-9'>
                        <div class='p-l-10'>
                        <label for='user_avatar_upload'> " . display_avatar($this->userFields->userData, '150px', '', FALSE, 'img-thumbnail') . "</label >
                        <br>
                        " . form_checkbox("delAvatar", $locale['delete'], '', ['reverse_label' => TRUE]) . "
                        </div>
                        </div></div>
";
            }

            return form_fileinput('user_avatar', $locale['u185'], '', [
                'upload_path' => IMAGES . "avatars / ",
                'input_id' => 'user_avatar_upload',
                'type' => 'image',
                'max_byte' => fusion_get_settings('avatar_filesize'),
                'max_height' => fusion_get_settings('avatar_width'),
                'max_width' => fusion_get_settings('avatar_height'),
                'thumbnail' => 0,
                "delete_original" => FALSE,
                'class' => 'm-t-10 m-b-0',
                "error_text" => $locale['u180'],
                "template" => "modern",
                'ext_tip' => sprintf($locale['u184'], parsebytesize(fusion_get_settings('avatar_filesize')), fusion_get_settings('avatar_width'), fusion_get_settings('avatar_height')),
            ]);


        }
        return '';

    }

    /**
     * Display Terms of Agreement Field
     *
     * @return string
     */
    public function termInput() {

        $settings = fusion_get_settings();
        $locale = fusion_get_locale('', [LOCALE . LOCALESET . 'policies.php']);

        if ($this->userFields->displayTerms == 1) {

            if ($_policy = LegalDocs::getInstance()->getPolicies(3)) {

                if (isset($_policy['ups'])) {
                    $policies[] = '<a href="' . BASEDIR . 'legal.php?type=ups" target="_blank">' . $_policy['ups'] . '</a>';
                }

                if (isset($_policy['pps'])) {
                    $policies[] = '<a href="' . BASEDIR . 'legal.php?type=pps" target="_blank">' . $_policy['pps'] . '</a>';
                }

                if (isset($_policy['cps'])) {
                    $policies[] = '<a href="' . BASEDIR . 'legal.php?type=cps" target="_blank">' . $_policy['cps'] . '</a>';
                }
            }

            if (isset($policies)) {
                return sprintf($locale['u193'], $settings['sitename'], $settings['sitename'], format_sentence($policies));
            }
        }

        return '';
    }

}
