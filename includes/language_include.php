<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: language_include.php
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
/**
 * Define constants for site language.
 *
 * @param string $lang The name of the language.
 */
function define_site_language($lang) {

    if (valid_language($lang)) {
        define('LANGUAGE', $lang);
        define('LOCALESET', $lang . '/');
    }
}

/**
 * Get the language package shortcode within global.php file
 *
 * @param $language_pack - // representation of folder name
 *
 * @return string
 */
function get_language_code($language_pack) {

    $locale = [];
    try {
        include LOCALE . $language_pack . '/global.php';
        return $locale['short_lang_name'] ?? $language_pack;
    } catch (Exception $e) {
        debug_print_backtrace();
        die('Stopping process');
    }
}

/**
 * Set the requested language.
 *
 * @param string $lang The name of the language.
 */
function set_language($lang) {

    $userdata = fusion_get_userdata();
    if (valid_language($lang)) {
        if (iMEMBER) {
            dbquery("UPDATE " . DB_USER_SETTINGS . " SET user_language='" . $lang . "' WHERE user_id='" . $userdata['user_id'] . "'");
        } else {
            $rows = dbrows(dbquery("SELECT user_language FROM " . DB_LANGUAGE_SESSIONS . " WHERE user_ip='" . USER_IP . "'"));
            if ($rows != 0) {
                dbquery("UPDATE " . DB_LANGUAGE_SESSIONS . " SET user_language='" . $lang . "', user_datestamp='" . time() . "' WHERE user_ip='" . USER_IP . "'");
            } else {
                dbquery("INSERT INTO " . DB_LANGUAGE_SESSIONS . " (user_ip, user_language, user_datestamp) VALUES ('" . USER_IP . "', '" . $lang . "', '" . time() . "');");
            }
            // Sanitize guest sessions occasionally
            dbquery("DELETE FROM " . DB_LANGUAGE_SESSIONS . " WHERE user_datestamp<'" . (time() - (86400 * 60)) . "'");
        }
    }
}

/**
 * Check if a given language is valid or if exists.
 * Checks whether a language can be found in enabled languages array.
 * Can also be used to check whether a language actually exists.
 *
 * @param string $lang The name of the language.
 * @param bool $file_check Intended to be used when enabling languages in Admin Panel.
 *
 * @return bool
 */
function valid_language($lang, $file_check = FALSE) {

    $enabled_languages = fusion_get_enabled_languages(TRUE);
    if (preg_match("/^([a-z0-9_-]){2,50}$/i", $lang) && ($file_check ? file_exists(LOCALE . $lang . "/global.php") : isset($enabled_languages[$lang]))) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Check language folder name and file
 *
 * @param $lang
 *
 * @return bool
 */
function check_language($lang) {
    return preg_match("/^([a-z0-9_-]){2,50}$/i", $lang) && is_file(LOCALE . $lang . "/global.php");

}

/**
 * Get language switch arrays.
 *
 * @return array
 */
function fusion_get_language_switch() {

    static $language_switch = [];
    if (empty($language_link)) {
        $enabled_languages = fusion_get_enabled_languages();
        foreach ($enabled_languages as $language => $language_name) {
            $link = clean_request('lang=' . $language, ['lang'], FALSE);
            $language_switch[$language] = ["language_name" => $language_name, "language_icon_s" => BASEDIR . "locale/$language/$language-s.png", "language_icon" => BASEDIR . "locale/$language/$language.png", "language_link" => $link,];
        }
    }

    return $language_switch;
}

/**
 * Get the array of enabled languages.
 *
 * @return array
 */
function fusion_get_enabled_languages($skip_translate = FALSE) {

    $settings = fusion_get_settings();
    $enabled_languages = [];

    if (isset($settings['enabled_languages'])) {
        $values = explode('.', $settings['enabled_languages']);
        foreach ($values as $language_name) {
            $enabled_languages[$language_name] = $skip_translate ? $language_name : translate_lang_names($language_name, TRUE);
        }
    }
    return $enabled_languages;
}

/**
 * Get the array of detected languages.
 *
 * @return array
 */
function fusion_get_detected_languages() {

    static $detected_languages = NULL;
    if ($detected_languages === NULL) {
        $all_languages = makefilelist(LOCALE, ".svn|.|..", TRUE, "folders");
        foreach ($all_languages as $language_name) {
            $detected_languages[$language_name] = translate_lang_names($language_name);
        }
    }

    return $detected_languages;
}
