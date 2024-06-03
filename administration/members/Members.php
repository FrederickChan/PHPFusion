<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: members_administration.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace Pro\Admin\Members;

use PHPFusion\Authenticate;
use Pro\Admin\Members\Controller\Account;
use Pro\Admin\Members\Controller\Manager;

class Members {

    private static $instance = NULL;


    /**
     * Static Instance
     *
     * @return static|null
     */
    public static function getInstance()
    : ?Members {

        if ( self::$instance == NULL ) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private $callables =
        [
            'log'      => 'displaySuspensionLog',
            'login'    => 'impersonate',
            'inactive' => 'inactivateAccount',
            'add'      => 'newAccount',
            'edit'     => 'editAccount',
            'delete'   => 'deleteAccount',
            'resend'   => 'resendAccount',
            'activate' => 'activateAccount'
        ];

    private function pfMembershipList() {

        //if (isset($_REQUEST['action']) && isset($_REQUEST['user_id']) || isset($_REQUEST['lookup'])) {
        //  dadna;
        //    // _+   rforms Actions
        //    $user_action = new Members_Action();
        //    if (isset($_REQUEST['lookup']) && !is_array($_REQUEST['lookup'])) {
        //        $_REQUEST['lookup'] = [$_REQUEST['lookup']];
        //    }
        //    $user_action->set_userID((array)(isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $_REQUEST['lookup']));
        //    $user_action->set_action((string)$_REQUEST['action']);
        //    $user_action->execute();
        //}
    }

    /**
     * Displays Administration
     *
     * @return void
     */
    public function displayAdmin() {

        if ( $ref = get('action') ) {

            if ( isset($this->callables[ $ref ]) ) {

                $class = new Account();

                $callable_method = $this->callables[ $ref ];

                if ( is_callable([ $class, $callable_method ]) ) {

                    /**
                     * @uses \Pro\Admin\Members\Controller\Account::newAccount()
                     * @uses \Pro\Admin\Members\Controller\Account::displaySuspensionLog()
                     * @uses \Pro\Admin\Members\Controller\Account::impersonate();
                     * @uses \Pro\Admin\Members\Controller\Account::inactivateAccount()
                     * @uses \Pro\Admin\Members\Controller\Account::addAccount()
                     * @uses \Pro\Admin\Members\Controller\Account::editAccount()
                     * @uses \Pro\Admin\Members\Controller\Account::deleteAccount()
                     * @uses \Pro\Admin\Members\Controller\Account::resendAccount()
                     * @uses \Pro\Admin\Members\Controller\Account::activateAccount()
                     */
                    $class->$callable_method();

                } else {

                    admin_exit();
                }
            } else {

                admin_exit();
            }
        } else {

            if ( $user_id = get('login', FILTER_VALIDATE_INT) ) {

                Authenticate::loginAs($user_id);

            } else {

                ( new Manager() )->view();
            }
        }
    }


}

//require_once(ADMIN.'members/members_view.php');
require_once( ADMIN . 'members/Helper.php' );
require_once( ADMIN . 'members/sub_controllers/Account.php' );
require_once( ADMIN . 'members/sub_controllers/Manager.php' );
//require_once(ADMIN.'members/sub_controllers/members_display.php');
//require_once(ADMIN.'members/sub_controllers/members_action.php');
//require_once(ADMIN.'members/sub_controllers/members_profile.php');
require_once( INCLUDES . 'suspend_include.php' );
