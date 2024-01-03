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
        $locale = fusion_get_locale();

        return [
            'notify_openform'  => openform( 'notifyFrm', 'POST' ),
            'notify_closeform' => closeform(),
            'comments'         => form_checkbox( 'user_notify_comments', $locale['u502'], $this->userFields->userData['user_notify_comments'], [
                'type'    => 'toggle',
                'ext_tip' => $locale['u503'],
            ] ),
            'mentions'         => form_checkbox( 'user_notify_mentions', $locale['u504'], $this->userFields->userData['user_notify_mentions'], [
                'type'    => 'toggle',
                'ext_tip' => $locale['u505'],
            ] ),
            'subscriptions'    => form_checkbox( 'user_notify_subscriptions', $locale['u506'], $this->userFields->userData['user_notify_subscriptions'], [
                'type'    => 'toggle',
                'ext_tip' => $locale['u507'],
            ] ),
            'birthdays'        => form_checkbox( 'user_notify_birthdays', $locale['u508'], $this->userFields->userData['user_notify_birthdays'], [
                'type'    => 'toggle',
                'ext_tip' => $locale['u509'],
            ] ),
            'groups'           => form_checkbox( 'user_notify_groups', $locale['u510'], $this->userFields->userData['user_notify_groups'], [
                'type'    => 'toggle',
                'ext_tip' => $locale['u511'],
            ] ),
            'events'           => form_checkbox( 'user_notify_events', $locale['u512'], $this->userFields->userData['user_notify_events'], [
                'type'    => 'toggle',
                'ext_tip' => 'These are notifications about Events.',
            ] ),
            'messages'         => form_checkbox( 'user_notify_messages', $locale['u514'], $this->userFields->userData['user_notify_messages'], [
                'type'    => 'toggle',
                'ext_tip' => $locale['u515'],
            ] ),
            'updates'          => form_checkbox( 'user_notify_updates', $locale['u518'], $this->userFields->userData['user_notify_updates'], [
                'type'    => 'toggle',
                'ext_tip' => $locale['u519'],
            ] ),
            'notify_button'    => form_button( 'update_notify', 'Confirm', 'update_notify', [
                'class' => 'btn-primary',
            ] )
        ];

    }

}
