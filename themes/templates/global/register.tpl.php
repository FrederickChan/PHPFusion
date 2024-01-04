<?php

use PHPFusion\Panels;

defined( 'IN_FUSION' ) || exit;


/**
 * Registration Form Template
 * echo output design in compatible with Version 7.xx theme set.
 *
 * @param $info - the array output that is accessible for your custom requirements
 */
function display_register_form( array $info = [] ) {

    Panels::getInstance()->hideAll();

    $settings = fusion_get_settings();
    $locale = fusion_get_locale();
    ?>

    <div class="register">
        <div class="content">
            <!--register_pre_idx-->
            <h5 class="text-center w-100 mt-4 mb-4">Create an account</h5>
            <?php openside( '' ) ?>
            <?php echo $info['form_open'] .
                $info['user_name'] .
                $info['user_email'] .
                $info['user_password'] .
                $info['user_admin_password'] .
                $info['user_custom'] .
                $info['validate'] .
                $info['button'] .
                $info['form_close']
            ?>
            <?php closeside() ?>
            <div class="text-center text-muted mb-4">
                <?php echo $info['terms'] ?>
            </div>
            <div class="text-center strong mt-5 mb-5"><?php echo strtr( $locale['u400'], ['[SITENAME]' => $settings['sitename']] ) ?>
                <a href="<?php echo BASEDIR . 'login.php' ?>"><?php echo $locale['login'] ?></a>
            </div>
            <!--register_sub_idx-->
        </div>
    </div>
    <?php
}


/*
if ( !function_exists( 'display_register_form' ) ) {
/**
* Registration Form Template
* echo output design in compatible with Version 7.xx theme set.
*
* @param $info - the array output that is accessible for your custom requirements
*/
// function display_register_form( array $info = [] ) {
// $locale = fusion_get_locale();
//
// echo "<!--HTML--> ";
// opentable( $locale['u101'] );
// echo "<!--register_pre_idx--> ";
// echo openform( 'registerFrm', 'POST' ) .
// $info['user_id'] .
// $info['user_name'] .
// $info['user_email'] .
// $info['user_avatar'] .
// $info['user_password'] .
// $info['user_admin_password'] .
// $info['user_custom'] .
// $info['validate'] .
// $info['terms'] .
// $info['button'] .
// closeform();
// echo "<!--register_sub_idx--> ";
// closetable();
// echo "<!--//HTML--> ";
// }
//}

