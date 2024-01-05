<?php
namespace PHPFusion\Userfields\Accounts;

use PHPFusion\PasswordAuth;
use PHPFusion\Userfields\UserFieldsValidate;

class AccountsUpdate extends UserFieldsValidate {


    /**
     * Handle request for email verification
     * Sends Verification code when you change email
     * Sends Verification code when you register
     */
    public function sendEmailVerification( $data ) {

        $settings = fusion_get_settings();
        $locale = fusion_get_locale();

        require_once INCLUDES . 'sendmail_include.php';

        $userCode = hash_hmac( "sha1", PasswordAuth::getNewPassword(), $data['user_email'] );

        $activationUrl = $settings['siteurl'] . "register.php?email=" . $data['user_email'] . "&code=" . $userCode;

        $message = str_replace( "USER_NAME", $data['user_name'], $locale['u152'] );
        $message = str_replace( "SITENAME", $settings['sitename'], $message );
        $message = str_replace( "SITEUSERNAME", $settings['siteusername'], $message );
        $message = str_replace( "USER_PASSWORD", $this->newPassword, $message );
        $message = str_replace( "ACTIVATION_LINK", $activationUrl, $message );

        $subject = str_replace( "[SITENAME]", $settings['sitename'], $locale['u151'] );

        if ( !sendemail( $data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message ) ) {

            $message = strtr( $locale['u154'], [
                '[LINK]'  => "<a href='" . BASEDIR . "contact.php'><strong>",
                '[/LINK]' => "</strong></a>"
            ] );

            addnotice( 'warning', $locale['u153'] . "\n" . $message, 'all' );
        }

        if ( fusion_safe() ) {

            $email_rows = [
                'user_code'      => $userCode,
                'user_name'      => $data['user_name'],
                'user_email'     => $data['user_email'],
                'user_datestamp' => time(),
                'user_info'      => base64_encode( serialize( $data ) )
            ];

            dbquery_insert( DB_NEW_USERS, $email_rows, 'save', ['primary_key' => 'user_name', 'no_unique' => TRUE] );
        }

        addnotice( 'success', $locale['u150'] );
    }

    /**
     * Send mail when an administrator adds a user from admin panel
     */
    function sendAdminRegistrationMail( $data ) {

        $settings = fusion_get_settings();

        $locale = fusion_get_locale( '', LOCALE . LOCALESET . "admin/members_email.php" );

        require_once INCLUDES . "sendmail_include.php";

        $subject = str_replace( "[SITENAME]", $settings['sitename'], $locale['email_create_subject'] );

        $replace_this = ["[USER_NAME]", "[PASSWORD]", "[SITENAME]", "[SITEUSERNAME]"];

        $replace_with = [
            $data['user_name'], $this->newPassword, $settings['sitename'], $settings['siteusername']
        ];

        $message = str_replace( $replace_this, $replace_with, $locale['email_create_message'] );

        sendemail( $data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message );

        // Administrator complete message
        addnotice( 'success', $locale['u172'] );
    }

    /**
     * @param $data
     */
    public function createAccount( $data ) {

        $locale = fusion_get_locale();

        $insert_id = dbquery_insert( DB_USERS, $data, 'save' );

        dbquery_insert( DB_USER_SETTINGS, $this->userFieldsInput->setEmptySettingsField( $insert_id ), 'save', ['no_unique' => TRUE, 'primary_key' => 'user_id'] );

        /**
         * Create user
         */
        $notice = $locale['u160'] . "\n" . $locale['u161'];

        if ( $this->userFieldsInput->moderation == 1 ) {

            $this->accountUpdate()->sendAdminRegistrationMail($data);

        } else {

            // got admin activation and not
            if ( fusion_get_settings( 'admin_activation' ) ) {
                // Missing registration data?
                $notice = $locale['u160'] . "\n" . $locale['u162'];
            }
        }

        addnotice( 'success', $notice, 'all' );
    }

}
