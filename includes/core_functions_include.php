<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: core_functions_include.php
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
defined('IN_FUSION') || exit;

use Defender\Token;
use PHPFusion\Authenticate;
use PHPFusion\ImageRepo;
use PHPFusion\QuantumFields;

/**
 * Get currency symbol by using a 3-letter ISO 4217 currency code
 * Note that if INTL pecl package is not installed, signs will degrade to ISO4217 code itself
 *
 * @param string $iso 3-letter ISO 4217
 * @param bool $description Set to false for just symbol
 *
 * @return array|string Array of currencies or string with one currency.
 */
function fusion_get_currency($iso = NULL, $description = TRUE) {

    $locale = fusion_get_locale('', LOCALE . LOCALESET . "currency.php");

    static $__currency = [];

    if (empty($__currency)) {
        // Euro Exceptions list
        $currency_exceptions = ["ADF" => "EUR", "ATS" => "EUR", "BEF" => "EUR", "CYP" => "EUR", "DEM" => "EUR", "EEK" => "EUR", "ESP" => "EUR", "FIM" => "EUR", "FRF" => "EUR", "GRD" => "EUR", "IEP" => "EUR", "ITL" => "EUR", "KZT" => "EUR", "LTL" => "EUR", "LUF" => "EUR", "LVL" => "EUR", "MCF" => "EUR", "MTL" => "EUR", "NLG" => "EUR", "PTE" => "EUR", "RUB" => "EUR", "SIT" => "EUR", "SKK" => "EUR", "SML" => "EUR", "VAL" => "EUR", "DDM" => "EUR", "ESA" => "EUR", "ESB" => "EUR",];
        foreach (array_keys($locale['currency']) as $country_iso) {
            $c_iso = !empty($currency_exceptions[$country_iso]) ? $currency_exceptions[$country_iso] : $country_iso;
            $c_symbol = (!empty($locale['currency_symbol'][$c_iso]) ? html_entity_decode($locale['currency_symbol'][$c_iso], ENT_QUOTES, $locale['charset']) : $c_iso);
            $c_text = $locale['currency'][$c_iso];
            $__currency[$country_iso] = $description ? $c_text . " ($c_symbol)" : $c_symbol;
        }
    }

    return $iso === NULL ? $__currency : (isset($__currency[$iso]) ? $__currency[$iso] : NULL);
}

/**
 * Check if a given theme exists and is valid.
 *
 * @param string $theme The theme folder you want to check.
 *
 * @return bool False if the theme does not exist and true if it does.
 */
function theme_exists($theme) {

    if ($theme == "Default") {
        $theme = fusion_get_settings('theme');
    }

    return is_string($theme) and preg_match("/^([a-z0-9_-]){2,50}$/i", $theme)
        and file_exists(THEMES . $theme . "/theme.php");

}

/**
 * Set a valid theme.
 *
 * @param string $theme The theme folder you want to set.
 */
function set_theme($theme) {

    $locale = fusion_get_locale();
    if (defined("THEME")) {
        return;
    }
    if (theme_exists($theme)) {
        define("THEME", THEMES . ($theme == "Default" ? fusion_get_settings('theme') : $theme) . "/");

        return;
    }
    foreach (new GlobIterator(THEMES . '*') as $dir) {
        if ($dir->isDir() and theme_exists($dir->getBasename())) {
            define("THEME", $dir->getPathname() . "/");

            return;
        }
    }
    // Don't stop if we are in admin panel since we use different themes now
    $no_theme_message = str_replace("[SITE_EMAIL]", fusion_get_settings("siteemail"), $locale['global_301']);

    if (preg_match("/\/administration\//i", $_SERVER['PHP_SELF'])) {

        addnotice('danger', "<strong>" . $theme . " - " . $locale['global_300'] . ".</strong><br /><br />\n" . $no_theme_message);

    } else {

        echo "<strong>" . $theme . " - " . $locale['global_300'] . ".</strong><br /><br />\n";

        echo $no_theme_message;

        die();

    }
}

/**
 * Compress HTML lines
 *
 * @param $buffer
 *
 * @return array|string|string[]|null
 */
function compress($buffer) {
    $search = [
        '/\>[^\S ]+/s',
        '/[^\S ]+\</s',
        '/(\s)+/s',
    ];
    $replace = [
        '>',
        '<',
        '\\1',
    ];
    $buffer = preg_replace($search, $replace, $buffer);
    return $buffer;
}

/**
 * Generate random string.
 *
 * @param int $length The length of the string.
 * @param bool $letters_only Only letters.
 *
 * @return string
 */
function random_string($length = 6, $letters_only = FALSE) {

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($letters_only) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }

    return $random_string;
}


/**
 * Cache of all smileys from the database.
 *
 * @return array Array of all smileys.
 */
function cache_smileys() {
    return ImageRepo::cacheSmileys();
}

/**
 * Parse the smileys in string and display smiley codes as smiley images.
 *
 * @param string $message A string that should have parsed smileys.
 *
 * @return string String with parsed smiley codes as smiley images ready for display.
 */
function parsesmileys($message) {

    if (!preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php])#si", $message)) {
        foreach (cache_smileys() as $smiley) {
            $smiley_code = preg_quote($smiley['smiley_code'], '#');
            $smiley_image = get_image("smiley_" . $smiley['smiley_text']);
            $smiley_image = "<img class='smiley' style='width:20px;height:20px;' src='$smiley_image' alt='" . $smiley['smiley_text'] . "'>";
            $message = preg_replace("#$smiley_code#s", $smiley_image, $message);
        }
    }

    return $message;
}

/**
 * Show smiley's button which will insert the smileys to the given textarea and form.
 *
 * @param string $textarea The id of the textarea
 * @param string $form The form id in which the textarea is located.
 *
 * @return string  Option for users to insert smileys in a post by displaying the smiley's button.
 */
function displaysmileys($textarea, $form = "inputform") {

    $smileys = "";
    $i = 0;
    foreach (cache_smileys() as $smiley) {
        if ($i != 0 && ($i % 10 == 0)) {
            $smileys .= "<br />\n";
        }
        $i++;
        $img = get_image("smiley_" . $smiley['smiley_text']);
        $smileys .= "<img class='smiley m-2' style='width:20px;height:20px;' src='" . $img . "' alt='" . $smiley['smiley_text'] . "' title='" . $smiley['smiley_text'] . "' onclick=\"insertText('" . $textarea . "', '" . $smiley['smiley_code'] . "', '" . $form . "');\">\n";
    }

    return $smileys;
}

/**
 * Cache of all installed BBCodes from the database.
 *
 * @return array Array of all BBCodes.
 */
function cache_bbcode() {

    static $bbcode_cache = [];
    if (empty($bbcode_cache)) {
        $bbcode_cache = [];
        $result = cdquery('bbcodes_cache', "SELECT bbcode_name FROM " . DB_BBCODES . " ORDER BY bbcode_order");
        while ($data = cdarray($result)) {
            $bbcode_cache[] = $data['bbcode_name'];
        }
    }

    return $bbcode_cache;
}

/**
 * Parse and force image/ to own directory.
 * Neutralize all image dir levels and convert image to pf image folder
 *
 * @param string $data String to parse.
 * @param string $prefix_ Image folder.
 *
 * @return string Parsed string.
 */
function parse_image_dir($data, $prefix_ = "") {

    $str = str_replace("../", "", $data);

    return (string)$prefix_ ? str_replace("images/", $prefix_, $str) : str_replace("images/", IMAGES, $str);
}

/**
 * Parse BBCodes, smileys and any special characters to HTML string.
 *
 * @param string $value String with unparsed text.
 * @param array $options Array of options.
 *
 * @return string
 */
function parse_text($value, $options = []) {
    $default_options = [
        'parse_smileys' => TRUE, // Smiley parsing.
        'parse_bbcode' => TRUE, // BBCode parsing.
        'decode' => TRUE, // Decode HTML entities.
        'default_image_folder' => IMAGES, // Image folder for parse_image_dir().
        'add_line_breaks' => FALSE, // Allows nl2br().
        'descript' => TRUE, // Sanitize text.
        'parse_users' => TRUE // Create user @tags.
    ];

    $options += $default_options;

    $charset = fusion_get_locale('charset');
    $value = stripslashes($value);
    if ($options['descript']) {
        $value = descript($value);
        $value = htmlspecialchars_decode($value);
    }
    if ($options['default_image_folder']) {
        $value = parse_image_dir($value, $options['default_image_folder']);
    }
    if ($options['parse_bbcode']) {
        $value = parseubb($value);
    }
    if ($options['parse_smileys']) {
        $value = parsesmileys($value);
    }
    if ($options['add_line_breaks']) {
        $value = nl2br($value);
    }
    if ($options['parse_users']) {
        $value = fusion_parse_user($value);
    }
    if ($options['decode']) {
        $value = html_entity_decode(html_entity_decode($value, ENT_QUOTES, $charset));
        $value = encode_code($value);
    }

    return (string)$value;
}

/**
 * Parse BBCodes in the given string.
 *
 * @param string $text A string that contains the text to be parsed.
 * @param string $selected The names of the required bbcodes to parse, separated by |.
 * @param bool $descript Sanitize text.
 *
 * @return string Parsed string.
 */
function parseubb($text, $selected = "", $descript = TRUE) {

    if ($descript) {
        $text = descript($text, FALSE);
    }

    $bbcode_cache = cache_bbcode();
    $bbcodes = [];
    foreach ($bbcode_cache as $bbcode) {
        $bbcodes[$bbcode] = $bbcode;
    }

    if (!empty($bbcodes['code'])) {
        $move_to_top = $bbcodes['code'];
        unset($bbcodes['code']);
        array_unshift($bbcodes, $move_to_top);
    }

    $sel_bbcodes = [];

    if ($selected) {
        $sel_bbcodes = explode("|", $selected);
    }
    foreach ($bbcodes as $bbcode) {
        $locale_file = '';
        if (file_exists(LOCALE . LOCALESET . "bbcodes/" . $bbcode . ".php")) {
            $locale_file = LOCALE . LOCALESET . "bbcodes/" . $bbcode . ".php";
        } else if (file_exists(LOCALE . "English/bbcodes/" . $bbcode . ".php")) {
            $locale_file = LOCALE . "English/bbcodes/" . $bbcode . ".php";
        }
        if ($locale_file) {
            \PHPFusion\Locale::setLocale($locale_file);
        }
    }

    $locale = fusion_get_locale();

    foreach ($bbcodes as $bbcode) {
        if ($selected && in_array($bbcode, $sel_bbcodes)) {
            if (file_exists(INCLUDES . "bbcodes/" . $bbcode . "_bbcode_include.php")) {
                include(INCLUDES . "bbcodes/" . $bbcode . "_bbcode_include.php");
            }
        } else if (!$selected) {
            if (file_exists(INCLUDES . "bbcodes/" . $bbcode . "_bbcode_include.php")) {
                include(INCLUDES . "bbcodes/" . $bbcode . "_bbcode_include.php");
            }
        }
    }

    // Added to fix code sniffer reported error
    unset($locale);

    return $text;
}

/**
 * Hide email from robots that have JavaScript disabled, as it requires JavaScript to view email.
 * Create a "mailto" link for the email address
 *
 * @param string $email The email you want to hide from robots.
 * @param string $title The text of the link.
 * @param string $subject A subject for a mail message if someone opens a link, and it opens in the mail client.
 *
 * @return string If browser has JavaScript enabled, email will be displayed correctly,
 *                otherwise, it will be hidden and difficult for a robot to decrypt.
 */
function hide_email($email, $title = "", $subject = "") {

    if (preg_match("/^[-0-9A-Z_.]{1,50}@([-0-9A-Z_.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
        $enc_email = '';
        $parts = explode("@", $email);
        $email = $parts[0] . '@' . $parts[1];
        for ($i = 0; $i < strlen($email); $i++) {
            $enc_email .= '&#' . ord($email[$i]) . ';';
        }

        $MailLink = "<a href='mailto:" . $enc_email;
        if ($subject != "") {
            $MailLink .= "?subject=" . urlencode($subject);
        }
        $MailLink .= "'>" . (!empty($title) ? $title : $enc_email) . "</a>";

        $MailLetters = "";
        for ($i = 0; $i < strlen($MailLink); $i++) {
            $l = substr($MailLink, $i, 1);
            if (strpos($MailLetters, $l) === FALSE) {
                $p = rand(0, strlen($MailLetters));
                $MailLetters = substr($MailLetters, 0, $p) . $l . substr($MailLetters, $p, strlen($MailLetters));
            }
        }
        $MailLettersEnc = str_replace("\\", "\\\\", $MailLetters);
        $MailLettersEnc = str_replace("\"", "\\\"", $MailLettersEnc);
        $MailIndexes = "";
        for ($i = 0; $i < strlen($MailLink); $i++) {
            $index = strpos($MailLetters, substr($MailLink, $i, 1));
            $index += 48;
            $MailIndexes .= chr($index);
        }

        $id = 'e' . rand(1, 99999999);

        $MailIndexes = str_replace("\\", "\\\\", $MailIndexes);
        $MailIndexes = str_replace("\"", "\\\"", $MailIndexes);
        $res = "<span id='" . $id . "'></span>";
        $res .= "<script type='text/javascript'>";
        $res .= "ML=\"" . str_replace("<", "xxxx", $MailLettersEnc) . "\";";
        $res .= "MI=\"" . str_replace("<", "xxxx", $MailIndexes) . "\";";
        $res .= "ML=ML.replace(/xxxx/g, '<');";
        $res .= "MI=MI.replace(/xxxx/g, '<');";
        $res .= "OT=\"\";";
        $res .= "for(j=0;j < MI.length;j++){";
        $res .= "OT+=ML.charAt(MI.charCodeAt(j)-48);";
        $res .= "}var e=document.getElementById('" . $id . "');e.innerHTML += OT;";
        $res .= "</script>";

        return $res;
    } else {
        return $email;
    }
}

/**
 * Highlights given words in string.
 *
 * @param array $words The words to highlight.
 * @param string $subject Text that contains a word (s) that should be highlighted.
 *
 * @return string Words highlighted in the string.
 */
function highlight_words($words, $subject) {

    for ($i = 0, $l = count($words); $i < $l; $i++) {
        $word[$i] = str_replace(["\\", "+", "*", "?", "[", "^", "]", "$", "(", ")", "{", "}", "=", "!", "<", ">", "|", ":", "#", "-", "_"], "", $words[$i]);
        if (!empty($words[$i])) {
            $subject = preg_replace("#($words[$i])(?![^<]*>)#i", "<span style='background-color:yellow;color:#333;font-weight:bold;padding-left:2px;padding-right:2px;'>\${1}</span>", $subject);
        }
    }

    return $subject;
}


/**
 * Scan image files for malicious code.
 *
 * @param string $file Path to image.
 *
 * @return bool True or false, depending on whether the image is safe or not.
 */
function verify_image($file) {

    $txt = file_get_contents($file);
    $patterns = ['#\<\?php#i', '#&(quot|lt|gt|nbsp);#i', '#&\#x([0-9a-f]+);#i', '#&\#([0-9]+);#i', "#([a-z]*)=([\`\'\"]*)script:#iU", "#([a-z]*)=([\`\'\"]*)javascript:#iU", "#([a-z]*)=([\'\"]*)vbscript:#iU", "#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU", "#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU", "#</*(applet|link|style|script|iframe|frame|frameset)[^>]*>#i"];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $txt)) {
            return FALSE;
        }
    }

    return TRUE;
}

/**
 * Check the user has rights and redirect if the user does not have rights for the page.
 *
 * @param string $rights Rights you want to check for the administrator.
 * @param bool $debug For debugging purposes.
 */
function pageaccess($rights, $debug = FALSE) {

    $error = [];
    if ($debug) {
        print_p('Admin Panel mode');
    }
    if (!defined('iAUTH')) {
        $error[] = 'iAuth error';
    }
    if (!isset($_GET['aid'])) {
        $error[] = 'Aid link error';
    }
    if (iADMIN && !empty($_GET['aid'])) {
        if ($_GET['aid'] != iAUTH) {
            $error[] = 'Aidlink mismatch. ' . iAUTH . ' != ' . $_GET['aid'] . "<br/>";
            $error[] .= USER_IP;
        }
    } else {
        $error[] = "You are logged out while accessing admin panel";
    }
    if (!checkrights($rights)) {
        $error[] = 'Checkrights Error';
    }
    if (!empty($error)) {
        if ($debug) {
            print_p($error);
        } else {
            redirect(BASEDIR);
        }
    }
}

/**
 * Create a list of files or folders and store them in an array.
 *
 * @param string $folder Path to folder.
 * @param string $filter The names of the filtered folders and files separated by |, false to use default filter.
 * @param bool $sort False if you don't want to sort the result.
 * @param string $type Possible value: files, folders.
 * @param string $ext_filter File extensions separated by |, only when $type is 'files'.
 *
 * @return array Array of all items.
 */
function makefilelist($folder, $filter = "", $sort = TRUE, $type = "files", $ext_filter = "") {

    $res = [];

    $default_filters = '.|..|.htaccess|index.php|._DS_STORE|.tmp';
    if ($filter === FALSE) {
        $filter = $default_filters;
    }

    $filter = explode("|", $filter);
    if ($type == "files" && !empty($ext_filter)) {
        $ext_filter = explode("|", strtolower($ext_filter));
    }
    if (file_exists($folder)) {
        $temp = opendir($folder);
        while ($file = readdir($temp)) {
            if ($type == "files" && !in_array($file, $filter)) {
                if (!empty($ext_filter)) {
                    if (!in_array(substr(strtolower(stristr($file, '.')), +1), $ext_filter) && !is_dir($folder . $file)) {
                        $res[] = $file;
                    }
                } else {
                    if (is_file($folder . $file)) {
                        $res[] = $file;
                    }
                }
            } else if ($type == "folders" && !in_array($file, $filter)) {
                if (is_dir($folder . $file)) {
                    $res[] = $file;
                }
            }
        }
        closedir($temp);
        if ($sort) {
            natsort($res);
        }

    } else {
        $error_log = debug_backtrace()[1];
        $function = (isset($error_log['class']) ? $error_log['class'] : '') . (isset($error_log['type']) ? $error_log['type'] : '') . (isset($error_log['function']) ? $error_log['function'] : '');
        $error_log = strtr(fusion_get_locale('err_103', LOCALE . LOCALESET . 'errors.php'), ['{%folder%}' => $folder, '{%function%}' => (!empty($function) ? '<code class=\'m-r-10\'>' . $function . '</code>' : '')]);
        set_error(2, $error_log, debug_backtrace()[1]['file'], debug_backtrace()[1]['line']);
    }

    return $res;
}

/**
 * Creates page navigation.
 *
 * @param int $rowstart The number of the first listed item.
 * @param int $count The number of entries displayed on one page.
 * @param int $total The total entries which should be displayed.
 * @param int $range The number of page buttons displayed and the range of them.
 * @param string $link The base url before the appended part.
 * @param string $getname The name of the $_GET parameter that contains the start number.
 * @param bool $button Displays as button.
 *
 * @return string|bool HTML navigation. False if $count is invalid.
 */
function makepagenav($rowstart, $count, $total, $range = 3, $link = "", $getname = "rowstart", $button = FALSE) {

    $locale = fusion_get_locale();
    /* Bootstrap may be disabled in theme (see Gillette for example) without settings change in DB.
       In such case this function will not work properly.
       With this fix (used $settings instead fusion_get_settings) function will work.*/
    $tpl_global = "<div class='pagenav'>%s\n%s</div>\n";
    $tpl_currpage = "<a class='pagenavlink active' href='%s=%d'>%d</a>";
    $tpl_page = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>";
    $tpl_divider = "<span class='pagenavdivider'>...</span>";
    $tpl_firstpage = "<a class='pagenavlink' data-value='0' href='%s=0'>1</a>";
    $tpl_lastpage = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>\n";
    $tpl_button = "<a class='pagenavlink' data-value='%d' href='%s=%d'>%s</a>\n";

    if (defined('BOOTSTRAP')) {
        $tpl_global = "<nav class='pagination'><div class='pagination-row'>%s</div><div class='pagination-nav'><div class='btn-group'>\n%s</div></div></nav>\n";
        $tpl_currpage = "<a class='btn btn-secondary btn-sm active' href='%s=%d'><strong>%d</strong></a>\n";
        $tpl_page = "<a class='btn btn-secondary btn-sm' data-value='%d' href='%s=%d'>%s</a>\n";
        $tpl_divider = "</div>\n<span class='ms-2 me-2'>...</span>\n<div class='btn-group'>";
        $tpl_firstpage = "<a class='btn btn-secondary btn-sm' data-value='0' href='%s=0'>1</a>\n";
        $tpl_lastpage = "<a class='btn btn-secondary btn-sm' data-value='%d' href='%s=%d'>%s</a>\n";
        $tpl_button = "<a class='btn btn-secondary btn-block btn-md' data-value='%d' href='%s=%d'>%s</a>\n";
    }

    if ($link == '') {
        $link = FUSION_SELF . "?";
        if (fusion_get_settings("site_seo") && defined('IN_PERMALINK')) {
            global $filepath;
            $link = $filepath . "?";
        }
    }

    if (!preg_match("#[0-9]+#", $count) || $count == 0) {
        return FALSE;
    }

    $pg_cnt = ceil($total / $count);
    if ($pg_cnt <= 1) {
        return "";
    }
    $idx_back = $rowstart - $count;
    $idx_next = $rowstart + $count;

    if ($button == TRUE) {
        if ($idx_next >= $total) {
            return sprintf($tpl_button, 0, $link . $getname, 0, $locale['load_end']);
        } else {
            return sprintf($tpl_button, $idx_next, $link . $getname, $idx_next, $locale['load_more']);
        }
    }

    $cur_page = ceil(($rowstart + 1) / $count);
    $idx_fst = max($cur_page - $range, 1);
    $idx_lst = min($cur_page + $range, $pg_cnt);

    if ($range == 0) {
        $idx_fst = 1;
        $idx_lst = $pg_cnt;
    }

    $res = '';

    if ($cur_page != $idx_fst) {
        $res .= sprintf($tpl_page, 0, $link . $getname, 0, show_icon('first') . $locale['first']);
        $res .= sprintf($tpl_page, $idx_back, $link . $getname, $idx_back, show_icon('previous') . $locale['previous']);
    }

    if ($idx_back >= 0) {
        if ($cur_page > ($range + 1)) {
            $res .= sprintf($tpl_firstpage, $link . $getname);
            if ($cur_page != ($range + 2)) {
                $res .= $tpl_divider;
            }
        }
    }

    for ($i = $idx_fst; $i <= $idx_lst; $i++) {
        $offset_page = ($i - 1) * $count;
        if ($i == $cur_page) {
            $res .= sprintf($tpl_currpage, $link . $getname, $offset_page, $i);
        } else {
            $res .= sprintf($tpl_page, $offset_page, $link . $getname, $offset_page, $i);
        }
    }

    if ($idx_next < $total) {
        if ($cur_page < ($pg_cnt - $range)) {
            if ($cur_page != ($pg_cnt - $range - 1)) {
                $res .= $tpl_divider;
            }

            $res .= sprintf($tpl_lastpage, ($pg_cnt - 1) * $count, $link . $getname, ($pg_cnt - 1) * $count, $pg_cnt);
        }
    }

    if ($cur_page != $idx_lst) {

        $res .= sprintf($tpl_page, $idx_next, $link . $getname, $idx_next, $locale['next'] . show_icon('next'));
        $res .= sprintf($tpl_page, ($pg_cnt - 1) * $count, $link . $getname, ($pg_cnt - 1) * $count, $locale['last'] . show_icon('last'));
    }

    // if there is a request, we can redirect
    if (check_post($getname . '_pg')) {
        if ($val = post($getname.'pg', FILTER_VALIDATE_INT)) {
            redirect(clean_request($getname . '=' . ($val * $count - $count), [$getname], FALSE));
        } else {
            redirect(clean_request('', [$getname], FALSE));
        }
    }

    $cur_page_field = openform(random_string(5), 'POST', FORM_REQUEST, ['class' => 'display-inline-block']) .
        form_text($getname . '_pg', '', $cur_page, [ 'inline' => TRUE, 'inner_class' => 'form-control-sm']) . closeform();

    return '<label for="'.$getname.'_pg">'.$locale['global_092'].'</label>'.sprintf($tpl_global,  $cur_page_field . " " . $locale['global_093'] . " " . $pg_cnt, $res);
}

/**
 * Rowstart count.
 *
 * @param int $count The number of entries displayed on one page.
 * @param int $total The total entries which should be displayed.
 * @param int $range The number of page buttons displayed and the range of them.
 *
 * @return float
 */
function rowstart_count($total, $count, $range = 3) {

    if ($total > $count) {
        $cur_page = ceil(($total + 1) / $count);
        $pg_cnt = ceil($total / $count);
        if ($pg_cnt <= 1) {
            return 0;
        }
        $row = min($cur_page + $range, $pg_cnt);

        return ($row - 1) * $count;
    }

    return 0;
}

/**
 * Hierarchy Page Breadcrumbs, generates breadcrumbs on all your category needs.
 *
 * @param array $tree_index dbquery_tree() or tree_index().
 * @param array $tree_full dbquery_tree_full().
 * @param string $id_col The name of the category id column.
 * @param string $title_col The name of the category nmae column.
 * @param string $getname The name of the $_GET parameter.
 */
function make_page_breadcrumbs($tree_index, $tree_full, $id_col, $title_col, $getname = "rownav") {

    $_GET[$getname] = !empty($_GET[$getname]) && isnum($_GET[$getname]) ? $_GET[$getname] : 0;

    // Recursive fatal protection
    if (!function_exists('breadcrumb_page_arrays')) {
        function breadcrumb_page_arrays($tree_index, $tree_full, $id_col, $title_col, $getname, $id) {

            $crumb = [];
            if (isset($tree_index[get_parent($tree_index, $id)])) {
                $_name = get_parent_array($tree_full, $id);
                $crumb = ['link' => isset($_name[$id_col]) ? clean_request($getname . "=" . $_name[$id_col], ["aid"]) : "", 'title' => isset($_name[$title_col]) ? QuantumFields::parseLabel($_name[$title_col]) : "",];
                if (get_parent($tree_index, $id) == 0) {
                    return $crumb;
                }
                $crumb_1 = breadcrumb_page_arrays($tree_index, $tree_full, $id_col, $title_col, $getname, get_parent($tree_index, $id));

                if (!empty($crumb_1)) {
                    $crumb = array_merge_recursive($crumb, $crumb_1);
                }

            }

            return $crumb;
        }
    }

    // then we make an infinity recursive function to loop/break it out.
    $crumb = breadcrumb_page_arrays($tree_index, $tree_full, $id_col, $title_col, $getname, $_GET[$getname]);
    // then we sort in reverse.
    $title_count = !empty($crumb['title']) && is_array($crumb['title']) ? count($crumb['title']) > 1 : 0;
    if ($title_count) {
        krsort($crumb['title']);
        krsort($crumb['link']);
    }
    if ($title_count) {
        foreach ($crumb['title'] as $i => $value) {
            add_breadcrumb(['link' => $crumb['link'][$i], 'title' => $value]);
            if ($i == count($crumb['title']) - 1) {
                add_to_title($value);
                add_to_meta($value);
            }
        }
    } else if (isset($crumb['title'])) {
        add_to_title($crumb['title']);
        add_to_meta($crumb['title']);
        add_breadcrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
    }
}

/**
 * Fetch the settings from the database.
 *
 * @param string $key The key of one setting
 *
 * @return string[]|string Associative array of settings or one setting by key.
 */
function fusion_get_settings($key = NULL) {

    // It is initialized only once because of 'static'
    static $settings = [];
    if (empty($settings) and defined('DB_SETTINGS') and dbconnection() && db_exists('settings')) {
        $result = dbquery("SELECT * FROM " . DB_SETTINGS);
        while ($data = dbarray($result)) {
            $settings[$data['settings_name']] = $data['settings_value'];
        }
    }

    return $key === NULL ? $settings : ($settings[$key] ?? NULL);
}


/**
 * Login / Logout / Revalidate
 */
function fusion_set_user() {

    static $userdata = [
        'user_level' => 0,
        'user_rights' => '',
        'user_groups' => '',
    ];

    if (check_post('login')) {

        sanitizer('user_name', '', 'user_name');
        sanitizer('user_pass', '', 'user_pass');

        if (fusion_safe()) {

            $auth = new Authenticate();
            $auth->authenticate(post('user_name'), post('user_pass'), check_post('remember_me'));

            if ($auth->authRedirection()) {
                // redirect to login system to generate pin
                redirect(BASEDIR . 'login.php?auth=security_pin&auth_email=pin');
            }

            if ($userdata = $auth->getUserData()) {
                redirect(BASEDIR . fusion_get_settings('opening_page'));
            }
        }
    } else if (get('logout') === 'yes') {

        $userdata = Authenticate::logOut();

        redirect(BASEDIR . fusion_get_settings('opening_page'));

    } else if (empty($userdata['user_id'])) {

        $userdata = Authenticate::validateAuthUser();
    }

    return $userdata;
}

/**
 * Get Aidlink.
 *
 * @return string
 */
function fusion_get_aidlink() {

    $aidlink = '';
    if (defined('iADMIN') && iADMIN && defined('iAUTH')) {
        $aidlink = '?aid=' . iAUTH;
    }

    return $aidlink;
}

/**
 * Get form tokens.
 *
 * @param string $form_id Form ID.
 * @param int $max_tokens Max tokens.
 *
 * @return string
 */
function fusion_get_token($form_id, $max_tokens = 5) {

    return Token::generate_token($form_id, $max_tokens);
}

/**
 * Run the installer or halt the script
 */
function fusion_run_installer() {

    if (is_file("install.php")) {
        redirect("install.php");
    } else {
        die("No config.php or install.php files were found");
    }
}

/**
 * Detect whether the system is installed and return the config file path.
 *
 * @return string
 */
function fusion_detect_installation() {

    $config_path = dirname(__DIR__) . '/config.php';
    if (!is_file($config_path) or !filesize($config_path)) {
        fusion_run_installer();
    }

    return $config_path;
}

/**
 * A wrapper function for file_put_contents with cache invalidation.
 * If opcache is enabled on the server, this function will write the file.
 * as the original file_put_contents and invalidate the cache of the file.
 * It is needed when you create a file dynamically and want to include it
 * before the cache is invalidated. Redirection does not matter.
 *
 * @param string $file File path.
 * @param string|array $data The data to write.
 * @param int $flags
 *
 * @return int Number of written bytes
 */
function write_file($file, $data, $flags = NULL) {

    if ($flags === NULL) {
        $bytes = file_put_contents($file, $data);
    } else {
        $bytes = file_put_contents($file, $data, $flags);
    }
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($file, TRUE);
    }

    return $bytes;
}

/**
 * Return the time in seconds
 *
 * @param $value
 * @param $denominator
 *
 * @return float|int|mixed
 */
function calculate_time($value, $denominator) {
    $multiplier = ['s' => 1, 'm' => 60, 'h' => 3600, 'j' => 86400,];
    if (isnum($value) && isset($multiplier[$denominator])) {
        return $value * $multiplier[$denominator];
    }
    return $value;
}


/**
 * Returns nearest data unit.
 *
 * @param int $total_bit Number of bytes.
 *
 * @return int
 */
function calculate_byte($total_bit) {
    $calc_opts = fusion_get_locale('admins_1020', LOCALE . LOCALESET . "admin/settings.php");
    foreach ($calc_opts as $byte => $val) {
        if ($total_bit / $byte <= 999) {
            return (int)$byte;
        }
    }

    return 1048576;
}

/**
 * Alternative to rename() that works on Windows.
 *
 * @param string $origin The old name.
 * @param string $target The new name.
 */
function fusion_rename($origin, $target) {

    if ($origin != "." && $origin != ".." && !is_dir($origin)) {
        if (TRUE !== @rename($origin, $target)) {
            copy($origin, $target);
            unlink($origin);
        }
    }
}

/**
 * cURL method to get any contents for Apache that does not support SSL for remote paths.
 *
 * @param string $url
 *
 * @return bool|string
 */
function fusion_get_contents($url) {

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // PHP 7.1
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);
    } else {
        $data = @file_get_contents($url);
    }

    return $data;
}

/**
 * Cached script loader.
 * This function will cache the path that has been added and avoid duplicates.
 *
 * @param string $file_path The source file.
 * @param string $file_type Possible value: script, css.
 * @param bool $html Return as html tags instead add to output handler.
 *
 * @return string|null
 */
function fusion_load_script($file_path, $file_type = 'script', $html = FALSE) {
    static $paths = [];

    if ($file_path && empty($paths[$file_path])) {

        $_fileinfo = pathinfo($file_path);

        $base_file = $_fileinfo['dirname'].'/'.$_fileinfo['filename'].'.'.$_fileinfo['extension'];
        if (is_file($base_file)) {
            $file_path = $base_file;
            if (!defined('FUSION_DEVELOPMENT')) {
                $file_path = $base_file.'?v='.filemtime($base_file);
            }
        }

        $min_file = $_fileinfo['dirname'].'/'.$_fileinfo['filename'].(strpos($_fileinfo['filename'], '.min') ? '.' : '.min.').$_fileinfo['extension'];
        if (is_file($min_file)) {
            $file_path = $min_file;
            if (!defined('FUSION_DEVELOPMENT')) {
                $file_path = $min_file.'?v='.filemtime($min_file);
            }
        }

        $paths[$file_path] = $file_path;

        if ($file_type == "script") {

            $html_tag = "<script src='$file_path' defer></script>";
            if ($html === TRUE) {
                return $html_tag;
            }
            add_to_footer($html_tag);

        } else if ($file_type == "css") {
            $html_tag = "<link rel='stylesheet' href='$file_path' type='text/css' media='all'>";
            if ($html === TRUE) {
                return $html_tag;
            }
            add_to_head($html_tag);
        }
    }

    return NULL;
}

/**
 * Get max server upload limit.
 *
 * @return mixed
 */
function max_server_upload() {

    // select maximum upload size
    $max_upload = convert_to_bytes(ini_get('upload_max_filesize'));
    // select post limit
    $max_post = convert_to_bytes(ini_get('post_max_size'));
    // select memory limit
    $memory_limit = convert_to_bytes(ini_get('memory_limit'));

    // return the smallest of them, this defines the real limit
    return min($max_upload, $max_post, $memory_limit);
}

/**
 * Turn on/off maintenance mode.
 *
 * @param bool $maintenance Turn On/Off.
 *
 * @return bool
 */
function maintenance_mode($maintenance = TRUE) {

    $file = BASEDIR . '.maintenance';

    if ($maintenance) {
        if (!($fp = @fopen($file, 'w'))) {
            return FALSE;
        }

        @fwrite($fp, '<?php $mt_mode_start = ' . time() . '; ?>');
        @fclose($fp);
        @chmod($file, 0644);

        return is_readable($file);
    } else {
        if (file_exists($file)) {
            return @unlink($file);
        }

        return NULL;
    }
}

/**
 * Recursive in_array
 *
 * @param mixed $needle The searched value.
 * @param array $haystack The array.
 * @param bool $strict If the third parameter strict is set to true then the in_array() function will also check the types of the needle in the
 *                        haystack.
 *
 * @return bool
 */
function in_array_r($needle, $haystack, $strict = FALSE) {

    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return TRUE;
        }
    }

    return FALSE;
}
