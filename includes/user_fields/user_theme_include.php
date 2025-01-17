<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_theme_include.php
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

// Display user field input
if ($profile_method == "input") {

    if (fusion_get_settings('userthemes') == 1 || iADMIN) {

        $theme_files = makefilelist(THEMES, ".|..|admin_themes|templates|.svn", TRUE, "folders");
        $lcoale = fusion_get_locale();

        array_unshift($theme_files, 'Default');

        $theme_opts = [];
        foreach ($theme_files as $theme) {
            $theme_opts[$theme] = $theme;
        }
        $locale = fusion_get_locale();

        $options += [
            'options'        => $theme_opts,
            'inline'         => (defined('INPUT_INLINE') || 0),
            'callback_check' => 'theme_exists',
            'error_text'     => $locale['uf_theme_error'],
            'inner_width'    => '100%',
            'width'          => '100%',
        ];

        $user_fields = form_select('user_theme', $locale['uf_theme'], $field_value, $options);
    }
    // Display in profile
} else if ($profile_method == 'display') {
    // no to display
}
