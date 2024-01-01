<?php
namespace PHPFusion\Userfields\Notifications;

use PHPFusion\Userfields\UserFieldsValidate;

/**
 * Class NotificationsValidate
 *
 * @package PHPFusion\Userfields\Notifications
 */
class NotificationsValidate extends UserFieldsValidate {

    /**
     * Update notifications settings for users
     */
    public function validate() {

        $locale = fusion_get_locale();

        if ( check_post( 'update_notify' ) ) {

            $rows = [
                'user_notify_comments'      => check_post( 'user_notify_comments' ) ?? '0',
                'user_notify_mentions'      => check_post( 'user_notify_mentions' ) ?? '0',
                'user_notify_subscriptions' => check_post( 'user_notify_subscriptions' ) ?? '0',
                'user_notify_birthdays'     => check_post( 'user_notify_birthdays' ) ?? '0',
                'user_notify_groups'        => check_post( 'user_notify_groups' ) ?? '0',
                'user_notify_events'        => check_post( 'user_notify_events' ) ?? '0',
                'user_notify_messages'      => check_post( 'user_notify_messages' ) ?? '0',
                'user_notify_updates'       => check_post( 'user_notify_updates' ) ?? '0',
            ];

            if ( $this->userFieldsInput->checkUpdateAccess() ) {

                if ( fusion_safe() ) {

                    dbquery_insert( DB_USER_SETTINGS, $rows, 'update' );

                    addnotice( 'success', $locale['u163']."\n".$locale['u521'] );

                    redirect(BASEDIR.'edit_profile.php?section=notifications');
                }
            }
        }

    }

}
