<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: register.php
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
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';

$locale = fusion_get_locale("", LOCALE.LOCALESET."user_fields.php");
$settings = fusion_get_settings();

require_once THEMES."templates/global/register.tpl.php";

add_to_title($locale['global_107']);
add_to_meta("keywords", $locale['global_107']);

if (iMEMBER || $settings['enable_registration'] == 0) {
    redirect(BASEDIR.'index.php');
}

if ($settings['gateway'] == 1) {
    if (empty($_SESSION["validated"])) {
        $_SESSION['validated'] = 'False';
    }
    if (isset($_SESSION["validated"]) && $_SESSION['validated'] !== 'True') {
        require_once INCLUDES."gateway/gateway.php";
    }
}

if ((isset($_SESSION["validated"]) && $_SESSION["validated"] == "True") || $settings['gateway'] == 0) {
    $errors = [];

    if (check_get('email') && check_get('code')) {
        if (!preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", get('email'))) {
            redirect("register.php?error=activate");
        }

        if (!preg_check("/^[0-9a-z]{40}$/", get('code'))) {
            redirect("register.php?error=activate");
        }

        $result = dbquery("SELECT user_info FROM ".DB_NEW_USERS." WHERE user_code=:code AND user_email=:email", [
            ':code' => get('code'), ':email' => get('email')
        ]);

        if (dbrows($result) > 0) {

            add_to_title($locale['u155']);

            $data = dbarray($result);

            $user_info = unserialize(base64_decode($data['user_info']));

            if (empty($user_info['user_hide_email'])) {
                $user_info['user_hide_email'] = 1;
            }

            dbquery_insert(DB_USERS, $user_info, 'save');

            $result = dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_code=:code LIMIT 1", [':code' => get('code')]);

            if ($settings['admin_activation'] == 1) {
                addnotice("info", $locale['u171']." - ".$locale['u162'], $settings["opening_page"]);
            } else {
                addnotice("info", $locale['u171']." - ".$locale['u161'], $settings["opening_page"]);
            }

        }
        redirect($settings['opening_page']);

    }

    if (!check_get('email') && !check_get('code')) {

        if (check_post('register')) {

            $userInput = new PHPFusion\UserFieldsInput();
            $userInput->moderation = 0;
            $userInput->skipCurrentPass = 1;
            $userInput->saveInsert();

            unset($userInput);
        }

        $userFields = new PHPFusion\UserFields();
        $userFields->postName = "register";
        $userFields->postValue = $locale['u101'];
        $userFields->registration = TRUE;

        $userFields->displayRegisterInput();
    }
}

require_once THEMES.'templates/footer.php';
