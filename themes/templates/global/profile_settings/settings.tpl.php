<?php

// Edit profile account settings
use PHPFusion\Panels;

function display_up_settings(array $info) {

    // Bootstrap 5 notion
    Panels::getInstance()->hidePanel('RIGHT');
    Panels::addPanel('navigation_panel', navigation_panel($info['section']), 1, USER_LEVEL_MEMBER, 1);

    // Only show on main page
    if (!empty($info['ref'])) : ?>

        <div class="profile-settings mb-5">
            <div class="d-flex align-items-center mb-4">
                <h5><a class="text-dark text-hover-underline" href="<?php echo BASEDIR . 'edit_profile.php' ?>">Account Settings</a></h5>
                <?php
                echo get_image('right') ?>
                <h5><?php
                    echo $info['page_title'] ?></h5>
            </div>
            <?php
            switch ($info['ref']) {
                case 'details':
                    if (!function_exists('details_up_settings')) :
                        require_once __DIR__ . '/details.tpl.php';
                    endif;

                    details_up_settings($info);
                    break;
                case 'password':
                    if (!function_exists('password_up_settings')) {
                        require_once __DIR__ . '/password.tpl.php';
                    }
                    password_up_settings($info);
                    break;
                case 'admin_password':
                    if (!function_exists('adminpassword_up_settings')) {
                        require_once __DIR__ . '/admin_password.tpl.php';
                    }
                    adminpassword_up_settings($info);
                    break;
            }
            ?>
        </div>

    <?php
    else:

        if (!function_exists('home_up_settings')) {
            require_once __DIR__ . '/home.tpl.php';
        }

        home_up_settings($info);

    endif;

}
