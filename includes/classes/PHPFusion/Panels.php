<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Panels.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

use PHPFusion\Rewrite\Router;

class Panels {
    const PANEL_LEFT = 1;
    const PANEL_U_CENTER = 2;
    const PANEL_L_CENTER = 3;
    const PANEL_RIGHT = 4;
    const PANEL_AU_CENTER = 5;
    const PANEL_BL_CENTER = 6;
    const PANEL_USER1 = 7;
    const PANEL_USER2 = 8;
    const PANEL_USER3 = 9;
    const PANEL_USER4 = 10;
    private static $panel_instance = NULL;
    private static $panel_name = [
        ['name' => 'LEFT', 'side' => 'left'],
        ['name' => 'U_CENTER', 'side' => 'upper'],
        ['name' => 'L_CENTER', 'side' => 'lower'],
        ['name' => 'RIGHT', 'side' => 'right'],
        ['name' => 'AU_CENTER', 'side' => 'aupper'],
        ['name' => 'BL_CENTER', 'side' => 'blower'],
        ['name' => 'USER1', 'side' => 'user1'],
        ['name' => 'USER2', 'side' => 'user2'],
        ['name' => 'USER3', 'side' => 'user3'],
        ['name' => 'USER4', 'side' => 'user4']
    ];

    private static $panel_excluded = [];
    private static $panels_cache = [];
    private static $available_panels = [];
    private static $hide_all = FALSE;

    private $panel_data;

    /**
     * @param bool $set_info
     *
     * @return Panels|null
     */
    public static function getInstance($set_info = TRUE) {
        if (self::$panel_instance === NULL) {
            self::$panel_instance = new static();
            if ($set_info) {
                self::cachePanels();
            }
        }

        return self::$panel_instance;
    }

    /**
     * Cache panels
     *
     * @return array
     */
    public static function cachePanels() {

        $p_result = dbquery("SELECT * FROM ".DB_PANELS." WHERE panel_status=:panel_status ORDER BY panel_side, panel_order", [
            ':panel_status' => 1
        ]);

        if (dbrows($p_result)) {
            $panel_side = 0;
            $panel_order = 0;
            while ($panel_data = dbarray($p_result)) {

                if ($panel_data['panel_side'] !== $panel_side) {
                    $panel_data['panel_order'] = 0;
                }

                $panel_side = $panel_data['panel_side'];
                $panel_order = $panel_order + 1;
                $panel_data['panel_order'] = $panel_order;

                if (multilang_table('PN')) {
                    $p_langs = explode('.', $panel_data['panel_languages']);
                    if (checkgroup($panel_data['panel_access']) && in_array(LANGUAGE, $p_langs)) {
                        self::$panels_cache[$panel_data['panel_side']][$panel_data['panel_order']] = $panel_data;
                    }
                } else {
                    if (checkgroup($panel_data['panel_access'])) {
                        self::$panels_cache[$panel_data['panel_side']][$panel_data['panel_order']] = $panel_data;
                    }
                }
            }
        }

        return self::$panels_cache;
    }

    /**
     * Add Panel to List of Panels to be cached
     *
     * @param $panel_name
     * @param $panel_content
     * @param $panel_side
     * @param $panel_access
     * @param $panel_order
     */
    public static function addPanel($panel_name, $panel_content, $panel_side, $panel_access = USER_LEVEL_PUBLIC, $panel_order = 0) {
        if (checkgroup($panel_access)) {
            self::$panels_cache[$panel_side][] = [
                'panel_id'          => str_replace(" ", "_", $panel_name).'-'.$panel_side,
                'panel_content'     => $panel_content,
                'panel_side'        => $panel_side,
                'panel_filename'    => '',
                'panel_type'        => 'custom',
                'panel_access'      => $panel_access,
                'panel_status'      => 1,
                'panel_display'     => 1,
                'panel_url_list'    => '',
                'panel_restriction' => 3,
                'panel_languages'   => implode('.', fusion_get_enabled_languages()),
                'panel_order'       => $panel_order,
                'panel_php_exe'     => 0
            ];
        }
    }

    /**
     * Display a panel given a panel id
     *
     * @param int $panel_id
     *
     * @return string
     */
    public static function displayPanel($panel_id) {
        $locale = fusion_get_locale();
        $html = "";
        if (!empty(self::$panels_cache)) {
            $panels = flatten_array(self::$panels_cache);
            foreach ($panels as $panelData) {
                if ($panelData['panel_id'] == $panel_id) {
                    ob_start();
                    if ($panelData['panel_type'] == "file") {
                        if (is_file(INFUSIONS.$panelData['panel_filename']."/".$panelData['panel_filename'].".php")) {
                            include INFUSIONS.$panelData['panel_filename']."/".$panelData['panel_filename'].".php";
                        } else {
                            if (iADMIN) {
                                addnotice('warning', sprintf($locale["global_130"], $panelData["panel_name"]));
                            }
                        }
                    } else {
                        if ($panelData['panel_php_exe']) {
                            eval(stripslashes($panelData['panel_content']));
                        } else {
                            echo parse_text($panelData['panel_content']);
                        }
                    }
                    $html = ob_get_contents();
                    ob_end_clean();

                    return $html;
                }
            }
        }

        return $html;
    }

    /**
     * Get excluded panel list
     *
     * @return array
     */
    public static function getPanelExcluded() {
        return self::$panel_excluded;
    }

    /**
     * Get all available panels
     *
     * @param array $excluded_panels
     *
     * @return array
     */
    public static function getAvailablePanels($excluded_panels = []) {
        // find current installed panels.
        if (empty(self::$available_panels)) {
            $temp = opendir(INFUSIONS);
            self::$available_panels['none'] = fusion_get_locale('469');
            while ($folder = readdir($temp)) {
                if (!in_array($folder, [
                        ".",
                        ".."
                    ]) && strstr($folder, "_panel")
                ) {

                    if (is_dir(INFUSIONS.$folder)) {
                        self::$available_panels[$folder] = $folder;
                    }
                    if ((!empty($excluded_panels) && in_array($folder, $excluded_panels))) {
                        unset(self::$available_panels[$folder]);
                    }
                }
            }
            closedir($temp);
        }

        return self::$available_panels;
    }

    /**
     * Hide panel
     *
     * @param $side - 'LEFT', 'RIGHT', 'U_CENTER', 'L_CENTER', 'AU_CENTER', 'BL_CENTER', 'USER1', 'USER2', 'USER3', 'USER4'
     */
    public function hidePanel($side) {
        foreach (self::$panel_name as $p_key => $p_side) {
            if ($p_side['name'] == $side) {
                self::$panel_excluded[$p_key + 1] = $side;
            }
        }
    }

    /**
     * Hide all panels
     */
    public function hideAll() {
        self::$hide_all = TRUE;
    }

    /**
     * @return bool
     */
    private function checkPanelVisibility() {
        $settings = fusion_get_settings();

        $url_arr = explode("\r\n", $this->panel_data['panel_url_list']);
        $url = [];
        $script_url = '/'.PERMALINK_CURRENT_PATH;
        if ($settings['site_seo']) {
            $params = http_build_query(Router::getRouterInstance()->getFileParams());
            $path = Router::getRouterInstance()->getFilePath();
            $script_url = '/'.(!empty($path) ? $path : PERMALINK_CURRENT_PATH).($params ? "?" : '').$params;
        }

        foreach ($url_arr as $url_list) {
            $url[] = $url_list;
            if ($this->wildcardMatch($script_url, $url_list)) {
                $url[] = $script_url;
            }
        }

        switch ($this->panel_data['panel_restriction']) {
            case 0: // Include on these pages only
                //  url_list is set, and panel_restriction set to 0 (Include) and current page matches url_list.
                if (!empty($this->panel_data['panel_url_list']) && in_array($script_url, $url)) {
                    return TRUE;
                }
                break;
            case 1: // Exclude on these pages only
                //  url_list is set, and panel_restriction set to 1 (Exclude) and current page does not match url_list.
                if (!empty($this->panel_data['panel_url_list']) && !in_array($script_url, $url)) {
                    return TRUE;
                }
                break;
            case 2: // Display on Opening Page only
                if ($settings['opening_page'] == 'index.php' && $script_url == '/' || $script_url == '/'.$settings['opening_page']) {
                    return TRUE;
                } else if (PERMALINK_CURRENT_PATH === $settings['opening_page']) {
                    return TRUE;
                }
                break;
            case 3: // Display panel on all pages
                //  url_list must be blank
                if (empty($p_data['panel_url_list'])) {
                    return TRUE;
                }
                break;
            default:
                break;
        }

        return FALSE;
    }
    /**
     * Cache and generate Panel Constants
     */
    public function getSitePanel() {
        if (self::$hide_all == TRUE) {
            return NULL;
        }

        if (empty(self::$panels_cache)) {
            self::cachePanels();
        }

        $locale = fusion_get_locale();

        // Add admin message
        $admin_mess = "<noscript><div class='alert alert-danger noscript-message admin-message'><strong>".$locale['global_303']."</strong></div>\n</noscript>\n<!--error_handler-->\n";

        add_to_head($admin_mess);

        // Optimize this part to cache_panels
        foreach (self::$panel_name as $p_key => $p_side) {

            if (isset(self::$panels_cache[$p_key + 1]) || defined("ADMIN_PANEL")) {
                ob_start();

                if (!defined("ADMIN_PANEL")) {

                    // reorder panel
                    self::$panels_cache[$p_key + 1] = sorter(self::$panels_cache[$p_key + 1], 'panel_order');

                    if (self::checkPanelStatus($p_side['side']) && !isset(self::$panel_excluded[$p_key + 1])) {

                        foreach (self::$panels_cache[$p_key + 1] as $this->panel_data) {

                            if ($this->checkPanelVisibility() === TRUE) { // Prevention of rendering unnecessary files

                                if ($this->panel_data['panel_type'] == "file") {

                                    $file_path = INFUSIONS.$this->panel_data['panel_filename']."/".$this->panel_data['panel_filename'].".php";

                                    if (is_file($file_path)) {
                                        include $file_path;
                                    } else {
                                        if (iADMIN) {
                                            addnotice('warning', sprintf($locale['global_130'], $this->panel_data['panel_name']));
                                        }
                                    }
                                } else {
                                    if ($this->panel_data['panel_php_exe']) {
                                        // This is slowest of em all.
                                        $panelStart = '';
                                        $panelEnd = '';
                                        if ($this->panel_data['panel_type'] == 'custom') {
                                            if (!strpos($this->panel_data['panel_content'], '<?php')) {
                                                //$panelContent .= "<?php ".PHP_EOL;
                                                $panelStart .= "echo \"".PHP_EOL;
                                            }
                                            if (!strpos($this->panel_data['panel_content'], '?>')) {
                                                $panelEnd .= "\";".PHP_EOL;
                                            }
                                            $this->panel_data['panel_content'] = str_replace("\"", "\\'", $this->panel_data['panel_content']);
                                        }
                                        $panelContent = $panelStart.stripslashes($this->panel_data['panel_content']).$panelEnd;
                                        eval($panelContent);

                                    } else {
                                        echo stripslashes($this->panel_data['panel_content']);
                                    }
                                }
                            }
                        }

                        unset($this->panel_data);

                        if (multilang_table("PN")) {
                            unset($p_langs);
                        }
                    }
                }

                $content = ob_get_contents();

                $html = "<div class='content".ucfirst($p_side['side'])."'>";
                $html .= $content;
                $html .= "</div>\n";

                define($p_side['name'], (!empty($content) ? $html : ''));
                ob_end_clean();

            } else {
                // This is in administration
                define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : ''));
            }
        }

    }

    /**
     * Check panel exclusions in certain page, which will be dropped sooner or later
     * Because we will need page composition database soon
     *
     * @param string $side
     *
     * @return bool
     */
    public static function checkPanelStatus($side) {
        $settings = fusion_get_settings();

        $exclude_list = "";
        if ($side == "left") {
            if ($settings['exclude_left'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_left']);
            }
            if (defined("LEFT_OFF")) {
                $exclude_list = FUSION_SELF;
            }
        } else if ($side == "upper") {
            if ($settings['exclude_upper'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_upper']);
            }
        } else if ($side == "aupper") {
            if ($settings['exclude_aupper'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_aupper']);
            }
        } else if ($side == "lower") {
            if ($settings['exclude_lower'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_lower']);
            }
        } else if ($side == "blower") {
            if ($settings['exclude_blower'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_blower']);
            }
        } else if ($side == "right") {
            if ($settings['exclude_right'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_right']);
            }
        } else if ($side == "user1") {
            if ($settings['exclude_user1'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user1']);
            }
        } else if ($side == "user2") {
            if ($settings['exclude_user2'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user2']);
            }
        } else if ($side == "user3") {
            if ($settings['exclude_user3'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user3']);
            }
        } else if ($side == "user4") {
            if ($settings['exclude_user4'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user4']);
            }
        }

        if (is_array($exclude_list)) {
            if (fusion_get_settings('site_seo')) {
                $params = http_build_query(Router::getRouterInstance()->getFileParams());
                $path = Router::getRouterInstance()->getFilePath();
                $file_path = '/'.(!empty($path) ? $path : PERMALINK_CURRENT_PATH).($params ? "?" : '').$params;
                $script_url = explode("/", $file_path);
            } else {
                $script_url = explode("/", '/'.PERMALINK_CURRENT_PATH);
            }

            $url_count = count($script_url);
            $base_url_count = substr_count(BASEDIR, "../") + 1;
            $current_url = "";
            while ($base_url_count != 0) {
                $current = $url_count - $base_url_count;
                $current_url .= "/".(!empty($script_url[$current]) ? $script_url[$current] : '');
                $base_url_count--;
            }

            $url = [];
            foreach ($exclude_list as $url_list) {
                $url[] = $url_list;
                if (self::getInstance()->wildcardMatch($current_url, $url_list)) {
                    $url[] = $current_url;
                }
            }


            return !in_array($current_url, $url);
        } else {
            return TRUE;
        }
    }

    /**
     * @param $source
     * @param $pattern
     *
     * @return false|int
     */
    private function wildcardMatch($source, $pattern) {
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        return preg_match('/^'.$pattern.'$/i', $source);
    }

    /**
     * Hide panel
     *
     * @param $side - 'LEFT', 'RIGHT', 'U_CENTER', 'L_CENTER', 'AU_CENTER', 'BL_CENTER', 'USER1', 'USER2', 'USER3', 'USER4'
     *
     * @deprecated use hidePanel()
     */
    public function hide_panel($side) {
        $this->hidePanel($side);
    }
}
