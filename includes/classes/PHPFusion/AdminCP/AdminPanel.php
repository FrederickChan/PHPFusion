<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Jupiter Admin
| Filename: AdminPanel.php
| Author: meangczac
|+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\AdminCP;

use PHPFusion\Errors;

/*
 * Adminpanel
 */

class AdminPanel {

    /* Helper object */
    private $helper;

    /* Constructs */
    /**
     * @var Errors
     */
    private $errorClass;


    public function __construct() {

//        fusion_load_script(THEMES."templates/acp/styles.css", "css");
//        fusion_load_script(INCLUDES."jquery/admin.js");

        echo "<noscript>";
        echo "<style>
        .pf-side-header .side-header-btn { display:none !important; }
        .pf-side-body {display:block!important;}
        .pf-admin-buttons {display:block !important;}
        </style>";
        echo "</noscript>";

        $this->helper = new AdminHelper();

    }

    private static $instance;

    public static function getInstance(): AdminPanel {

        if (empty(self::$instance)) {
            self::$instance = new AdminPanel();
        }

        return self::$instance;
    }


    /* Admin panel theme */
    public function viewTheme() {

        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();
        $this->errorClass = Errors::getInstance();
        $errors = [];
//        $errors = $this->errorClass->getErrors();
        $new_errors = [];
//        $new_errors = $this->errorClass->getNewErrors();

        $sitebanner = '';
        if (!empty($settings['sitebanner'])) {
            $sitebanner = str_replace('images/', IMAGES, fusion_get_settings('sitebanner'));
            $sitebanner = '<img src="' . IMAGES . $sitebanner . '" alt="">';
        }

        $breadcrumbs = $this->helper->getAdminBreadcrumbs();

        $info = array(
            'admin_pages' => $this->helper->getAdminPages(),
            'admin_sections' => $this->helper->viewThemeAdminSections(),
            'admin_avatar' => display_avatar($userdata, '30px', 'pf-nav-avatar', FALSE, 'img-circle'),
            'userdata' => $userdata,
            'admin_breadcrumbs' => $breadcrumbs,
            'settings' => fusion_get_settings(),
            'sitebanner' => $sitebanner,
            'profile_uri' => BASEDIR . 'profile.php?lookup=' . $userdata['user_id'],
            'api_url' => fusion_get_locale('', array(LOCALE . LOCALESET . 'api.php')),
            'signout_uri' => FUSION_REQUEST . '&logout',
            'settings_uri' => $this->helper->getSettingsURI(),
            'dashboard_uri' => $this->helper->getDashboardURI(),
            'admin_notices' => $this->helper->getAdminNotices(),
            'admin_buttons' => fusion_filter_hook('pf_admin_buttons') ?? '',
            'admin_filters' => fusion_filter_hook('pf_admin_filters')[0] ?? '',
            'admin_page_title' => fusion_filter_hook('pf_admin_page_title')[0] ?? '',
            'admin_page_nav' => fusion_filter_hook('pf_admin_left_nav')[0] ?? '', // this one will pass content
            'content' => CONTENT,
            'main_width_class' => fusion_filter_hook('pf_admin_full_width')[0] ?? '',
            'footer_errors' => $this->footerErrors($errors, $new_errors),
            'new_errors' => $new_errors,
        );

        echo fusion_render(THEMES . "templates/acp/", 'theme.twig', $info, TRUE);
    }

    /**
     * @param $errors
     * @param $new_errors
     *
     * @return string
     */
    private function footerErrors($errors, $new_errors): string {

        if (iADMIN && checkrights("ERRO") && count($new_errors)) {
            $locale = fusion_get_locale();

            // Modal
            $modal = openmodal('tbody',
                $locale['ERROR_464'],
                ['class' => 'modal-lg modal-center zindex-boost errorlogmodal', 'button_id' => 'footer_debug']);
            $modal .= $this->errorClass->getErrorLogs();
            $modal .= closemodal();
            add_to_footer($modal);

            return '<a id="footer_debug"><i class="far fa-bug"></i><span class="error-badge badge">' . count($new_errors) . '</span></a>';
        }

        return '';
    }

    /* Dashboard */
    public function viewDashboard() {
        return fusion_render(THEMES . "templates/acp/", 'dashboard.twig', array(), defined("FUSION_DEVELOPMENT"));
    }

    /* Admin login */
    public function viewLogin() {

        $sitebanner = str_replace('images/', IMAGES, fusion_get_settings('sitebanner'));

        $info = [
            'openform' => openform('adminLoginfrm', 'POST'),
            'closeform' => closeform(),
            'settings' => fusion_get_settings(),
            'sitebanner' => $sitebanner,
            'password_input' => form_text('admin_password',
                'Password',
                '',
                ['required' => TRUE, 'type' => 'password']),
            'button' => form_button('admin_login', 'Sign in', 'admin_login'),
        ];

        echo fusion_render(THEMES . "templates/acp/", 'login.twig', $info, TRUE);
    }

}
