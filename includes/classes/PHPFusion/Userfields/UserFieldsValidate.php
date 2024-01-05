<?php

namespace PHPFusion\Userfields;

use PHPFusion\Userfields\Accounts\AccountsUpdate;
use PHPFusion\Userfields\Accounts\Validation\AccountProfile;
use PHPFusion\Userfields\Accounts\Validation\AccountCaptchas;
use PHPFusion\Userfields\Accounts\Validation\AccountClose;
use PHPFusion\Userfields\Accounts\Validation\AccountEmail;
use PHPFusion\Userfields\Accounts\Validation\AccountMessaging;
use PHPFusion\Userfields\Accounts\Validation\AccountPassword;
use PHPFusion\Userfields\Accounts\Validation\AccountPrivacy;
use PHPFusion\Userfields\Accounts\Validation\AccountTwoFactor;
use PHPFusion\Userfields\Accounts\Validation\AccountUsername;

use PHPFusion\UserFieldsInput;

abstract class UserFieldsValidate {

    private static $updateInstance;
    private static $captchaInstances;
    private static $usernameInstance;
    private static $passwordInstance;
    private static $emailInstance;
    private static $closeInstance;
    private static $twofactorInstance;
    private static $privacyInstance;
    private static $messageInstance;
    private static $profileInstance;
    protected $userFieldsInput;
    protected $_data;
    protected $newPassword;

    /**
     * UserFieldsValidate constructor.
     *
     * @param $userFieldsInput
     */
    public function __construct( UserFieldsInput $userFieldsInput ) {
        $this->userFieldsInput = $userFieldsInput;
    }

    /**
     * @return AccountEmail
     */
    public function accountUpdate() {
        if ( !isset( self::$updateInstance ) ) {
            self::$updateInstance = ( new AccountsUpdate( $this->userFieldsInput ) );
        }
        return self::$updateInstance;
    }

    /**
     * @return AccountUsername
     */
    public function accountUsername() {
        if ( !isset( self::$usernameInstance ) ) {
            self::$usernameInstance = ( new AccountUsername( $this->userFieldsInput ) );
        }
        return self::$usernameInstance;
    }

    /**
     * @return AccountUsername
     */
    public function accountEmail() {
        if ( !isset( self::$emailInstance ) ) {
            self::$emailInstance = ( new AccountEmail( $this->userFieldsInput ) );
        }
        return self::$emailInstance;

    }

    /**
     * @return AccountPassword
     */
    public function accountPassword() {
        if ( !isset( self::$passwordInstance ) ) {
            self::$passwordInstance = ( new AccountPassword( $this->userFieldsInput ) );
        }
        return self::$passwordInstance;
    }

    /**
     * @return AccountCaptchas
     */
    public function accountCaptchas() {
        if ( !isset( self::$captchaInstances ) ) {
            self::$captchaInstances = ( new AccountCaptchas( $this->userFieldsInput ) );
        }
        return self::$captchaInstances;
    }

    /**
     * @return AccountTwoFactor
     */
    public function accountTwoFactor() {
        if ( !isset( self::$twofactorInstance ) ) {
            self::$twofactorInstance = ( new AccountTwoFactor( $this->userFieldsInput ) );
        }
        return self::$twofactorInstance;
    }

    /**
     * @return AccountProfile
     */
    public function accountProfile() {
        if ( !isset( self::$profileInstance ) ) {
            self::$profileInstance = ( new AccountProfile( $this->userFieldsInput ) );
        }
        return self::$profileInstance;
    }

    /**
     * @return AccountClose
     */
    public function accountClose() {
        if ( !isset( self::$closeInstance ) ) {
            self::$closeInstance = ( new AccountClose( $this->userFieldsInput ) );
        }
        return self::$closeInstance;
    }

    /**
     * @return AccountPrivacy
     */
    public function accountPrivacy() {
        if ( !isset( self::$privacyInstance ) ) {
            self::$privacyInstance = ( new AccountPrivacy( $this->userFieldsInput ) );
        }
        return self::$privacyInstance;
    }

    /**
     * @return AccountMessaging
     */
    public function accountMessaging() {
        if ( !isset( self::$messageInstance ) ) {
            self::$messageInstance = ( new AccountMessaging( $this->userFieldsInput ) );
        }
        return self::$messageInstance;
    }

}
