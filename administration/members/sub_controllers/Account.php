<?php

namespace Pro\Admin\Members\Controller;

use PHPFusion\UserFields;
use PHPFusion\UserFieldsInput;
use PHPMailer\PHPMailer\Exception;
use Pro\Admin\Members\Helper;

/**
 * Account class
 */
class Account extends Helper {

    /**
     * Show logs
     *
     * @uses displayAdmin
     * @return void
     */
    private function displaySuspensionLog() {

        if ( ! $this->getIsAdmin() ) {
            display_suspend_log($this->getUserId(), 'all', get('rowstart', FILTER_VALIDATE_INT));
        }
    }

    /**
     * Impersonate login session - by giving links
     *
     * @return void
     */
    private function impersonate() {

        if ( $user = fusion_get_user(self::$user_id) ) {
            if ( $user["user_id"] ) {
                if ( fusion_get_userdata("user_level") <= $user["user_level"] && fusion_get_userdata("user_id") != $user["user_id"] ) {
                    session_add("login_as", $user["user_id"]);
                    redirect(BASEDIR . self::$settings["opening_page"]);
                }
            }
        }
        redirect(FUSION_REQUEST);
    }


    /**
     * Inactivate account
     *
     * @return void
     * @throws Exception
     */
    private function inactivateAccount() {

        if ( ! self::$user_id && fusion_get_settings('enable_deactivation') && self::$is_admin ) {
            $inactive = dbcount("(user_id)", DB_USERS,
                                "user_status='0' AND user_level>" . USER_LEVEL_SUPER_ADMIN . " AND user_lastvisit <:last_visited AND user_actiontime=:action_time",
                                [
                                    ':last_visited' => self::$time_overdue,
                                    ':action_time'  => 0,
                                ]
            );
            $button = self::$locale['ME_502'] . format_word($inactive, self::$locale['fmt_user']);
            if ( ! $inactive ) {
                add_notice('success', self::$locale['ME_460']);
                redirect(FUSION_SELF . fusion_get_aidlink());
            }

            if ( isset($_POST['deactivate_users']) && \defender::safe() ) {
                require_once INCLUDES . "sendmail_include.php";
                $result = dbquery("SELECT user_id, user_name, user_email, user_password FROM " . DB_USERS . "
                                        WHERE user_level>" . USER_LEVEL_SUPER_ADMIN . " AND user_lastvisit<'" . self::$time_overdue . "' AND user_actiontime='0' AND user_status='0'
                                        LIMIT 0,50
                                        ");
                $rows = dbrows($result);
                if ( $rows != '0' ) {
                    while ( $data = dbarray($result) ) {
                        $message = strtr(self::$locale['email_deactivate_message'], [
                                                                                      '[CODE]'         => md5(self::$response_required . $data['user_password']),
                                                                                      '[SITENAME]'     => self::$settings['sitename'],
                                                                                      '[SITEUSERNAME]' => self::$settings['siteusername'],
                                                                                      '[USER_NAME]'    => $data['user_name'],
                                                                                      '[USER_ID]'      => $data['user_id'],
                                                                                  ]
                        );
                        if (
                            sendemail($data['user_name'],
                                      $data['user_email'],
                                      self::$settings['siteusername'],
                                      self::$settings['siteemail'],
                                      self::$locale['email_deactivate_subject'],
                                      $message)
                        ) {
                            dbquery("UPDATE " . DB_USERS . " SET user_status='7', user_actiontime='" . self::$response_required . "' WHERE user_id='" . $data['user_id'] . "'");
                            suspend_log($data['user_id'], self::USER_DEACTIVATE, self::$locale['ME_468']);
                        }
                    }
                    add_notice('success',
                               sprintf(self::$locale['ME_461'], format_word($rows, self::$locale['fmt_user'])));
                    redirect(FUSION_SELF . fusion_get_aidlink());
                }
            }

            // Put this into view.

            BreadCrumbs::getInstance()->addBreadCrumb([ 'link' => self::$status_uri['inactive'], 'title' => self::$locale['ME_462'] ]);
            opentable(self::$locale['ME_462']);
            if ( $inactive > 50 ) {
                add_notice('info', sprintf(self::$locale['ME_463'], floor($inactive / 50)));
            }
            echo "<div>";
            $action = fusion_get_settings('deactivation_action') == 0 ? self::$locale['ME_556'] : self::$locale['ME_557'];
            $text = sprintf(self::$locale['ME_464'],
                            $inactive,
                            self::$settings['deactivation_period'],
                            self::$settings['deactivation_response'],
                            $action);
            echo str_replace([ "[strong]", "[/strong]" ],
                             [ "<strong>", "</strong>" ],
                             $text
            );
            if ( self::$settings['deactivation_action'] == 1 ) {
                echo "<br />\n" . self::$locale['ME_465'];
                echo "</div>\n<div class='admin-message alert alert-warning m-t-10'><strong>" . self::$locale['ME_454'] . "</strong>\n" . self::$locale['ME_466'] . "\n";
                if ( checkrights('S9') ) {
                    echo "<a href='" . ADMIN . "settings_users.php" . fusion_get_aidlink() . "'>" . self::$locale['ME_467'] . "</a>";
                }
            }
            echo "</div>\n<div class='text-center'>\n";
            echo openform('member_form', 'post', self::$status_uri['inactive']);
            echo form_button('deactivate_users', $button, $button, [ 'class' => 'btn-primary m-r-10' ]);
            echo form_button('cancel', self::$locale['cancel'], self::$locale['cancel']);
            echo closeform();
            echo "</div>\n";
            closetable();
        }
    }

    /**
     * Add Account Form
     *
     * @used-by
     *
     * @return void
     */
    public function newAccount() {

        $locale = $this->getLocale();

        add_breadcrumb([ 'link' => ADMIN_CURRENT_DIR . '&ref=add', 'title' => $locale['ME_450'] ]);

        if ( post('form_id') == 'newAccountFrm' ) {

            $userInput = new UserFieldsInput();
            $userInput->validation = FALSE;
            $userInput->emailVerification = FALSE;
            $userInput->adminActivation = FALSE;
            $userInput->registration = TRUE;
            $userInput->skipCurrentPass = TRUE;
            $userInput->subscription = TRUE;
            $userInput->admin_mode = TRUE;
            $userInput->redirect_uri = ADMIN_CURRENT_DIR;
            $userInput->saveInsert();

        }

        $userFields = new UserFields();
        $userFields->postName = "savesettings";
        $userFields->formname = 'newAccountFrm';
        $userFields->postValue = $locale['ME_450'];
        $userFields->displayValidation = fusion_get_settings("display_validation");
        $userFields->plugin_folder = [ INCLUDES . "user_fields/", INFUSIONS ];
        $userFields->plugin_locale_folder = LOCALE . LOCALESET . "user_fields/";
        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;
        $userFields->registration = TRUE;
        $userFields->admin_mode = TRUE;
        $userFields->method = 'input';
        $userFields->displayProfileInput();

    }

    private function displayImpersonate() {

        $settings = fusion_get_settings();

        add_to_footer(
            openmodal('impersona',
                      '',
                      [ 'button_id' => 'impersonate', 'class' => 'modal-md' ]) .
            '<div class="flex column ac gap-15 p-l-15 p-r-15">' .
            '<h3>Impersonate ' . $this->data['user_name'] . '</h3>' .
            '<div class="spacer-sm">' .
            display_avatar($this->data, '100px', '', FALSE, 'img-circle') .
            '</div>' .
            '<p class="text-center">This is an authentication link to sign in to ' . $settings['sitename'] . ' as <strong>' . $this->data['user_name'] . '</strong>.<br>
                                You can send this link to anyone who need it, or use it to sign in their account.</p>' .
            form_text('plink',
                      '',
                      $settings['siteurl'] . 'login.php?token=' . \PHPFusion\Authenticate::impersonateUrl($this->data['user_id']),
                      [
                          'deactivate'        => TRUE,
                          'append'            => TRUE,
                          'append_form_value' => 'copy',
                          'append_value'      => 'Copy link',
                          'append_button'     => TRUE,
                          'append_class'      => 'btn-primary',
                          'append_type'       => 'button',
                          'width'             => '100%',
                          'inner_width'       => '100%',
                          'append_button_id'  => 'copy-persona-link',
                          'append_data'       => [
                              'clipboard-target' => '#plink'
                          ]
                      ]) .
            '<p>This link is only valid for the next <strong>24 hours</strong></p>' .
            '</div>' .
            closemodal()
        );

        // copy link js
        fusion_load_script(INCLUDES . 'jscripts/clipboard.js');
    }

    private $data;

    /**
     * Edit Account Form
     *
     * @return void
     * @throws Exception
     */
    public function editAccount() {

        $locale = $this->getLocale();

        if ( $this->data = fusion_get_user($this->getUserId()) ) {

            // Redirect if data is empty or user is a super admin
            if ( ! $this->data['user_id'] or $this->data['user_level'] <= USER_LEVEL_SUPER_ADMIN ) {
                redirect(ADMIN_CURRENT_DIR);
            }

            add_breadcrumb(
                [
                    'link'  => ADMIN_CURRENT_DIR . '&action=edit&id=' . $this->data['user_id'],
                    'title' => sprintf($locale['ME_452'], $this->data['user_name'])
                ]
            );

            $userInput = new UserFieldsInput();
            $userInput->userData = $this->data;
            $userInput->validation = FALSE;
            $userInput->emailVerification = FALSE;
            $userInput->adminActivation = FALSE;
            $userInput->skipCurrentPass = TRUE;
            $userInput->subscription = TRUE;
            $userInput->admin_mode = TRUE;
            $userInput->registration = FALSE;
            $userInput->redirect_uri = ADMIN_CURRENT_DIR;

            $userFields = new UserFields();
            $userFields->userData = $this->data;
            $userFields->postName = 'savesettings';
            $userFields->formname = 'editAccountFrm';
            $userFields->postValue = $locale['ME_450'];
            $userFields->displayValidation = fusion_get_settings('display_validation');
            $userFields->plugin_folder = [ INCLUDES . 'user_fields/', INFUSIONS ];
            $userFields->plugin_locale_folder = LOCALE . LOCALESET . 'user_fields/';
            $userFields->showAdminPass = TRUE;
            $userFields->skipCurrentPass = TRUE;
            $userFields->registration = FALSE;
            $userFields->admin_mode = TRUE;
            $userFields->displayTerms = FALSE;
            $userFields->method = 'input';

            // Reset passwords
            if ( admin_post('adminreset') ) {
                ( new UserFields\Reset() )->doReset();
            }

            if (check_post('delmember')) {
                $userInput->doRemove();
            }

            // User actions
            if ( admin_post('savesettings') || check_post('action') ) {
                $userInput->saveUpdate();
            }

            // Impersonate modal
            $this->displayImpersonate();

            $userInput->moderateUser();

            $userInput->updateAvatar();

            $userInput->updateCover();

            $userFields->displayProfileInput();

        }
        else {
            redirect(ADMIN_CURRENT_DIR);
        }
    }

    // resend account credentials need email templates.
    private function resendAccount() {

        if ( get("lookup", FILTER_VALIDATE_INT) ) {
            Members_Profile::resend_email();
        }
        else {
            redirect(FUSION_SELF . $aidlink);
        }
    }

    private function activateAccount() {

        if ( get("lookup") && get("code") ) {
            Members_Profile::activate_user();
        }
        else {
            redirect(FUSION_SELF . $aidlink);
        }
    }


}
