<?php
namespace PHPFusion\Userfields\Accounts;

use GoogleAuthenticator\GoogleAuthenticator;
use GoogleAuthenticator\GoogleQrUrl;
use PHPFusion\Geomap;
use PHPFusion\LegalDocs;
use PHPFusion\PasswordAuth;
use PHPFusion\Userfields\UserFieldsForm;

/**
 * Class AccountsInput
 * Model
 *
 * @package PHPFusion\Userfields\Accounts
 */
class AccountsForm extends UserFieldsForm {

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

    /**
     * Shows password input field
     *
     * @return string
     */
    public function passwordInputField() {

        $locale = fusion_get_locale();

        $settings = fusion_get_settings();


        if ( $this->userFields->registration || $this->userFields->moderation ) {

            return form_text( 'user_password1', $locale['u134a'], '', [
                    'type'             => 'password',
                    'autocomplete_off' => TRUE,

                    'max_length'        => 64,
                    'error_text'        => $locale['u134'] . $locale['u143a'],
                    'required'          => !$this->userFields->moderation,
                    'password_strength' => TRUE,
                    'ext_tip'           => $password_tip,
                    'class'             => 'm-b-15'
                ] ) .
                form_text( 'user_password2', $locale['u134b'], '', [
                    'type'             => 'password',
                    'autocomplete_off' => TRUE,

                    'max_length' => 64,
                    'error_text' => $locale['u133'],
                    'required'   => !$this->userFields->moderation
                ] );
        }


        // form_hidden( 'user_hash', '', $this->userFields->userData['user_password'] );

    }

    /**
     * Admin Password - not available for everyone except edit profile.
     *
     * @return string
     */
    public function adminpasswordInputField() {
        $locale = fusion_get_locale();

        if ( !$this->userFields->registration && iADMIN && !defined( 'ADMIN_PANEL' ) ) {

            //$this->userFields->info['user_admin_password'] = form_para( $locale['u131'], 'adm_password', 'profile_category_name' );

            if ( $this->userFields->userData['user_admin_password'] ) {
                // This is for changing password

                return
                    form_text( 'user_admin_password', $locale['u144a'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => TRUE,

                            'max_length' => 64,
                            'error_text' => $locale['u136'],
                            'class'      => 'm-b-15'
                        ]
                    )
                    . form_text( 'user_admin_password1', $locale['u144'], '', [
                            'type'             => 'password',
                            'autocomplete_off' => TRUE,

                            'max_length'        => 64,
                            'error_text'        => $locale['u136'],
                            'tip'               => $locale['u147'],
                            'password_strength' => TRUE,
                            'class'             => 'm-b-15'
                        ]
                    )
                    . form_text( 'user_admin_password2', $locale['u145'], '', [

                            'type'             => 'password',
                            'autocomplete_off' => TRUE,

                            'max_length' => 64,
                            'error_text' => $locale['u136'],
                            'class'      => 'm-b-15'
                        ]
                    );
            }

            // This is just setting new password off blank records
            return form_text( 'user_admin_password', $locale['u144'], '', [
                        'type'              => 'password',
                        'autocomplete_off'  => TRUE,
                        'password_strength' => TRUE,

                        'max_length' => 64,
                        'error_text' => $locale['u136'],
                        'ext_tip'    => $locale['u147'],
                        'class'      => 'm-b-15'

                    ]
                ) .
                form_text( 'user_admin_password2', $locale['u145'], '', [
                        'type'             => 'password',
                        'autocomplete_off' => TRUE,

                        'max_length' => 64,
                        'error_text' => $locale['u136'],
                        'class'      => 'm-b-15'
                    ]
                );
        }
        return '';
    }

    /**
     * Email input field modal
     *
     * @return array
     */
    public function emailInputField() {

        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

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

        /*
        add_to_jquery("
        var current_email = $('#user_email') . val();
        $('#user_email') . on( 'input change propertyChange paste', function ( e){
            if ( current_email !== $(this) . val() ) {
                $('#user_password_verify-field') . removeClass( 'display-none' );
            } else {
                $('#user_password_verify-field') . addClass( 'display-none' );
            }
        } );
        ");
        */
    }

    public function moveToPrivacy() {
        $info['user_hide_phone'] = form_checkbox( 'user_hide_phone', $locale['u107'], $this->userFields->userData['user_hide_phone'], [
            'inline'  => FALSE,
            'toggle'  => TRUE,
            'ext_tip' => $locale['u108']
        ] );
        $info['user_hide_email'] = form_checkbox( 'user_hide_email', $locale['u051'], $this->userFields->userData['user_hide_email'], [
            'inline'  => FALSE,
            'toggle'  => TRUE,
            'ext_tip' => $locale['u106']
        ] );
    }

    /**
     * User Fields field page
     *
     * @return array
     */
    public function profileInputField() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

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
     * @return array
     */
    public function profilePasswordField() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $info['password_form_open'] = openform( 'passwordSettings', 'POST' );

        $info['password_form_close'] = closeform();


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
        $info['password_text'] = format_sentence( $password_strength );

        $info['password_field'] = form_text( 'user_password', $locale['u135a'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'class'            => 'm-b-15'
            ] )
            . form_text( 'user_password1', $locale['u134'], '', [
                'type'              => 'password',
                'autocomplete_off'  => TRUE,
                'max_length'        => 64,
                'error_text'        => $locale['u133'],
                'tip'               => $locale['u147'],
                'password_strength' => TRUE,
                'class'             => 'm-b-15'
            ] )
            . form_text( 'user_password2', $locale['u134b'], '', [
                'type'             => 'password',
                'autocomplete_off' => TRUE,
                'max_length'       => 64,
                'error_text'       => $locale['u133'],
                'class'            => 'm-b-15'
            ] );

        $info['password_email_field'] = form_text( 'email_code', 'Email verification code', '', [
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

        $info['password_submit_button'] = form_button( 'update_password_btn', 'Submit', 'update_password_btn', ['class' => 'btn-primary'] );

        return $info;
    }


    public function profileTOTPField() {

        $settings = fusion_get_settings();

        $g = new GoogleAuthenticator();

        $info['totp_form_open'] = openform( 'userTOTPSettings', 'POST' );
        $info['totp_form_close'] = closeform();
        $info['totp_submit_button'] = form_button( 'user_totp_submit', 'Submit', '', [
            'class' => 'btn-primary'
        ] );
        $info['totp_email_field'] = form_text( 'email_code', 'Email verification code', '', [
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

        if ( !fusion_get_userdata( 'user_totp' ) ) {
            // New setup
            if ( empty( $_SESSION['totp_secret'] ) ) {

                $user_secret = $g->generateSecret();
                $_SESSION['totp_secret'] = $user_secret;

            } else {

                $user_secret = $_SESSION['totp_secret'];
            }

            //Optionally, you can use $g->generateSecret() to generate your secret
            //$secret = $g->generateSecret();
            $info['totp_key'] = $user_secret;
            $info['totp_form_open'] = openform( 'userTOTPSettings', 'POST' );
            $info['totp_form_close'] = closeform();
            $info['totp_qr_image'] = GoogleQrUrl::generate( $this->userFields->userData['user_email'], $user_secret, $settings['sitename'] );


            $info['totp_code_field'] = form_text( 'totp_code', 'Authenticator Code (TOTP)', '', [
                    'required'    => TRUE,
                    'placeholder' => 'Please enter',
                    'ext_tip'     => '6 digits code on your google authenticator'
                ] ) . form_hidden( 'user_totp', '', $user_secret );

            $info['totp_submit_button'] = form_button( 'user_totp_submit', 'Submit', '', [
                'class' => 'btn-primary'
            ] );
        }


        return $info;
    }


    public function getCustomFields() {
        $user_fields = '';
        if ( !empty( $this->info['user_field'] ) && is_array( $this->info['user_field'] ) ) {
            foreach ( $this->info['user_field'] as $catID => $fieldData ) {
                if ( !empty( $fieldData['title'] ) ) {
                    $user_fields .= form_para( $fieldData['title'], 'fieldcat' . $catID );
                }
                if ( !empty( $fieldData['fields'] ) ) {
                    $user_fields .= implode( '', $fieldData['fields'] );
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
        if ( !$this->userFields->registration ) {
            $locale = fusion_get_locale();

            if ( isset( $this->userFields->userData['user_avatar'] ) && $this->userFields->userData['user_avatar'] != "" ) {

                return " < div class='row' ><div class='col-xs-12 col-sm-3' >
                        <strong > " . $locale['u185'] . "</strong ></div >
                        <div class='col-xs-12 col-sm-9' >
                        <div class='p-l-10' >
                        <label for='user_avatar_upload' > " . display_avatar( $this->userFields->userData, '150px', '', FALSE, 'img-thumbnail' ) . "</label >
                        <br >
" . form_checkbox( "delAvatar", $locale['delete'], '', ['reverse_label' => TRUE] ) . "
                        </div >
                        </div ></div >
";
            }

            return form_fileinput( 'user_avatar', $locale['u185'], '', [
                'upload_path'     => IMAGES . "avatars / ",
                'input_id'        => 'user_avatar_upload',
                'type'            => 'image',
                'max_byte'        => fusion_get_settings( 'avatar_filesize' ),
                'max_height'      => fusion_get_settings( 'avatar_width' ),
                'max_width'       => fusion_get_settings( 'avatar_height' ),
                'thumbnail'       => 0,
                "delete_original" => FALSE,
                'class'           => 'm-t-10 m-b-0',
                "error_text"      => $locale['u180'],
                "template"        => "modern",
                'ext_tip'         => sprintf( $locale['u184'], parsebytesize( fusion_get_settings( 'avatar_filesize' ) ), fusion_get_settings( 'avatar_width' ), fusion_get_settings( 'avatar_height' ) )
            ] );


        }
        return '';

    }

    /**
     * Display Captcha
     *
     * @return string
     */
    public function captchaInput() {

        $locale = fusion_get_locale();

        if ( $this->userFields->displayValidation == 1 && $this->userFields->moderation == 0 ) {

            $_CAPTCHA_HIDE_INPUT = FALSE;

            include INCLUDES . "captchas / " . fusion_get_settings( "captcha" ) . " / captcha_display . php";

            $html = " < div class='form-group row' > ";
            $html .= "<label for='captcha_code' class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' > " . $locale['u190'] . " <span class='required' >*</span ></label > ";
            $html .= "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9' > ";

            $html .= display_captcha( [
                'captcha_id' => 'captcha_userfields',
                'input_id'   => 'captcha_code_userfields',
                'image_id'   => 'captcha_image_userfields'
            ] );

            if ( $_CAPTCHA_HIDE_INPUT === FALSE ) {
                $html .= form_text( 'captcha_code', '', '', [
                    'inline'           => 1,
                    'required'         => 1,
                    'autocomplete_off' => TRUE,
                    'width'            => '200px',
                    'class'            => 'm-t-15',
                    'placeholder'      => $locale['u191']
                ] );
            }
            $html .= "</div ></div > ";
            return $html;
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
        $locale = fusion_get_locale( '', [LOCALE . LOCALESET . 'policies.php'] );

        if ( $this->userFields->displayTerms == 1 ) {

            if ( $_policy = LegalDocs::getInstance()->getPolicies( 3 ) ) {

                if ( isset( $_policy['ups'] ) ) {
                    $policies[] = '<a href="' . BASEDIR . 'legal . php ? type = ups" target="_blank">' . $_policy['ups'] . '</a>';
                }

                if ( isset( $_policy['pps'] ) ) {
                    $policies[] = '<a href="' . BASEDIR . 'legal . php ? type = pps" target="_blank">' . $_policy['pps'] . '</a>';
                }

                if ( isset( $_policy['cps'] ) ) {
                    $policies[] = '<a href="' . BASEDIR . 'legal . php ? type = cps" target="_blank">' . $_policy['cps'] . '</a>';
                }
            }

            if ( isset( $policies ) ) {
                add_to_jquery( "

                let registerTermsFn = () => {
    let btnDOM = $('button[name=\"" . $this->userFields->postName . "\"]');
                    if ( btnDOM . length ) {
                        btnDOM = $(btnDOM[0]);
                        btnDOM . attr( 'disabled', TRUE ) . addClass( 'disabled' );
                        $('#agreement') . on( 'click', function () {
                            if ( $(this) . is( ':checked' ) ) {
                                btnDOM . attr( 'disabled', FALSE ) . removeClass( 'disabled' );
                            } else {
                                btnDOM . attr( 'disabled', TRUE ) . addClass( 'disabled' );
                            }
                        } );
                    }
                }

                registerTermsFn();
                " );

                return form_checkbox( 'agreement', sprintf( strtr( $locale['u193'], ['[SITENAME]' => $settings['sitename']] ), format_sentence( $policies ) ), '', ["required" => TRUE, "reverse_label" => TRUE, 'inline' => FALSE] );;
            }

        }

        return '';
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
