<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: settings_languages.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$locale = fusion_get_locale('', [LOCALE . LOCALESET . 'admin/settings.php', LOCALE . LOCALESET . 'setup.php']);
$aidlink = fusion_get_aidlink();

$contents = array(
    'view' => 'lang_view',
    'button' => 'lang_button',
    'settings' => TRUE,
    'link' => ($admin_link ?? ''),
    'title' => $locale['admins_682ML'],
    'description' => $locale['admins_language_description'],
    'actions' => array(
        'post' => array(
            'savesettings' => array('form_id' => 'settingsFrm', 'callback' => 'lang_post'),
            'clearcache' => array('form_id' => 'cacheFrm', 'callback' => 'lang_cache_post')
        ),
    ),
);
include __DIR__ . "/posts/language.php";
include __DIR__ . "/views/language.php";

function mlt_updates() {
    $inf_result = dbquery("SELECT * FROM " . DB_INFUSIONS);
    if (dbrows($inf_result)) {
        while ($cdata = dbarray($inf_result)) {
            include INFUSIONS . $cdata['inf_folder'] . "/infusion.php"; // there is a system language inside. // cant read into system language.
            if (isset($inf_mlt) && is_array($inf_mlt)) {
                $inf_mlt = flatten_array($inf_mlt);
                if (!empty($inf_mlt['title']) && !empty($inf_mlt['rights'])) {
                    dbquery("UPDATE " . DB_LANGUAGE_TABLES . " SET mlt_title='" . $inf_mlt['title'] . "' WHERE mlt_rights='" . $inf_mlt['rights'] . "'");
                } else {
                    //$defender->stop();
                    add_notice("danger",
                        "Error due to incomplete locale translations in infusions folder " . $cdata['inf_folder'] . ". This infusion does not have the localized title and change is aborted. Please translate setup.php.");
                }
            }
            unset($inf_mlt);
        }
    }
}
