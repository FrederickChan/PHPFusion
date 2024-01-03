<?php
namespace PHPFusion\Userfields\Accounts;

use PHPFusion\Userfields\UserFieldsForm;

class AccountMessaging extends UserFieldsForm {

    public function profileMessageField() {

        $locale = fusion_get_locale();

        return [
            'page_title'   => $locale['u600'],
            'pm_openform'  => openform( 'pmSettingsFrm', 'POST' ),
            'pm_closeform' => closeform(),
            // previously 'pm_email_notify'
            'pm_email'     => form_checkbox( 'user_pm_email', $locale['u601'], $this->userFields->userData['user_pm_email'], [
                'type'    => 'radio',
                'options' => [
                    '0' => $locale['u603'],
                    '1' => $locale['u604'],
                    '2' => $locale['u605'],
                ],
                'ext_tip' => $locale['u602']
            ] ),
            'pm_save_sent' => form_checkbox( 'user_pm_save_sent', $locale['u606'], $this->userFields->userData['user_pm_save_sent'], [
                'type'    => 'radio',
                'options' => [
                    '0' => $locale['u603'],
                    '1' => $locale['u608'],
                    '2' => $locale['u609'],
                ],
                'ext_tip' => $locale['u607']
            ] ),
            'pm_button' => form_button( 'user_pm_submit', 'Confirm', 'user_pm_submit', [
                'class' => 'btn-primary',
            ] )
        ];
    }

}
