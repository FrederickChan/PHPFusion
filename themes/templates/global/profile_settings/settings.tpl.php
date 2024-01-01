<?php

// Edit profile account settings
use PHPFusion\Panels;

function display_up_settings( array $info ) {

    // Bootstrap 5 notion
    Panels::getInstance()->hidePanel( 'RIGHT' );
    Panels::addPanel( 'navigation_panel', navigation_panel( $info['section'] ), 1, USER_LEVEL_MEMBER, 1 );

    // Only show on main page
    if ( !empty( $info['ref'] ) ) : ?>

        <div class="profile-settings mb-5">
            <div class="d-flex align-items-center mb-4">
                <h5>Account Settings</h5>
                <span class="mb-2 ms-2 me-2"><?php echo show_icon( 'angle-right' ) ?></span>
                <h5><?php echo $info['page_title'] ?></h5>
            </div>
            <?php
            switch ( $info['ref'] ) {
                case 'details':

                    if ( !function_exists( 'details_up_settings' ) ) :
                        require_once __DIR__ . '/details.tpl.php';
                    endif;

                    details_up_settings( $info );
                    break;
                case 'authenticator':
                    if ( !function_exists( 'twofactor_up_settings' ) ) {
                        require_once __DIR__ . '/authenticator.tpl.php';
                    }

                    twofactor_up_settings( $info );
                    break;
                case 'password':
                    if ( !function_exists( 'password_up_settings' ) ) {
                        require_once __DIR__ . '/password.tpl.php';
                    }

                    password_up_settings( $info );
                    break;
                case 'admin_password':
                    if ( !function_exists( 'adminpassword_up_settings' ) ) {
                        require_once __DIR__ . '/admin_password.tpl.php';
                    }
                    adminpassword_up_settings( $info );
                    break;
                case 'privacy':
                    if ( !function_exists( 'privacy_up_settings' ) ) {
                        require_once __DIR__ . '/data_privacy.tpl.php';
                    }
                    privacy_up_settings( $info );
                    break;

            }
            ?>
        </div>

    <?php else:

        if ( !function_exists( 'home_up_settings' ) ) {
            require_once __DIR__ . '/home.tpl.php';
        }
        home_up_settings( $info );

    endif;

}
