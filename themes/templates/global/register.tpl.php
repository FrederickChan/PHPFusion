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

        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        ?>

        <div class="register">
            <div class="content">
                <!--register_pre_idx-->
                <h5 class="text-center w-100 mt-4 mb-4">Create an account</h5>
                <?php openside( '' ) ?>
                <?php echo $info['form_open'];

                if ( !check_get( 'validation' ) ) :
                    echo $info['user_name'] .
                        $info['user_email'] .
                        $info['user_password'] .
                        $info['user_custom'] .
                        $info['button'];
                else:
                    echo $info['validation'] .
                        $info['button'];
                endif;

                echo $info['form_close'];
                closeside();
                if ( !check_get( 'validation' ) ) :
                    ?>
                    <div class="text-center text-muted mb-4">
                        <?php echo $info['terms'] ?>
                    </div>
                <?php endif ?>
                <div class="text-center strong mt-5 mb-5"><?php echo strtr( $locale['u400'], ['[SITENAME]' => $settings['sitename']] ) ?>
                    <a href="<?php echo BASEDIR . 'login.php' ?>"><?php echo $locale['login'] ?></a>
                </div>
                <!--register_sub_idx-->
            </div>
        </div>
        <?php
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
