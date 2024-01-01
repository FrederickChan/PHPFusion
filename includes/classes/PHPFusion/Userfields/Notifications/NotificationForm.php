<?php
/*
 * -------------------------------------------------------+
 * | PHPFusion Content Management System
 * | Copyright (C) PHP Fusion Inc
 * | https://phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename: theme.php
 * | Author:  Meangczac (Chan)
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */

namespace PHPFusion\Userfields\Notifications;

use PHPFusion\Userfields\UserFieldsForm;

class NotificationForm extends UserFieldsForm {

    /**
     * Notification form inputs
     *
     * @return array
     */
    public function displayInputFields() {

        return [
            'notify_openform'  => openform( 'notifyFrm', 'POST' ),
            'notify_closeform' => closeform(),
            'comments'         => form_checkbox( 'user_notify_comments', 'Comments', $this->userFields->userData['user_notify_comments'], [
                'type'    => 'toggle',
                'class'   => 'list-group-item m-0 ps-3 pe-3',
                'ext_tip' => 'These are notifications for comments on your posts and replies to your comments.',
            ] ),
            'mentions'         => form_checkbox( 'user_notify_mentions', 'Mentions', $this->userFields->userData['user_notify_mentions'], [
                'type'    => 'toggle',
                'class'   => 'list-group-item m-0 ps-3 pe-3',
                'ext_tip' => 'These are notifications for when someone tags you in a comment, post or story.',
            ] ),
            'subscriptions'    => form_checkbox( 'user_notify_subscriptions', 'Subscriptions', $this->userFields->userData['user_notify_subscriptions'], [
                'type'    => 'toggle',
                'class'   => 'list-group-item m-0 ps-3 pe-3',
                'ext_tip' => 'These are notifications to remind you of updates you may have missed.',
            ] ),
            'birthdays'        => form_checkbox( 'user_notify_birthdays', 'Birthdays', $this->userFields->userData['user_notify_birthdays'], [
                'type'    => 'toggle',
                'class'   => 'list-group-item m-0 ps-3 pe-3',
                'ext_tip' => 'These are notifications about your members birthdays.',
            ] ),
            'groups'           => form_checkbox( 'user_notify_groups', 'Groups', $this->userFields->userData['user_notify_groups'], [
                'type'    => 'toggle',
                'class'   => 'list-group-item m-0 ps-3 pe-3',
                'ext_tip' => 'These are notifications about activity in Groups you have joined.',
            ] ),
            'events'           => form_checkbox( 'user_notify_events', 'Events', $this->userFields->userData['user_notify_events'], [
                'type'    => 'toggle',
                'class'   => 'list-group-item m-0 ps-3 pe-3',
                'ext_tip' => 'These are notifications about Events.',
            ] ),
            'messages'         => form_checkbox( 'user_notify_messages', 'Messaging', $this->userFields->userData['user_notify_messages'], [
                'type'    => 'toggle',
                'class'   => 'list-group-item m-0 ps-3 pe-3',
                'ext_tip' => 'These are notifications for messages you’ve received directly in the private messages inbox.',
            ] ),
            'updates'          => form_checkbox( 'user_notify_updates', 'Updates', $this->userFields->userData['user_notify_updates'], [
                'type'    => 'toggle',
                'class'   => 'list-group-item m-0 ps-3 pe-3',
                'ext_tip' => 'These are notifications for requests, breaking news, expiring offers and more.',
            ] ),
            'notify_button'    => form_button( 'update_notify', 'Confirm', 'update_notify', [
                'class' => 'btn-primary',
            ] )
        ];

    }

}
