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

namespace PHPFusion\Userfields;

use PHPFusion\SiteLinks;
use PHPFusion\UserFields;
use PHPFusion\Userfields\Accounts\AccountMessaging;
use PHPFusion\Userfields\Accounts\AccountPrivacy;
use PHPFusion\Userfields\Accounts\Fields\AccountCaptchas;
use PHPFusion\Userfields\Accounts\Fields\AccountEmail;
use PHPFusion\Userfields\Accounts\Fields\AccountPasswords;
use PHPFusion\Userfields\Accounts\Fields\AccountProfile;
use PHPFusion\Userfields\Accounts\Fields\AccountTwoFactor;
use PHPFusion\Userfields\Accounts\Fields\AccountUsername;

/**
 * Class UserFieldsForm
 * Form builder
 *
 * @package PHPFusion\Userfields
 */
abstract class UserFieldsForm {

    private static $usernameInstance;
    private static $emailInstance;
    private static $passwordInstance;
    private static $profileInstance;
    private static $twofactorInstance;
    private static $privacyInstance;
    private static $pmInstance;
    private static $captchas;

    protected $userFields;

    /**
     * UserFieldsInput constructor.
     *
     * @param UserFields $class
     */
    public function __construct( $class ) {
        $this->userFields = $class;
    }

    /**
     * @return AccountUsername
     */
    public function accountUsername() {
        if ( !isset( self::$usernameInstance ) ) {
            self::$usernameInstance = ( new AccountUsername( $this->userFields ) );
        }
        return self::$usernameInstance;
    }

    /**
     * @return AccountEmail
     */
    public function accountEmail() {
        if ( !isset( self::$emailInstance ) ) {
            self::$emailInstance = ( new AccountEmail( $this->userFields ) );
        }
        return self::$emailInstance;
    }


    /**
     * @return AccountPasswords
     */
    public function accountPassword() {
        if ( !isset( self::$passwordInstance ) ) {
            self::$passwordInstance = ( new AccountPasswords( $this->userFields ) );
        }
        return self::$passwordInstance;
    }

    /**
     * @return AccountProfile
     */
    public function accountProfile() {
        if ( !isset( self::$profileInstance ) ) {
            self::$profileInstance = ( new AccountProfile( $this->userFields ) );
        }
        return self::$profileInstance;
    }

    /**
     * @return AccountTwoFactor
     */
    public function accountTwoFactor() {
        if ( !isset( self::$twofactorInstance ) ) {
            self::$twofactorInstance = ( new AccountTwoFactor( $this->userFields ) );
        }
        return self::$twofactorInstance;
    }

    public function accountPrivacy() {
        if ( !isset( self::$privacyInstance ) ) {
            self::$privacyInstance = ( new AccountPrivacy( $this->userFields ) );
        }
        return self::$privacyInstance;
    }

    public function accountMessaging() {
        if ( !isset( self::$pmInstance ) ) {
            self::$pmInstance = ( new AccountMessaging( $this->userFields ) );
        }
        return self::$pmInstance;
    }

    public function accountCaptchas() {
        if ( !isset( self::$captchas ) ) {
            self::$captchas = ( new AccountCaptchas( $this->userFields ) );
        }
        return self::$captchas;
    }

}
