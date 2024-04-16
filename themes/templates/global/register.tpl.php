<?php

use PHPFusion\Panels;

defined( 'IN_FUSION' ) || exit;
Panels::getInstance()->hideAll();

/**
 * Registration Form Template
 * echo output design in compatible with Version 7.xx theme set.
 *
 * @param $info - the array output that is accessible for your custom requirements
 */
if (!function_exists('display_register_form')) {
    function display_register_form( array $info = [] ) {
        Panels::getInstance()->hideAll();
        echo fusion_get_template('register', $info);
    }
}

if ( !function_exists( 'display_gateway' ) ) {
    function display_gateway( $info ) {

        $locale = fusion_get_locale();
        ?>
        <div class="register">
            <div class="content">
                <?php
                opentable('');
                if ( $info['showform'] ) {
                    // $locale['gateway_069']

                    echo $info['openform'];
                    echo $info['hiddeninput'];
                    echo '<h5>' . $info['gateway_question'] . '</h5>';
                    echo $info['textinput'];
                    echo $info['button'];
                    echo $info['closeform'];
                    closetable();
                } else if ( !isset( $_SESSION["validated"] ) ) {
                    echo '<h5 class="m-0">' . $locale['gateway_068'] . '</h5>';
                }
                if ( isset( $info['incorrect_answer'] ) && $info['incorrect_answer'] == TRUE ) {
                    //opentable( $locale['gateway_069'] );
                    echo '<div class="well text-center"><h5 class="m-0">' . $locale['gateway_066'] . '</h5></div>';
                    echo '<input type="button" value="' . $locale['gateway_067'] . '" class="btn btn-block btn-default spacer-xs" onclick="location=\'' . BASEDIR . 'register.php\'"/>';

                }
                closetable();
                ?>
            </div>
        </div>
        <?php
    }
}
