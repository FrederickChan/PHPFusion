<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: bootstrap_include.php
| Author: meangczac (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Get bootstrap framework file paths
 *
 * @param        $part
 * @param string $version
 * @param bool $php
 * @return string
 */
function get_bootstrap($part, $version = '3', $php = FALSE) {
    static $framework_paths = [];

    if (empty($framework_paths)) {

        if ($version < 3) {
            $version = 3;
        } else if ($version > 5) {
            $version = 5;
        }
        $version = 'v' . $version;

        // Headers and footers
        require_once __DIR__ . '/' . $version . '/index.php';

        $_dir = __DIR__ . '/' . $version . '/';

        $framework_paths['php'] = [
            'showsublinks' => ['dir' => $_dir, 'file' => 'navbar.php'],
            'form_inputs' => ['dir' => $_dir, 'file' => 'dynamics.php'],
            'collapse' => ['dir' => $_dir, 'file' => 'collapse.php'],
        ];

        // Template paths
        $framework_paths['twig'] = [
            'showsublinks' => ['dir' => __DIR__ . '/' . $version . '/utils/', 'file' => 'navbar.twig'],
            'form_inputs' => ['dir' => __DIR__ . '/' . $version . '/', 'file' => 'dynamics.twig'],
            'modal' => ['dir' => __DIR__ . '/' . $version . '/utils/', 'file' => 'modal.twig'],
            'register' =>['dir' => TEMPLATES.'html/public/', 'file' => 'register.twig'],
            'login' => ['dir' => TEMPLATES . 'html/public/', 'file' => 'login.twig'],
            'login_auth' => ['dir' => TEMPLATES . 'html/public/', 'file' => 'login_auth.twig'],
            // Profile
            'up_notify' => ['dir' => TEMPLATES . 'html/public/profile_settings/', 'file' => 'notify.twig'],
            'up_close' => ['dir' => TEMPLATES . 'html/public/profile_settings/', 'file' => 'close.twig'],
            'up_privacy' => ['dir' => TEMPLATES . 'html/public/profile_settings/', 'file' => 'privacy.twig'],
            'up_home'=> ['dir' => TEMPLATES . 'html/public/profile_settings/', 'file' => 'home.twig'],
            // Home settings
            'up_home_details' => ['dir' => TEMPLATES . 'html/public/profile_settings/home/', 'file' => 'details.twig'],
            'up_home_password' => ['dir' => TEMPLATES . 'html/public/profile_settings/home/', 'file' => 'password.twig'],
            'up_home_adm_password' => ['dir' => TEMPLATES . 'html/public/profile_settings/home/', 'file' => 'adm_password.twig'],

            // Privacy Settings
            'up_data' => ['dir' => TEMPLATES . 'html/public/profile_settings/privacy/', 'file' => 'data.twig'],
            'up_privacy_twofactor' => ['dir' => TEMPLATES . 'html/public/profile_settings/privacy/', 'file' => 'two_factor.twig'],
            'up_privacy_settings' => ['dir' => TEMPLATES . 'html/public/profile_settings/privacy/', 'file' => 'settings.twig'],
            'up_privacy_pm' => ['dir' => TEMPLATES . 'html/public/profile_settings/privacy/', 'file' => 'messaging.twig'],
            'up_privacy_login' => ['dir' => TEMPLATES . 'html/public/profile_settings/privacy/', 'file' => 'login.twig'],
            'up_privacy_blacklist' => ['dir' => TEMPLATES . 'html/public/profile_settings/privacy/', 'file' => 'blacklist.twig'],
        ];
    }

    $_type = $php ? 'php' : 'twig';

//    static $debug;
//    if (empty($debug)) {
//        $debug = $framework_paths['twig'];
//        print_p($debug);
//    }

    return $framework_paths[$_type][$part] ?? '';
}

if (defined('BOOTSTRAP')) {

    /**
     * Load bootstrap
     * BOOTSTRAP - version number
     */
    get_bootstrap('load', BOOTSTRAP);

    /**
     * @uses bootstrap_header()
     */
    fusion_add_hook('fusion_header_include', 'bootstrap_header');

    /**
     * @uses bootstrap_footer()
     */
    fusion_add_hook('fusion_footer_include', 'bootstrap_footer');

    function get_theme_template($component) {
        static $framework_paths = [];
        if (empty($paths)) {
            $framework_paths = fusion_filter_hook('fusion_theme_templates');
            $framework_paths = flatten_array($framework_paths);
        }

        return $framework_paths[$component] ?? '';
    }


    /**
     * System template callback function
     *
     * @param $component
     * @param $info
     *
     * @return string
     */
    function fusion_get_template($component, $info) {

        if ($path = get_bootstrap($component)) {

            // check for theme port, for now only support php
            if ($theme_path = get_theme_template($component)) {
                $path = $theme_path;
            }

            // Get twig templates
            return fusion_render($path['dir'], $path['file'], $info, defined('FUSION_DEVELOPMENT'));

        } else if ($path = get_bootstrap($component, 'auto', TRUE)) {
            // Get php templates
            require_once $path['dir'] . $path['file'];

            if ($callback = call_user_func($component, $info)) {
                return $callback;
            }
        }

        return 'This template ' . $component . ' is not supported';
    }
}
