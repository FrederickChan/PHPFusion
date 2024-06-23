<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Jupiter Admin
| Filename: AdminHelper.php
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

use PHPFusion\Admins;
use PHPFusion\BreadCrumbs;

/**
 * Admin helper class
 */
class AdminHelper extends Admins {

    private $custom_section_icon = [
        'fad fa-dashboard',
        'fad fa-books',
        'fad fa-id-badge',
        'fad fa-cog',
        'fad fa-wrench',
        'fad fa-box-full',
    ];

    private $custom_icons = [
        'C'    => 'fad fa-comments-alt', // Comments
        'CP'   => 'fad fa-edit', // Custom page
        'F'    => 'fad fa-comment-alt-lines', // Forum
        'FM'   => 'fad fa-folder', // File Manager
        'IM'   => 'fad fa-image', // Images
        'UL'   => 'fad fa-user-edit', // User Log
        'UF'   => 'fad fa-id-card-alt', // User Fields
        'UG'   => 'fad fa-users', // User Groups
        'MI'   => 'fad fa-taxi', // Migration Tool
        'M'    => 'fad fa-user', //Members
        'B'    => 'fad fa-user-secret', //Blacklist
        'APWR' => 'fad fa-user-lock', //Admin Password Reset
        'AD'   => 'fad fa-user-tie', // Administrators
        'MAIL' => 'fad fa-mailbox', // Email
        'ROB'  => 'fad fa-robot', //Robots
        'TS'   => 'fad fa-rocket-launch', // Theme
        'SM'   => 'fad fa-grin-beam', // Smileys
        'SL'   => 'fad fa-link', // Sitelinks
        'PI'   => 'fab fa-php', // PHP Info
        'PL'   => 'fad fa-anchor', // Permalinks
        'P'    => 'fad fa-window-alt', //Panels
        'I'    => 'fad fa-magnet', // Infusions
        'ERRO' => 'fad fa-bug', // Error
        'DB'   => 'fad fa-database',
        'BB'   => 'fad fa-star-shooting', // bbcode
        'SB'   => 'fad fa-image-polaroid', // banner
        'LANG' => 'fad fa-language', // Language Settings
        'S12'  => 'fad fa-lock', // Security
        'S9'   => 'fad fa-user-md', // User Management
        'S7'   => 'fad fa-comments', // PM
        'S6'   => 'fad fa-box', //Miscellaneous
        'S4'   => 'fad fa-lock', // Registration
        'S3'   => 'fad fa-cogs', // Theme Settings
        'S2'   => 'fad fa-clock', // Time and Date
        'S1'   => 'fad fa-tools', // Settings Main
    ];

    /* Constructor */
    private $buttons;

    public function __construct() {
        $this->setAdmin();
    }

    /* Admin pages */
    public function viewThemeAdminPages(): array {
        //$locale = fusion_get_locale();

        //
        //if (!empty($admin_pages)) {
        //    foreach ($admin_pages as $keys => $pages) {
        //        foreach ($pages as $index => $apage) {
        //            if (checkrights($apage['admin_rights'])) {
        //                if ($index != 5) {
        //                    $apage['admin_title'] = $locale[$apage['admin_rights']] ?? $apage['admin_title'];
        //                }
        //            }
        //        }
        //    }
        //}

        return $this->getAdminPages();
    }


    /* Admin sections */
    public function viewThemeAdminSections(): array {
        $admin_sections = $this->getAdminSections();
        if (!empty($admin_sections)) {
            foreach ($admin_sections as $index => $section) {
                $admin_sections[$index] = [
                    'title' => $section,
                    'icon'  => get_image($this->custom_section_icon[$index])
                ];
            }
        }

        unset($admin_sections[0]);
        unset($admin_sections[1]);

        return $admin_sections;
    }

    public function getSettingsURI() {
        return $this->settings_uri;
    }

    public function getDashboardURI() {
        return $this->dashboard_uri;
    }

    /**
     * Format breadcrumbs
     *
     * @return array
     */
    public function getAdminBreadcrumbs(): array {
        $breadcrumbs = BreadCrumbs::getInstance();
        $arr = $breadcrumbs->toArray();
        // Unset Home
        unset($arr[0]);

        return $arr;
    }

    /* Notices */
    public function getAdminNotices(): string {
        return $this->renderNotices(getNotices());
    }


    private function renderNotices($notices) {

        $messages = '';

        foreach ( $notices as $status => $notice ) {

            if ($status !='success') {
                $icon = 'lightbulb';
                if ( $status == 'warning' ) {
                    $icon = 'bell';
                }
                else if ( $status == 'danger' ) {
                    $icon = 'lightbulb-exclamation';
                }

                $messages .= "<div class='admin-message alert alert-" . $status . " alert-dismissible' role='alert'>";

                $messages .= "<button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span></button>";
                $messages .= '<div class="flex gap-15 ac"><i class="fal fa-' . $icon . ' fa-lg"></i><div>';
                $messages .= implode('<br/>', $notice);
                $messages .= "</div>\n</div>\n";
                $messages .= '</div>';

            }

        }

        return $messages;
    }

}
