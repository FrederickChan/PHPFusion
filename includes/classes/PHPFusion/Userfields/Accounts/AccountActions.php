<?php
namespace PHPFusion\Userfields\Accounts;

class AccountActions {

    private $userdata;

    /**
     * @throws \Exception
     */
    public function __construct( $user_id ) {

        $this->userdata = fusion_get_user( $user_id );

        require_once INCLUDES . 'sendmail_include.php';

        fusion_set_locale( LOCALE . LOCALESET . 'admin/members_email.php' );

        if ( !$this->userdata['user_id'] ) {
            throw new \Exception( 'No user found with this ID.'.$user_id );
        }
    }

    // When user goes deactivate own account
    public function userDeactivateUser() {

        $settings = fusion_get_settings();
        $locale = fusion_get_locale();

        $response_required = time() + ( 86400 * $settings['deactivation_response'] );

        $activation_url = $settings['siteurl'] . "reactivate.php?user_id=" . $this->userdata['user_id'] . "&code=" . md5( $response_required . $this->userdata['user_password'] );

        fusion_sendmail( 'U_UDEACTIVATE', display_name( $this->userdata ), $this->userdata['user_email'], [
            'subject' => $locale['email_user_deactivate_subject'],
            'message' => $locale['email_user_deactivate_message'],
            'replace' => [
                '[USER_NAME]'         => $this->userdata['user_name'],
                '[USER_ID]'           => $this->userdata['user_id'],
                '[DURATION]'          => countdown( 86400 * $settings['deactivation_response'] ),
                '[REACTIVATION_LINK]' => $activation_url,
            ]
        ] );

        dbquery( "UPDATE " . DB_USERS . " SET user_status=:status, user_actiontime=:duration WHERE user_id=:uid", [
            ':uid'      => $this->userdata['user_id'],
            ':duration' => $response_required,
            ':status'   => USER_DEACTIVATE
        ] );

        suspend_log( $this->userdata['user_id'], USER_DEACTIVATE, $locale['ME_468'] );

        return TRUE;
    }

    // When deleting a user
    public function deleteUser() {

        // remove avatar
        if ( $this->userdata['user_avatar'] != "" && file_exists( IMAGES . "avatars/" . $this->userdata['user_avatar'] ) ) {
            @unlink( IMAGES . "avatars/" . $this->userdata['user_avatar'] );
        }

        // Delete core content
        dbquery( "DELETE FROM " . DB_USERS . " WHERE user_id=:uid", [':uid' => $this->userdata['user_id']] );

        dbquery( "DELETE FROM " . DB_NEW_USERS . " WHERE user_id=:uid", [':uid' => $this->userdata['user_id']] );

        dbquery( "DELETE FROM " . DB_COMMENTS . " WHERE comment_name=:uid", [':uid' => $this->userdata['user_id']] );

        dbquery( "DELETE FROM " . DB_RATINGS . " WHERE rating_user=:uid", [':uid' => $this->userdata['user_id']] );

        dbquery( "DELETE FROM " . DB_SUSPENDS . " WHERE suspended_user=:uid", [':uid' => $this->userdata['user_id']] );

        dbquery( "DELETE FROM " . DB_MESSAGES . " WHERE message_to=:uid01 OR message_from=:uid02", [
            ':uid01' => $this->userdata['user_id'], ':uid02' => $this->userdata['user_id']] );

        // Do user actions on all infusions
        fusion_apply_hook( 'fusion_user_action', 'delete_user', $this->userdata['user_id'] );

        return TRUE;

    }

}

