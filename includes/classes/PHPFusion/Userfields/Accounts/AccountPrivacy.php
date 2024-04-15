<?php
namespace PHPFusion\Userfields\Accounts;

use PHPFusion\Userfields\UserFieldsForm;

/**
 * Class AccountPrivacy
 * @package PHPFusion\Userfields\Accounts
 */
class AccountPrivacy extends UserFieldsForm {

    /**
     * @return array
     */
    public function profilePrivacyField() {

        $locale = fusion_get_locale();

        return [
            'page_title'          => 'Data Privacy Settings',
            'privacy_openform'    => openform( 'privacySettingsFrm', 'POST' ),
            'privacy_closeform'   => closeform(),
            'user_hide_phone_input'     => form_checkbox( 'user_hide_phone', $locale['u107'], $this->userFields->userData['user_hide_phone'], [
                'toggle'  => TRUE,
                'ext_tip' => $locale['u108']
            ] ),
            'user_hide_email_input'     => form_checkbox( 'user_hide_email', $locale['u051'], $this->userFields->userData['user_hide_email'], [
                'toggle'  => TRUE,
                'ext_tip' => $locale['u106']
            ] ),
            // this can be added by module?
            'user_hide_location_input'  => form_checkbox( 'user_hide_location', 'Hide Location', $this->userFields->userData['user_hide_location'], [
                'toggle'  => TRUE,
                'ext_tip' => $locale['u106']
            ] ),
            'user_hide_birthdate_input' => form_checkbox( 'user_hide_birthdate', 'Hide Birthdate', $this->userFields->userData['user_hide_birthdate'], [
                'toggle'  => TRUE,
                'ext_tip' => $locale['u106']
            ] ),
            'privacy_button'      => form_button( 'user_privacy_submit', 'Confirm', 'user_privacy_submit', [
                'class' => 'btn-primary',
            ] )
        ];
    }

}
