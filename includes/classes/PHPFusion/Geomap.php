<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Geomap.php
| Author: Frederick MC Chan (Chan)
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

/**
 * Class Geomap
 *
 * @package PHPFusion
 */
class Geomap {

    public static $country_list = [];
    public static $currency_list = [];

    /**
     * @param string $country
     *
     * @return array|mixed|null
     */
    public function callingCodes($country = NULL) {
        $countries = [];

        require_once INCLUDES.'geomap/geo.countries.php';

        if ($country !== NULL) {
            foreach ($countries as $country_arr) {
                if ($country_arr['code'] == $country) {
                    return $country_arr['prefix'];
                }
            }
            return NULL;
        }

        return array_map(function ($value) {
            return $value['prefix'];
        }, $countries);
    }

    public function callingCodesOpts($country = NULL) {
        $countries = [];

        require_once INCLUDES.'geomap/geo.countries.php';
        if ($country !== NULL) {
            foreach ($countries as $country_arr) {
                if ($country_arr['code'] == $country) {
                    return $country_arr['prefix'];
                }
            }
            return NULL;
        }

        return array_map(function ($value) {
            return '('.$value['prefix'].') '.$value['name'];
        }, $countries);
    }

    /**
     * @return mixed|string
     * @todo: countries.json information to geo.countries.php
     */
    private function getCountryResource() {
        $map_repo = file_get_contents(INCLUDES.'geomap/countries.json');

        return json_decode($map_repo);
    }

    /**
     * Returns countries
     *
     * @param string $country_code ISO cca2 code / returns array if NULL
     *
     * @return array|null
     */
    function currency($country_code = NULL) {
        $locale = fusion_get_locale("", LOCALE.LOCALESET.'currency.php');

        if (empty(self::$currency_list)) {

            foreach (self::getCountryResource() as $object) {
                if ($object->name->common !== 'Antarctica') {
                    if ($country_code == $object->currency[0]) {
                        break;
                    }
                    $currency_symbol = isset($locale['currency_symbol'][$object->currency[0]]) ? $locale['currency_symbol'][$object->currency[0]] : "$";
                    self::$currency_list[$object->currency[0]] = $locale['currency'][$object->currency[0]]." (".$currency_symbol.")";
                }
            }
        }

        if ($country_code !== NULL) {
            return isset(self::$currency_list[$country_code]) ? self::$currency_list[$country_code] : $locale['na'];
        } else {
            return array_filter(self::$currency_list);
        }
    }

    /**
     * Returns a locale from a country code that is provided.
     * I have not thought of an implementation benefits out of this yet.
     *
     * @param string $country_code  ISO 3166-2-alpha 2 country code
     * @param string $language_code ISO 639-1-alpha 2 language code
     *
     * @returns string a locale, formatted like en_US, or null if not found
     */
    public static function countryCodeLoLocale($country_code, $language_code = '') {
        $locales = [
            'af-ZA',
            'am-ET',
            'ar-AE',
            'ar-BH',
            'ar-DZ',
            'ar-EG',
            'ar-IQ',
            'ar-JO',
            'ar-KW',
            'ar-LB',
            'ar-LY',
            'ar-MA',
            'arn-CL',
            'ar-OM',
            'ar-QA',
            'ar-SA',
            'ar-SY',
            'ar-TN',
            'ar-YE',
            'as-IN',
            'az-Cyrl-AZ',
            'az-Latn-AZ',
            'ba-RU',
            'be-BY',
            'bg-BG',
            'bn-BD',
            'bn-IN',
            'bo-CN',
            'br-FR',
            'bs-Cyrl-BA',
            'bs-Latn-BA',
            'ca-ES',
            'co-FR',
            'cs-CZ',
            'cy-GB',
            'da-DK',
            'de-AT',
            'de-CH',
            'de-DE',
            'de-LI',
            'de-LU',
            'dsb-DE',
            'dv-MV',
            'el-GR',
            'en-029',
            'en-AU',
            'en-BZ',
            'en-CA',
            'en-GB',
            'en-IE',
            'en-IN',
            'en-JM',
            'en-MY',
            'en-NZ',
            'en-PH',
            'en-SG',
            'en-TT',
            'en-US',
            'en-ZA',
            'en-ZW',
            'es-AR',
            'es-BO',
            'es-CL',
            'es-CO',
            'es-CR',
            'es-DO',
            'es-EC',
            'es-ES',
            'es-GT',
            'es-HN',
            'es-MX',
            'es-NI',
            'es-PA',
            'es-PE',
            'es-PR',
            'es-PY',
            'es-SV',
            'es-US',
            'es-UY',
            'es-VE',
            'et-EE',
            'eu-ES',
            'fa-IR',
            'fi-FI',
            'fil-PH',
            'fo-FO',
            'fr-BE',
            'fr-CA',
            'fr-CH',
            'fr-FR',
            'fr-LU',
            'fr-MC',
            'fy-NL',
            'ga-IE',
            'gd-GB',
            'gl-ES',
            'gsw-FR',
            'gu-IN',
            'ha-Latn-NG',
            'he-IL',
            'hi-IN',
            'hr-BA',
            'hr-HR',
            'hsb-DE',
            'hu-HU',
            'hy-AM',
            'id-ID',
            'ig-NG',
            'ii-CN',
            'is-IS',
            'it-CH',
            'it-IT',
            'iu-Cans-CA',
            'iu-Latn-CA',
            'ja-JP',
            'ka-GE',
            'kk-KZ',
            'kl-GL',
            'km-KH',
            'kn-IN',
            'kok-IN',
            'ko-KR',
            'ky-KG',
            'lb-LU',
            'lo-LA',
            'lt-LT',
            'lv-LV',
            'mi-NZ',
            'mk-MK',
            'ml-IN',
            'mn-MN',
            'mn-Mong-CN',
            'moh-CA',
            'mr-IN',
            'ms-BN',
            'ms-MY',
            'mt-MT',
            'nb-NO',
            'ne-NP',
            'nl-BE',
            'nl-NL',
            'nn-NO',
            'nso-ZA',
            'oc-FR',
            'or-IN',
            'pa-IN',
            'pl-PL',
            'prs-AF',
            'ps-AF',
            'pt-BR',
            'pt-PT',
            'qut-GT',
            'quz-BO',
            'quz-EC',
            'quz-PE',
            'rm-CH',
            'ro-RO',
            'ru-RU',
            'rw-RW',
            'sah-RU',
            'sa-IN',
            'se-FI',
            'se-NO',
            'se-SE',
            'si-LK',
            'sk-SK',
            'sl-SI',
            'sma-NO',
            'sma-SE',
            'smj-NO',
            'smj-SE',
            'smn-FI',
            'sms-FI',
            'sq-AL',
            'sr-Cyrl-BA',
            'sr-Cyrl-CS',
            'sr-Cyrl-ME',
            'sr-Cyrl-RS',
            'sr-Latn-BA',
            'sr-Latn-CS',
            'sr-Latn-ME',
            'sr-Latn-RS',
            'sv-FI',
            'sv-SE',
            'sw-KE',
            'syr-SY',
            'ta-IN',
            'te-IN',
            'tg-Cyrl-TJ',
            'th-TH',
            'tk-TM',
            'tn-ZA',
            'tr-TR',
            'tt-RU',
            'tzm-Latn-DZ',
            'ug-CN',
            'uk-UA',
            'ur-PK',
            'uz-Cyrl-UZ',
            'uz-Latn-UZ',
            'vi-VN',
            'wo-SN',
            'xh-ZA',
            'yo-NG',
            'zh-CN',
            'zh-HK',
            'zh-MO',
            'zh-SG',
            'zh-TW',
            'zu-ZA',];
        foreach ($locales as $locale) {
            $locale_region = locale_get_region($locale);
            $locale_language = locale_get_primary_language($locale);
            $locale_array = [
                'language' => $locale_language,
                'region'   => $locale_region
            ];
            if (strtoupper($country_code) == $locale_region &&
                $language_code == '') {
                return locale_compose($locale_array);
            } else if (strtoupper($country_code) == $locale_region &&
                strtolower($language_code) == $locale_language) {
                return locale_compose($locale_array);
            }
        }
        return NULL;
    }
}
