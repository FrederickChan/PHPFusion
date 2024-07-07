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
    private $buttons;
    private array $filters;
    private $title;
    private $pageNav;
    private $fullWidth;


    public function __construct() {

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
            'content' => CONTENT,

            // All hooks malfunctioned and did not register
            'admin_buttons' => $this->getButtons(),
//            'admin_filters' => $this->getFilters(),
//            'admin_page_title' => $this->getTitle(),
//            'admin_page_nav' => $this->getPageNav(), //fusion_filter_hook('pf_admin_left_nav')[0] ?? '', // this one will pass content
//            'main_width_class' => $this->getFullWidth(),

            'footer_errors' => $this->footerErrors($errors, $new_errors),
            'new_errors' => $new_errors,
        );
        // Debug

        return fusion_render(THEMES . "templates/acp/", 'theme.twig', $info, TRUE);
    }


    public function getFullWidth() {
        return $this->pageNav;
    }

    public function setFullWidth($value) {
        $this->fullWidth = $value;
    }

    public function getPageNav() {
        return $this->pageNav;
    }

    public function setPageNav($value) {
        $this->pageNav = $value;
    }

    public function setTitle($value) {
        $this->title = $value;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getFilters() {
        return $this->filters;
    }

    public function setFilters($value) {
        $this->filters[] = $value;
    }

    public function setButtons($value) {
        $this->buttons = $value;
    }

    public function getButtons() {
        return $this->buttons;
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
        add_to_title('Login');

        $sitebanner = str_replace('images/', IMAGES, fusion_get_settings('sitebanner'));

        $info = array(
            'openform' => openform('adminLoginfrm'),
            'closeform' => closeform(),
            'settings' => fusion_get_settings(),
            'sitebanner' => $sitebanner,
            'password_input' => form_text('admin_password','', '',
                array('placeholder'=>'Login password',
                    'required' => TRUE,
                    'type' => 'password')
            ),
            'button' => form_button('admin_login', 'Sign In', 'admin_login'),
        );

        echo fusion_render(THEMES . "templates/acp/", 'login.twig', $info, TRUE);
    }

}
