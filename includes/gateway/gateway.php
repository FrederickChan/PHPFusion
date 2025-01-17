<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: gateway.php
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

/**
 * Anti Bot Gateway that combine multiple methods to prevent auto bots.
 */

$locale = fusion_get_locale();

require_once "constants_include.php";
require_once "functions_include.php";

// Terminate and ban all excessive access atempts.
antiflood_countaccess();

// Flag for pass, just increment on amount of checks we add.
$multiplier = "0";
$reply_method = '';

$info = [
    'showform'         => FALSE,
    'incorrect_answer' => FALSE
];

// Don´t run twice
if (!isset($_POST['gateway_submit']) && !isset($_POST['register'])) {

    // Get some numbers up. Always keep an odd number to void 10-10 etc.
    $a = rand(11, 20);
    $b = rand(1, 10);

    $method = fusion_get_settings('gateway_method'); // 0 words, 1 numbers, 2 both

    if ($method == 0) {
        $antibot = $a + $b;
        $multiplier = "+";
        $reply_method = $locale['gateway_062'];
        $a = convertNumberToWord($a);
        $antibot = convertNumberToWord($antibot);
        $_SESSION["antibot"] = strtolower($antibot);
    } else if ($method == 1) {
        $antibot = $a - $b;
        $multiplier = "-";
        $reply_method = $locale['gateway_063'];
        $_SESSION["antibot"] = $antibot;
        $b = convertNumberToWord($b);
    } else {
        if ($a > 15) {
            $antibot = $a + $b;
            $multiplier = "+";
            $reply_method = $locale['gateway_062'];
            $a = convertNumberToWord($a);
            $antibot = convertNumberToWord($antibot);
            $_SESSION["antibot"] = strtolower($antibot);
        } else {
            $antibot = $a - $b;
            $multiplier = "-";
            $reply_method = $locale['gateway_063'];
            $_SESSION["antibot"] = $antibot;
            $b = convertNumberToWord($b);
        }
    }

    $a = str_rot47($a);
    $b = str_rot47($b);

    echo "<noscript>".$locale['gateway_052']."</noscript>";

    // Just add fields to random
    $honeypot_array = [$locale['gateway_053'], $locale['gateway_054'], $locale['gateway_055'], $locale['gateway_056'], $locale['gateway_057'], $locale['gateway_058'], $locale['gateway_059']];
    shuffle($honeypot_array);
    $_SESSION["honeypot"] = $honeypot_array[3];

    // Try this, and we see, Rot47 Encryption etc.
    add_to_footer('<script type="text/javascript">
        function decode(x) {
            let s = "";

            for (let i = 0; i < x.length; i++) {
                let j = x.charCodeAt(i);
                if ((j >= 33) && (j <= 126)) {
                    s += String.fromCharCode(33 + ((j + 14) % 94));
                } else {
                    s += String.fromCharCode(j);
                }
            }

            return s;
        }

        $("#gateway_question").append("'.$locale['gateway_060'].' " + decode("'.$a.'") + " '.$multiplier.' " + decode("'.$b.'") + " '.$locale['gateway_061'].' '.$reply_method.'");
    </script>');

    $info = [
        'showform'         => TRUE,
        'gateway_question' => '<span id="gateway_question"></span>',
        'openform'         => openform('gatewayFrm', 'post', 'register.php'),
        'closeform'        => closeform(),
        'hiddeninput'      => form_hidden($honeypot_array[3]),
        'textinput'        => form_text('gateway_answer', "<span id='gateway_question'></span>", "", ['class'=>'mb-3', 'error_text' => $locale['gateway_064'], 'floating_label'=>TRUE, 'placeholder'=>'Answer', 'required' => 1]),
        'button'           => form_button('gateway_submit', $locale['gateway_065'], $locale['gateway_065'], ['class' => 'btn-primary btn-block']),
    ];
}

if (isset($_POST['gateway_answer'])) {
    $honeypot = '';

    if (isset($_SESSION["honeypot"])) {
        $honeypot = $_SESSION["honeypot"];
    }

    $_SESSION["validated"] = "False";

    if (isset($_POST["$honeypot"]) && $_POST["$honeypot"] == "") {
        $antibot = stripinput(strtolower($_POST["gateway_answer"]));

        if (isset($_SESSION["antibot"])) {
            if ($_SESSION["antibot"] == $antibot) {
                $_SESSION["validated"] = "True";
                redirect(BASEDIR."register.php");
            } else {
                $info['incorrect_answer'] = TRUE;
            }
        }
    }
}

display_gateway($info);
