<?php
namespace PHPFusion\Userfields\Accounts\Fields;

use PHPFusion\Userfields\UserFieldsForm;

class AccountCaptchas extends UserFieldsForm {

    /**
     * Display Captcha
     *
     * @return string
     */
    public function captchaInput() {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $_CAPTCHA_HIDE_INPUT = FALSE;

        include INCLUDES . "captchas/" . $settings['captcha'] . "/captcha_display.php";
        // remove u190

        $html = display_captcha( [
            'captcha_id' => 'captcha_userfields',
            'input_id'   => 'captcha_code_userfields',
            'image_id'   => 'captcha_image_userfields'
        ] );

        if ( $_CAPTCHA_HIDE_INPUT === FALSE ) {

            $html = form_text( 'captcha_code', '', '', [
                'inline'           => 1,
                'required'         => 1,
                'autocomplete_off' => TRUE,
                'width'            => '200px',
                'class'            => 'm-t-15',
                'placeholder'      => $locale['u191']
            ] );
        }

        return $html;
    }

}
