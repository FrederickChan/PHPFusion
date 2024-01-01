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

namespace PHPFusion\Userfields\Privacy;

use PHPFusion\Authenticate;
use PHPFusion\Quantum\QuantumFactory;
use PHPFusion\Userfields\UserFieldsForm;

class PrivacyForm extends UserFieldsForm {

    /**
     * Deprecated
     *
     * @return array
     */
    public function displayInputFields() {

        switch ( $this->userFields->info['ref'] ) {

            case 'login':
                return $this->getLoginField();
            case 'blacklist':
                return $this->getBlacklistField();
            default:
                return [];
        }

    }

    private function getLoginField() {

        if ( $session = get( 'rm' ) ) {

            dbquery( "DELETE FROM " . DB_USER_SESSIONS . " WHERE user_session=:session_name AND user_id=:uid", [
                ':uid'          => $this->userFields->userData['user_id'],
                ':session_name' => $session,
            ] );

            if ( $session == $this->userFields->userData['user_session'] ) {
                Authenticate::logOut();
                redirect( BASEDIR . fusion_get_settings( 'opening_page' ) );
            } else {
                redirect( FUSION_REQUEST );
            }
        }

        $res = dbquery( "SELECT * FROM " . DB_USER_SESSIONS . " WHERE user_id=:uid ORDER BY user_logintime DESC", [':uid' => $this->userFields->userData['user_id']] );

        if ( dbrows( $res ) ) {
            while ( $rows = dbarray( $res ) ) {
                $rows['remove'] = BASEDIR . 'edit_profile.php?section=privacy&ref=login&rm=' . $rows['user_session'];
                $info['user_logins'][] = $rows;
            }
        }

        return $info;
    }

    private function getBlacklistField() {

        $result = dbquery( "SELECT * FROM " . DB_USER_BLACKLIST . " WHERE user_id=:uid ORDER BY blacklist_time DESC", [
            ':uid' => $this->userFields->userData['user_id']
        ] );
        if ( dbrows( $result ) ) {
            while ( $rows = dbarray( $result ) ) {
                $rows['remove'] = BASEDIR . 'edit_profile.php?section=privacy&ref=blacklist&rm=' . $rows['blacklist_uid'];
                $data[] = $rows;
            }
        }

        return [];
    }

    /**
     * In development
     * User log information
     *
     * @return array
     */
    private function getUserLogsField() {

        $locale = fusion_get_locale();

        $field_names = [
            'user_name'           => $locale['u068'],
            'user_firstname'      => $locale['u010'],
            'user_lastname'       => $locale['u011'],
            'user_addname'        => $locale['u012'],
            'user_password'       => $locale['u133'],
            'user_admin_password' => $locale['u144a'],
            'user_phone'          => $locale['u013'],
            'user_email'          => $locale['u128'],
            'user_level'          => $locale['u063'],
        ];

        $res = dbquery( "SELECT field_title, field_name FROM " . DB_USER_FIELDS );
        if ( dbrows( $res ) ) {
            while ( $rows = dbarray( $res ) ) {
                $field_names[$rows['field_name']] = parse_label( $rows['field_title'] );
            }
        }

        $res = dbquery( "SELECT * FROM " . DB_USER_LOG . " WHERE userlog_user_id=:uid ORDER BY userlog_timestamp DESC", [':uid' => (int)$this->userFields->userData['user_id']] );
        if ( dbrows( $res ) ) {
            while ( $rows = dbarray( $res ) ) {

                $rows['title'] = $locale['u075'];

                if ( isset( $field_names[$rows['userlog_field']] ) ) {
                    $log = sprintf( $locale['u076'], '<strong>' . $field_names[$rows['userlog_field']] . '</strong>', $rows['userlog_value_old'], $rows['userlog_value_new'] );
                } else {
                    $log = sprintf( $locale['u077'], $rows['userlog_value_old'], $rows['userlog_value_new'] );
                }

                $rows['description'] = $log;

                $info['user_log'][$rows['userlog_id']] = $rows;
            }
        }

        return $info ?? [];
    }


}
