<?php

/**
 * @throws Exception
 */
function time_view() {
    $locale = fusion_get_locale();

    $timezones_json = json_decode('{
      "Etc/GMT+12": "International Date Line West",
      "Pacific/Midway": "Midway Island, Samoa",
      "Pacific/Honolulu": "Hawaii",
      "America/Juneau": "Alaska",
      "America/Dawson": "Pacific Time (US and Canada); Tijuana",
      "America/Boise": "Mountain Time (US and Canada)",
      "America/Chihuahua": "Chihuahua, La Paz, Mazatlan",
      "America/Phoenix": "Arizona",
      "America/Chicago": "Central Time (US and Canada)",
      "America/Regina": "Saskatchewan",
      "America/Mexico_City": "Guadalajara, Mexico City, Monterrey",
      "America/Belize": "Central America",
      "America/Detroit": "Eastern Time (US and Canada)",
      "America/Indiana/Indianapolis": "Indiana (East)",
      "America/Bogota": "Bogota, Lima, Quito",
      "America/Glace_Bay": "Atlantic Time (Canada)",
      "America/Caracas": "Caracas, La Paz",
      "America/Santiago": "Santiago",
      "America/St_Johns": "Newfoundland and Labrador",
      "America/Sao_Paulo": "Brasilia",
      "America/Argentina/Buenos_Aires": "Buenos Aires, Georgetown",
      "America/Godthab": "Greenland",
      "Etc/GMT+2": "Mid-Atlantic",
      "Atlantic/Azores": "Azores",
      "Atlantic/Cape_Verde": "Cape Verde Islands",
      "GMT": "Dublin, Edinburgh, Lisbon, London",
      "Africa/Casablanca": "Casablanca, Monrovia",
      "Atlantic/Canary": "Canary Islands",
      "Europe/Belgrade": "Belgrade, Bratislava, Budapest, Ljubljana, Prague",
      "Europe/Sarajevo": "Sarajevo, Skopje, Warsaw, Zagreb",
      "Europe/Brussels": "Brussels, Copenhagen, Madrid, Paris",
      "Europe/Amsterdam": "Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
      "Africa/Algiers": "West Central Africa",
      "Europe/Bucharest": "Bucharest",
      "Africa/Cairo": "Cairo",
      "Europe/Helsinki": "Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius",
      "Europe/Athens": "Athens, Istanbul, Minsk",
      "Asia/Jerusalem": "Jerusalem",
      "Africa/Harare": "Harare, Pretoria",
      "Europe/Moscow": "Moscow, St. Petersburg, Volgograd",
      "Asia/Kuwait": "Kuwait, Riyadh",
      "Africa/Nairobi": "Nairobi",
      "Asia/Baghdad": "Baghdad",
      "Asia/Tehran": "Tehran",
      "Asia/Dubai": "Abu Dhabi, Muscat",
      "Asia/Baku": "Baku, Tbilisi, Yerevan",
      "Asia/Kabul": "Kabul",
      "Asia/Yekaterinburg": "Ekaterinburg",
      "Asia/Karachi": "Islamabad, Karachi, Tashkent",
      "Asia/Kolkata": "Chennai, Kolkata, Mumbai, New Delhi",
      "Asia/Kathmandu": "Kathmandu",
      "Asia/Dhaka": "Astana, Dhaka",
      "Asia/Colombo": "Sri Jayawardenepura",
      "Asia/Almaty": "Almaty, Novosibirsk",
      "Asia/Rangoon": "Yangon Rangoon",
      "Asia/Bangkok": "Bangkok, Hanoi, Jakarta",
      "Asia/Krasnoyarsk": "Krasnoyarsk",
      "Asia/Shanghai": "Beijing, Chongqing, Hong Kong SAR, Urumqi",
      "Asia/Kuala_Lumpur": "Kuala Lumpur, Singapore",
      "Asia/Taipei": "Taipei",
      "Australia/Perth": "Perth",
      "Asia/Irkutsk": "Irkutsk, Ulaanbaatar",
      "Asia/Seoul": "Seoul",
      "Asia/Tokyo": "Osaka, Sapporo, Tokyo",
      "Asia/Yakutsk": "Yakutsk",
      "Australia/Darwin": "Darwin",
      "Australia/Adelaide": "Adelaide",
      "Australia/Sydney": "Canberra, Melbourne, Sydney",
      "Australia/Brisbane": "Brisbane",
      "Australia/Hobart": "Hobart",
      "Asia/Vladivostok": "Vladivostok",
      "Pacific/Guam": "Guam, Port Moresby",
      "Asia/Magadan": "Magadan, Solomon Islands, New Caledonia",
      "Pacific/Fiji": "Fiji Islands, Kamchatka, Marshall Islands",
      "Pacific/Auckland": "Auckland, Wellington",
      "Pacific/Tongatapu": "Nuku\'alofa"
    }', TRUE);
    $timezone_array = [];
    foreach ($timezones_json as $zone => $zone_city) {
        $offset = (new DateTime(NULL, new DateTimeZone($zone)))->getOffset() / 3600;
        $timezone_array[$zone] = '(GMT' . ($offset < 0 ? $offset : '+' . $offset) . ') ' . $zone_city;
    }

    $weekdayslist = explode("|", $locale['weekdays']);

    $date_opts = [];
    foreach ($locale['dateformats'] as $dateformat) {
        $date_opts[$dateformat] = showdate($dateformat, time());
    }

    $settings = fusion_get_settings();

    echo openform('settingsFrm', 'POST');
    openside($locale['admins_458'] . '<small>Set the time and date configurations', TRUE);
    echo '<div class="mt-3 mb-3">';
    echo '<div class="d-flex w-100 lh-lg"><span>' . $locale['admins_459'] . '</span><span class="ms-auto emphasis">' . showdate($settings['longdate'], time(), ['tz_override' => $settings['serveroffset']]) . '</span></div>';
    echo '<div class="d-flex w-100 lh-lg"><span>' . $locale['admins_460'] . '</span><span class="ms-auto emphasis">' . (column_exists('users', 'user_timezone') ? showdate($settings['longdate'], time(), ['tz_override' => fusion_get_userdata('user_timezone')]) : $locale['na']) . '</span></div>';
    echo '<div class="d-flex w-100 lh-lg"><span>' . $locale['admins_461'] . '</span><span class="ms-auto emphasis">' . showdate($settings['longdate'], time(), ['tz_override' => $settings['timeoffset']]) . '</span></div>';
    echo '<div class="d-flex w-100 lh-lg"><span>' . $locale['admins_466'] . '</span><span class="ms-auto emphasis">' . showdate($settings['longdate'], time(), ['tz_override' => $settings['default_timezone']]) . '</span></div>';
    echo '</div>';
    closeside();

    openside('Time Settings<small>' . $locale['admins_time_description'] . '</small>', TRUE);
    echo form_select('shortdate', $locale['admins_451'], $settings['shortdate'], [
        'options' => $date_opts,
        'placeholder' => $locale['admins_455'],
        'inner_width' => '100%',
    ]);
    echo form_select('longdate', $locale['admins_452'], $settings['longdate'], [
        'options' => $date_opts,
        'placeholder' => $locale['admins_455'],
        'inner_width' => '100%',
    ]);
    echo form_select('forumdate', $locale['admins_453'], $settings['forumdate'], [
        'options' => $date_opts,
        'placeholder' => $locale['admins_455'],
        'inner_width' => '100%',
    ]);
    echo form_select('newsdate', $locale['admins_457'], $settings['newsdate'], [
        'options' => $date_opts,
        'placeholder' => $locale['admins_455'],
        'inner_width' => '100%',
    ]);
    echo form_select('subheaderdate', $locale['admins_454'], $settings['subheaderdate'], [
        'options' => $date_opts,
        'placeholder' => $locale['admins_455'],
        'inner_width' => '100%',
    ]);
    closeside();
    openside('Offset Settings<small>The configuration settings for offset on Time and Date system</small>', TRUE);
    echo form_select('serveroffset', $locale['admins_463'], $settings['serveroffset'], [
        'options' => $timezone_array, 'inner_width' => '100%']);
    echo form_select('timeoffset', $locale['admins_456'], $settings['timeoffset'], ['options' => $timezone_array, 'inner_width' => '100%']);
    echo form_select('default_timezone', $locale['admins_464'], $settings['default_timezone'], ['options' => $timezone_array, 'inner_width' => '100%']);
    echo form_select('week_start', $locale['admins_465'], $settings['week_start'], ['options' => $weekdayslist, 'inner_width' => '100%']);
    closeside();

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    time_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();
}

function time_button() {
    $locale = fusion_get_locale();
    echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
}
