<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: format_include.php
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
 * Prevent strings from growing to long and breaking the layout.
 *
 * @param string $text String to trim.
 * @param int $length Max length of the string.
 *
 * @return string String trimmed to the given length.
 */
function trimlink($text, $length) {

    if (strlen($text) > $length) {
        if (function_exists('mb_substr')) {
            $text = mb_substr($text, 0, ($length - 3), 'UTF-8') . "...";
        } else {
            $text = substr($text, 0, ($length - 3)) . "...";
        }
    }

    return $text;
}

/**
 * Trim a text to a number of words.
 *
 * @param string $text String to trim.
 * @param int $limit The number of words.
 * @param string $suffix If $text is longer than $limit, $suffix will be appended.
 *
 * @return string String trimmed to the given length.
 */
function fusion_first_words($text, $limit, $suffix = '&hellip;') {

    $text = preg_replace('/[\r\n]+/', '', $text);

    return preg_replace('~^(\s*\w+' . str_repeat('\W+\w+', $limit - 1) . '(?(?=[?!:;.])[[:punct:]]\s*))\b(.+)$~isxu', '$1' . $suffix, strip_tags($text));
}

/**
 * Pure trim function.
 *
 * @param string $str String to trim.
 * @param int $length The number of characters.
 *
 * @return string Trimmed text.
 */
function trim_text($str, $length = 300) {

    for ($i = $length; $i <= strlen($str); $i++) {
        $spacetest = substr("$str", $i, 1);
        if ($spacetest == " ") {
            $spaceok = substr("$str", 0, $i);

            return ($spaceok . "...");
        }
    }

    return ($str);
}

/**
 * Replace offensive words with the defined replacement word.
 * The list of offensive words and the replacement word are both defined in the Security Settings.
 *
 * @param string $text Text that should be censored.
 *
 * @return string Censored text.
 */
function censorwords($text) {
    $settings = fusion_get_settings();

    if ($settings['bad_words_enabled'] && !empty($settings['bad_words'])) {
        //$words = preg_quote(trim($settings['bad_words']), "/");
        //$words = preg_replace("/\\s+/", "|", $words);
        $words = str_replace("\r", "", $settings["bad_words"]);
        $words = str_replace("\n", "|", $words);
        $text = preg_replace("/" . $words . "/si", $settings['bad_word_replace'], $text);
    }

    return $text;
}

/*
 *Censor portion of the text from plain view
 */
function censortext($text) {
    $count = floor(strlen($text) / 2);
    $replace_count = ceil($count / 1.5);
    return substr_replace($text, str_repeat('*', $replace_count), 3, $count);
}

/**
 * Encode and format code inside <code> tag.
 *
 * @param string $text String with code.
 *
 * @return string Encoded and formatted code.
 */
function encode_code($text) {

    preg_match_all("#<code>(.*?)</code>#is", $text, $codes);
    $replace = [];
    foreach ($codes[1] as $key => $codeblock) {
        $replace[$key] = htmlentities($codeblock, ENT_QUOTES, 'UTF-8', FALSE);
    }
    unset($key, $codeblock);

    if (!empty($codes[0])) {
        if (!defined('PRISMJS')) {
            define('PRISMJS', TRUE);
            add_to_head('<link rel="stylesheet" href="' . INCLUDES . 'bbcodes/code/prism.css">');
            add_to_footer('<script src="' . INCLUDES . 'bbcodes/code/prism.js"></script>');
        }
    }

    foreach ($codes[0] as $key => $replacer) {
        $code = str_replace('&lt;br /&gt;', '', $replace[$key]);
        $code = format_code($code);
        $text = str_replace($replacer, '<pre><code class="language-php">' . $code . '</code></pre>', $text);
    }
    unset($key, $replacer, $replace);

    return $text;
}

/**
 * Add correct amount of spaces and tabs inside code.
 *
 * @param string $code The code you want to format.
 *
 * @return string Formatted code.
 */
function format_code($code) {

    $code = htmlentities($code, ENT_QUOTES, 'UTF-8', FALSE);

    $code = str_replace(["  ", "  ", "\t", "[", "]"], ["&nbsp; ", " &nbsp;", "&nbsp; &nbsp;", "&#91;", "&#93;"], $code);

    return preg_replace("/^ {1}/m", "&nbsp;", $code);
}

/**
 * Formats a number in a numeric acronym, and rounding.
 *
 * @param int $value Number to format.
 * @param int $decimals The number of decimals.
 * @param string $dec_point Decimal point.
 * @param string $thousand_sep Thousands separator.
 * @param bool $round Round number.
 * @param bool $acryonym Acronym.
 *
 * @return string
 */
function format_num($value, $decimals = 0, $dec_point = ".", $thousand_sep = ",", $round = TRUE, $acryonym = TRUE) {

    $array = [13 => $acryonym ? "t" : "trillion", 10 => $acryonym ? "b" : "billion", 7 => $acryonym ? "m" : "million", 4 => $acryonym ? "k" : "thousand"];

    if (is_numeric($value)) {
        if ($round === TRUE) {
            foreach ($array as $length => $rounding) {
                if (strlen($value) >= $length) {
                    $power = pow(10, $length - 1);
                    if ($value > $power && $length > 4 && $decimals === NULL) {
                        $decimals = 2;
                    }

                    return number_format(($value / $power), $decimals, $dec_point, $thousand_sep) . $rounding;
                }
            }
        }

        return number_format($value, $decimals, $dec_point, $thousand_sep);
    }

    return $value;
}

/**
 * Converts any formatted number back to float numbers in PHP
 *
 * @param string|int $value Formatted number.
 *
 * @return float
 */
function format_float($value) {

    return floatval(preg_replace('/[^\d.]/', '', $value));
}


/**
 * Format the date and time according to the site and user offset.
 *
 * @param string $format Possible value: shortdate, longdate, forumdate, newsdate or date pattern for the strftime.
 * @param int $val Unix timestamp.
 * @param array $options Possible options tz_override.
 *
 * @return string String formatted according to the given format string.
 *                Month and weekday names and other language dependent strings respect the current locale set.
 */
function showdate($format, $val, $options = []) {
    $userdata = fusion_get_userdata();

    if (isset($options['tz_override'])) {
        $tz_client = $options['tz_override'];
    } else {
        if (!empty($userdata['user_timezone'])) {
            $tz_client = $userdata['user_timezone'];
        } else {
            $tz_client = fusion_get_settings('timeoffset');
        }
    }

    if (empty($tz_client)) {
        $tz_client = 'Europe/London';
    }

    $offset = 0;

    try {
        $client_dtz = new DateTimeZone($tz_client);
        $client_dt = new DateTime('now', $client_dtz);
        $offset = (int)$client_dtz->getOffset($client_dt);
    } catch (Exception $e) {
        set_error(E_CORE_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
    }

    if (!empty($val)) {
        $offset = (int)$val + $offset;
        if (in_array($format, ['shortdate', 'longdate', 'forumdate', 'newsdate'])) {
            $format = fusion_get_settings($format);

            return format_date($format, $offset);
        }

        return format_date($format, $offset);

    }

    $format = fusion_get_settings($format);
    $offset = time() + $offset;

    return format_date($format, $offset);
}

/**
 * Format date - replacement for strftime()
 *
 * @param string $format Dateformat
 * @param int $time Timestamp
 *
 * @return string
 */
function format_date($format, $timestamp) {
    $locale = fusion_get_locale();
    $format = str_replace(['%a', '%A', '%d', '%e', '%u', '%w', '%W', '%b', '%h', '%B', '%m', '%y', '%Y', '%D', '%F', '%x', '%n', '%t', '%H', '%k', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z', '%c', '%s', '%%'], ['D', 'l', 'd', 'j', 'N', 'w', 'W', 'M', 'M', 'F', 'm', 'y', 'Y', 'm/d/y', 'Y-m-d', 'm/d/y', "\n", "\t", 'H', 'G', 'h', 'g', 'i', 'a', 'A', 'h:i:s A', 'H:i', 's', 'H:i:s', 'H:i:s', 'O', 'T', 'D M j H:i:s Y', 'U', '%'], $format);

    $format = preg_replace('/(?<!\\\\)r/', DATE_RFC2822, $format);
    $new_format = '';
    $format_length = strlen($format);
    $lcmonth = explode('|', $locale['months']);
    $lcweek = explode('|', $locale['weekdays']);
    $lcshort = explode('|', $locale['shortmonths']);
    $lcmerid = explode('|', $locale['meridiem']);

    $date = DateTimeImmutable::createFromFormat('U', $timestamp);

    for ($i = 0; $i < $format_length; $i++) {
        switch ($format[$i]) {
            case 'D':
                $new_format .= addcslashes(substr($lcweek[$date->format('w')], 0, 2), '\\A..Za..z');
                break;
            case 'l':
                $new_format .= addcslashes($lcweek[$date->format('w')], '\\A..Za..z');
                break;
            case 'F':
                $new_format .= addcslashes($lcmonth[$date->format('n')], '\\A..Za..z');
                break;
            case 'M':
                $new_format .= addcslashes($lcshort[$date->format('n')], '\\A..Za..z');
                break;
            case 'a':
                $mofset = $offset->format('a') == 'am' ? 0 : 1;
                $new_format .= addcslashes($lcmerid[$mofset], '\\A..Za..z');
                break;
            case 'A':
                $mofset = $offset->format('A') == 'AM' ? 2 : 3;
                $new_format .= addcslashes($lcmerid[$mofset], '\\A..Za..z');
                break;
            case '\\':
                $new_format .= $format[$i];
                // If character follows a slash, we add it without translating.
                if ($i < $format_length) {
                    $new_format .= $format[++$i];
                }
                break;
            default:
                $new_format .= $format[$i];
                break;
        }
    }

    return $date->format($new_format);
}

/*function format_date($format, $time) {
    $format = str_replace(
        ['%a', '%A', '%d', '%e', '%u', '%w', '%W', '%b', '%h', '%B', '%m', '%y', '%Y', '%D', '%F', '%x', '%n', '%t', '%H', '%k', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z', '%c', '%s', '%%'],
        ['D', 'l', 'd', 'j', 'N', 'w', 'W', 'M', 'M', 'F', 'm', 'y', 'Y', 'm/d/y', 'Y-m-d', 'm/d/y', "\n", "\t", 'H', 'G', 'h', 'g', 'i', 'A', 'a', 'h:i:s A', 'H:i', 's', 'H:i:s', 'H:i:s', 'O', 'T', 'D M j H:i:s Y', 'U', '%'],
        $format
    );

    $date = DateTimeImmutable::createFromFormat('U', $time);

    return $date->format($format);
}*/


/**
 * Translate bytes into kB, MB, GB or TB.
 *
 * @param int $size The number of bytes.
 * @param int $decimals The number of decimals.
 * @param bool $dir True if it is the size of a directory.
 *
 * @return string
 */
function parsebytesize($size, $decimals = 2, $dir = FALSE) {

    $locale = fusion_get_locale();

    $kb = 1024;
    $mb = 1024 * $kb;
    $gb = 1024 * $mb;
    $tb = 1024 * $gb;

    $size = (empty($size)) ? "0" : $size;

    if (($size == 0) && ($dir)) {
        return "0 " . $locale['global_460'];
    } else if ($size < $kb) {
        return $size . $locale['global_461'];
    } else if ($size < $mb) {
        return round($size / $kb, $decimals) . 'kB';
    } else if ($size < $gb) {
        return round($size / $mb, $decimals) . 'MB';
    } else if ($size < $tb) {
        return round($size / $gb, $decimals) . 'GB';
    } else {
        return round($size / $tb, $decimals) . 'TB';
    }
}

/**
 * Minify JS code.
 *
 * @param string $code Unminified code.
 *
 * @return string Minified code.
 */
function jsminify($code) {

    $minifier = new JS($code);

    return $minifier->minify();
}


/**
 * Convert B, KB, MB, GB, TB, PB to bytes
 *
 * @param $value
 *
 * @return array|float|int|string|string[]|null
 */
function parse_byte($value) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $number = substr($value, 0, -2);
    $suffix = strtoupper(substr($value, -2));

    //B or no suffix
    if (is_numeric(substr($suffix, 0, 1))) {
        return preg_replace('/[^\d]/', '', $value);
    }

    $exponent = array_flip($units)[$suffix] ?? NULL;
    if ($exponent === NULL) {
        return NULL;
    }

    return $number * (1024 ** $exponent);
}


/**
 * Convert to bytes.
 *
 * @param int|string $val
 *
 * @return int
 */
function convert_to_bytes($val) {

    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $kb = 1024;
    $mb = 1024 * $kb;
    $gb = 1024 * $mb;
    switch ($last) {
        case 'g':
            $val = (int)$val * $gb;
            break;
        case 'm':
            $val = (int)$val * $mb;
            break;
        case 'k':
            $val = (int)$val * $kb;
            break;
    }

    return (int)$val;
}


/**
 * @param $s1
 * @param $s2
 *
 * @return false|string
 */
function strleft($s1, $s2) {

    return substr($s1, 0, strpos($s1, $s2));
}

/**
 * Adds a whitespace if value is present.
 *
 * @param string $value
 *
 * @return string
 */
function whitespace($value) {

    if (!empty($value)) {
        return " " . $value;
    }

    return "";
}
