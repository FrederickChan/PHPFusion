<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: login.tpl.php
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

use PHPFusion\Panels;

defined( 'IN_FUSION' ) || exit;

if ( !function_exists( "display_auth_form" ) ) {
    function display_auth_form( $info ) {
        echo fusion_get_template('login_auth', $info);
    }
}

function display_gateway( $info ) {

    Panels::getInstance()->hideAll();

    $locale = fusion_get_locale();

    if ( $info['showform'] ) : ?>

        <h5 class="text-center w-100 mb-4"><?php echo $locale['gateway_069'] ?></h5>
        <div class="card">
            <div class="card-body">
                <?php
                echo $info['openform'];
                echo $info['hiddeninput'];
                echo $info['textinput'];
                echo $info['button'];
                echo $info['closeform'];
                ?>
            </div>
        </div>

    <?php elseif ( !isset( $_SESSION["validated"] ) ) : ?>
        <div class="well text-center"><h3 class="m-0"><?php echo $locale['gateway_068'] ?></h3></div>
    <?php
    endif;

    if ( isset( $info['incorrect_answer'] ) && $info['incorrect_answer'] == TRUE ) :

        opentable( $locale['gateway_069'] );
        ?>
        <h5 class="mb-5"><?php echo $locale['gateway_066'] ?></h5>
        <a href="<?php echo BASEDIR . 'register.php' ?>" class="btn btn-default"><?php echo $locale['gateway_067'] ?></a>
        <!--        <input type="button" value="--><?php //echo $locale['gateway_067']
        ?><!--" class="text-center btn btn-info spacer-xs" onclick="location=--><?php //echo BASEDIR . 'register.php'
        ?><!--">-->
        <?php
        closetable();

    endif;
}

if ( !function_exists( 'display_login_form' ) ) {
    /**
     * Display Login form
     *
     * @param array $info
     */
    function display_login_form( array $info ) {

        echo fusion_get_template('login', $info);
    }
}
