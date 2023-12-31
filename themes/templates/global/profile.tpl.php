<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: profile.tpl.php
| Author: Frederick MC Chan (Chan)
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

if ( !function_exists( 'display_register_form' ) ) {
    /**
     * Registration Form Template
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     */
    function display_register_form( array $info = [] ) {
        $locale = fusion_get_locale();

        echo "<!--HTML-->";
        opentable( $locale['u101'] );
        echo "<!--register_pre_idx-->";
        echo openform( 'registerFrm', 'POST' ) .
            $info['user_id'] .
            $info['user_name'] .
            $info['user_email'] .
            $info['user_avatar'] .
            $info['user_password'] .
            $info['user_admin_password'] .
            $info['user_custom'] .
            $info['validate'] .
            $info['terms'] .
            $info['button'] .
            closeform();
        echo "<!--register_sub_idx-->";
        closetable();
        echo "<!--//HTML-->";
    }
}

if ( !function_exists( 'display_profile_form' ) ) {
    /**
     * Edit Profile Form Template
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     */
    function display_profile_form( array $info = [] ) {
        $opentab = '';
        $closetab = '';
        if ( !empty( $info['tab_info'] ) ) {
            $opentab = opentab( $info['tab_info'], check_get( 'section' ) ? get( 'section' ) : 1, 'user-profile-form', TRUE );
            $closetab = closetab();
        }
        opentable( '' );
        echo $opentab;

        echo "<!--editprofile_pre_idx--><div id='profile_form' class='spacer-sm'>";
        echo openform( 'profileFrm', 'POST', FORM_REQUEST, ['enctype' => TRUE] );
        echo $info['user_id'];
        echo $info['user_name'];
        echo $info['user_firstname'];
        echo $info['user_lastname'];
        echo $info['user_addname'];
        echo $info['user_phone'];
        echo $info['user_email'];
        echo $info['user_hide_email'];
        echo $info['user_avatar'];
        echo $info['user_password'];
        echo $info['user_admin_password'];
        echo $info['user_custom'];
        echo $info['user_bio'];
        echo $info['validate'];
        echo $info['terms'];
        echo $info['button'];
        echo closeform();
        echo "</div><!--editprofile_sub_idx-->";

        echo $closetab;
        closetable();
    }
}


// New User Profile Proposals
if ( !function_exists( 'display_up_settings' ) ) {

    function navigation_panel( $info ) {

        if ( !empty( $info ) ) {
            $menu = '';
            $_get = get( 'section' );
            $i = 0;
            foreach ( $info as $key => $rows ) {

                $active = ( !$i && !$_get || $_get == $key ? ' active' : '' );

                $menu .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">'
                    . '<a class="nav-link d-flex mb-0' . $active . '" href="' . $rows['link'] . '" aria-selected="true" role="tab">' . $rows['title'] . '</a>'
                    . '</li>';

                $i++;
            }
        }

        return fusion_get_function( 'openside', '' )
            . '<ul class="nav nav-tabs nav-pills nav-pills-soft flex-column fw-bold gap-2 border-0" role="tablist">'
            . ( $menu ?? '' )
            . '</ul>'
            . fusion_get_function( 'closeside' );
    }

    // Edit profile account settings
    function display_up_settings( array $info ) {
        // Bootstrap 5 notion
        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();

        Panels::getInstance()->hidePanel( 'RIGHT' );
        Panels::addPanel( 'navigation_panel', navigation_panel( $info['section'] ), 1, USER_LEVEL_MEMBER, 1 );


        echo '<!--editprofile_pre_idx-->';
        ?>
        <?php if ( empty( $info['ref'] ) ) : ?>

            <div class="profile-idx pt-3 mb-5">
                <div class="row align-items-center">
                    <div class="col-xs-12 col-sm-8 col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="profile-avatar me-3">
                                <?php echo display_avatar( $userdata, '75px', '', FALSE, 'img-circle' ) ?>
                            </div>
                            <div class="profile-meta">
                                <div class="d-flex align-items-center">
                                    <h4 class="mb-0 me-3"><?php echo $userdata['user_name'] ?></h4>
                                    <span class="badge bg-primary-subtle">
                                    <a href="edit-username"><?php echo show_icon( 'edit', 'text-primary' ) ?></a>
                                </span>
                                </div>
                                <small>
                                    <span class="text-lighter me-3">Email:</span><span class="text-body-emphasis"><?php echo censortext( $userdata['user_email'] ) ?></span>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-4">
                <span class="badge rounded-pill bg-light text-dark text-normal me-3 p-1">
                    <span class="badge bg-primary-subtle badge-circle">
                        <?php echo show_icon( 'check-circle', 'text-primary icon-sm' ) ?>
                    </span>
                    <span class="fs-6 text-normal">Verified</span>
                </span>
                        <span class="badge rounded-pill bg-light text-dark text-normal me-3 p-1">
                <span class="badge bg-warning-subtle badge-circle">
                    <?php echo show_icon( 'verified', 'text-warning icon-sm' ) ?>
                </span>
                <span class="fs-6 text-normal"><?php echo getuserlevel( $userdata['user_level'] ) ?></span>
            </span>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="profile-settings mb-5">
        <div class="d-flex align-items-center mb-4">
            <h5>Account Settings</h5>
            <?php if ( !empty( $info['ref'] ) ) : ?>
                <span class="mb-2 ms-2 me-2"><?php echo show_icon( 'angle-right' ) ?></span>
                <h5><?php echo $info['page_title'] ?></h5>
            <?php endif ?>
        </div>

        <?php if ( $info['ref'] == 'details' ) : ?>
        <!-- Profile_page-->
        <?php openside( 'Profile Information' ) ?>
        <?php echo $info['form_open'] ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-4">
                <?php echo $info['user_firstname'] ?>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4">
                <?php echo $info['user_lastname'] ?>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-4">
                <?php echo $info['user_addname'] ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6">
                <?php echo $info['user_name'] ?>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6">
                <?php echo $info['user_name_display'] ?>
            </div>
        </div>
        <div class="row align-items-center">
            <?php echo $info['user_phone'] ?>
        </div>
        <?php echo $info['user_bio'] ?>
        <?php if ( !empty( $info['user_field'] ) ) : ?>
            <?php foreach ( $info['user_field'] as $field ) : ?>
                <?php if ( !empty( $field['fields'] ) && !empty( $field['title'] ) ) : ?>
                    <?php //openside( $field['title'] ) ?>
                    <?php echo '<h6 class="spacer-sm">' . $field['title'] . '</h6>' ?>
                    <?php foreach ( $field['fields'] as $field_inputs ) : ?>
                        <?php echo $field_inputs ?>
                    <?php endforeach ?>
                    <?php //closeside() ?>
                <?php endif ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php echo $info['button'] . $info['form_close'] ?>
        <?php closeside() ?>

    <?php elseif ( $info['ref'] == 'authenticator' ) : ?>
        <!--TOTP_page-->
        <?php if ( !isset( $info['totp_code_field'] ) ) : ?>

        <?php openside( 'Unbind two factor authenticator' ) ?>
        <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-9">
            <?php echo $info['totp_form_open'] ?>
            <?php echo $info['totp_email_field'] ?>
            <?php echo $info['totp_submit_button'] ?>
            <?php echo $info['totp_form_close'] ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-3">
            <div class="border border-start border-top-0 border-bottom-0 border-end-0 p-3">
                For your account safety, we will not show any public key used previously to setup your account
                two factor authentication smartphone. If you wish to setup two factor authentication again, you are required to unbind the previous key first.
            </div>
        </div>
        <?php closeside() ?>

        <?php else: ?>
            <?php openside( 'Setup two factor authenticator' ) ?>
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-9">
                    <h6>Step 1: Download a two factor authenticator app to your smartphone.</h6>
                    <div class="d-flex equal-height spacer-md w-100">
                        <div class="d-inline-block me-2">
                            <a href="<?php echo $info['link']['appstore'] ?>" target="_blank" class="btn btn-default d-flex align-content-between align-items-center gap-2">
                                <?php echo get_image( 'app_store', 'App Store', 'height:24px;' ) ?>
                                <div class="ms-2 d-flex flex-column justify-content-start">
                                    <div class="strong text-start">App Store</div>
                                    <small class="text-muted">Download from</small>
                                </div>
                            </a>
                        </div>
                        <a href="#" id="appstore-qr" class="btn btn-default d-flex align-items-center me-2">
                            <?php echo get_image( 'qrcode', 'Scan Code' ) ?>
                        </a>
                        <?php echo openmodal( 'scan-apple', 'Scan for Apple Appstore downloads', [
                            'button_id' => 'appstore-qr'
                        ] ) ?>
                        <div class="text-center">
                            <?php echo show_qrcode( $info['link']['appstore'] ) ?>
                        </div>
                        <?php echo closemodal() ?>

                        <div class="d-inline-block me-2">
                            <a href="<?php echo $info['link']['playstore'] ?>" target="_blank" class="btn btn-default d-flex align-content-between align-items-center gap-2">
                                <?php echo get_image( 'google_play', 'Google Play', 'height:24px;' ) ?>
                                <div class="ms-2 d-flex flex-column justify-content-start">
                                    <div class="strong text-start">Google Play</div>
                                    <small class="text-muted">Download from</small>
                                </div>
                            </a>
                        </div>
                        <a href="#" id="playstore-qr" class="btn btn-default d-flex align-items-center me-2">
                            <?php echo get_image( 'qrcode', 'Scan Code' ) ?>
                        </a>
                        <?php echo openmodal( 'scan-playstore', 'Scan for Google Playstore downloads', [
                            'button_id' => 'playstore-qr'
                        ] ) ?>
                        <div class="text-center">
                            <?php echo show_qrcode( $info['link']['playstore'] ) ?>
                        </div>
                        <?php echo closemodal() ?>
                    </div>
                    <h6>Step 2: Setup "Google Authenticator".</h6>
                    <span class="text-warning">Note: Please properly keep the Google verification key.</span>
                    <div class="spacer-md">
                        <img src="<?php echo $info['totp_qr_image'] ?>" alt="Scan with authenticator">
                        <p>Key: <code><?php echo $info['totp_key'] ?></code></p>
                    </div>

                    <h6>Step 3: Input the 6-digits dynamic code from your google authenticator.</h6>
                    <p>In the Google Authenticator, Click + to add new account. You may scan the QR code or enter provided key to add your account on Google Authenticator.
                    </p>
                    <div class="spacer-md card p-3">
                        <?php echo $info['totp_form_open'] ?>
                        <?php echo $info['totp_email_field'] ?>
                        <?php echo $info['totp_code_field'] ?>
                        <div class="text-end">
                            <?php echo $info['totp_submit_button'] ?>
                        </div>
                        <?php echo $info['totp_form_close'] ?>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="border border-start border-top-0 border-bottom-0 border-end-0 p-3">
                        <h5>Notes:</h5>
                        <p>Do not delete the Google verification code account in the Google Authenticator app, otherwise you will be restricted from account operations. If you are unable to enter the Google verification code due to phone loss, software uninstallation, and other similar reasons, please contact the site support admin.</p>
                        <p>If it keeps prompting wrong Google verification code, please check and calibrate the phone time. The current server time: <?php echo showdate( 'Y-m-d h:i:s Z', time() ) ?></p>
                        <p>Google authentication adds a second layer of protection to your account's safety. After enabling this feature, you will be required to enter the Google verification code everytime you log in, or do password changes. This feature is currently available for IOS and Android devices.</p>
                    </div>
                </div>
            </div>
            <?php closeside() ?>
        <?php endif ?>

        <?php elseif ( $info['ref'] == 'password' ) : ?>

        <!--    Password_page-->
        <?php openside( 'Change password' ) ?>
        <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-9">
            <?php echo $info['password_form_open'] ?>
            <?php echo $info['password_field'] ?>
            <?php echo $info['password_email_field'] ?>
            <div class="text-end">
                <?php echo $info['password_submit_button'] ?>
            </div>
            <?php echo $info['password_form_close'] ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-3">
            <div class="border border-start border-top-0 border-bottom-0 border-end-0 p-3">
                <h5>Notes:</h5>
                <?php echo $info['password_text'] ?>
            </div>
        </div>
        <?php closeside() ?>

        <?php elseif ( $info['ref'] == 'admin_password' ) : ?>

        <!--    Admin_password_page-->
        <?php openside( 'Change admin password' ) ?>
        <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-9">
            <?php echo $info['password_form_open'] ?>
            <?php echo $info['password_field'] ?>
            <?php echo $info['password_email_field'] ?>
            <div class="text-end">
                <?php echo $info['password_submit_button'] ?>
            </div>
            <?php echo $info['password_form_close'] ?>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-3">
            <div class="border border-start border-top-0 border-bottom-0 border-end-0 p-3">
                <h5>Notes:</h5>
                <?php echo $info['password_text'] ?>
            </div>
        </div>
        <?php closeside() ?>

    <?php else: ?>

        <div class="list-group">
            <!--            Email-->
            <div class="list-group-item py-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-xs-12 col-sm-4">
                        <div class="d-flex align-items-center">
                            <?php echo show_icon( 'email', 'text-dark me-3' ) ?>
                            <h6 class="m-0">Email</h6>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <?php echo show_icon( 'check-circle', 'text-success' ) ?>
                        <?php echo censortext( $userdata['user_email'] ) ?>
                    </div>
                    <div class="col-xs-12 col-sm-2">
                        <!--                        <button id="email_change" class="btn btn-sm btn-block btn-primary-soft"-->
                        <!--                                data-bs-toggle="modal" data-bs-target="#emailChangeModal">Change-->
                        <!--                        </button>-->
                        <button id="email_change" class="btn btn-sm btn-block btn-primary-soft">Change</button>
                    </div>
                </div>
            </div>
            <?php
            echo openmodal( 'emailChange', 'Are you sure to change?', ['button_id' => 'email_change', 'static' => TRUE] ) .
                '<div class="spacer-sm d-flex justify-content-center"><div class="circle bg-warning-soft p-3 d-inline-block">
                <img class="icon-md" src="' . get_image( 'warning' ) . '" alt="">
            </div></div>' .
                'For security reasons, you are required to set up TOTP to change your email address.' .
                //modalfooter( '<button id="confirmEmailChange" data-bs-dismiss="modal" class="btn btn-primary">Continue</a>' ) .
                modalfooter( '<button id="confirmEmailChange" data-bs-dismiss="modal" class="btn btn-primary">Continue</a>', TRUE ) .
                closemodal() .
                openmodal( 'emailForm', 'Change Email', ['button_id' => 'confirmEmailChange', 'hidden' => TRUE, 'static' => TRUE] ) .
                $info['user_email_form_open'] .
                $info['user_email'] .
                $info['user_email_change'] .
                modalfooter( $info['user_email_submit'], TRUE ) .
                $info['user_email_form_close'] .
                closemodal();
            ?>

            <!--            Profile Info-->
            <div class="list-group-item py-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-xs-12 col-sm-4">
                        <div class="d-flex align-items-center">
                            <?php echo show_icon( 'profile', 'text-dark me-3' ) ?>
                            <h6 class="m-0">Profile Information</h6>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="d-flex align-items-center">
                            <?php echo display_avatar( $userdata, '30px', 'me-2', TRUE, 'img-circle' ) ?>
                            <?php echo display_name( $userdata, 'profile-link', TRUE ) ?>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-2">
                        <a href="<?php echo $info['link']['details'] ?>" class="btn btn-sm btn-block btn-primary-soft">Change</a>
                    </div>
                </div>
            </div>
            <!--            Account Field -->
            <div class="list-group-item py-3">
                <div class="row align-items-center">
                    <div class="col-xs-12 col-sm-4">
                        <div class="d-flex align-items-center">
                            <?php echo show_icon( 'otp', 'me-3 text-dark' ) ?>
                            <h6 class="m-0">TOTP</h6>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <?php echo show_icon( 'check-circle', $info['user_totp_status'] ? 'text-success' : 'text-lighter' ) ?>
                        <?php echo $info['user_totp_status'] ? 'Activated' : 'Not Set' ?>
                    </div>
                    <div class="col-xs-12 col-sm-2">
                        <a href="<?php echo $info['link']['totp'] ?>" class="btn btn-sm btn-block btn-primary-soft">Change</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="profile-settings mb-5">
            <h5 class="spacer-md">Advanced Settings</h5>

            <?php
            $col_css = 12 / ( isset( $info['link']['admin_password'] ) ? 4 : 3 );
            ?>

            <div class="row equal-height">
                <div class="col-xs-12 col-sm-<?php echo $col_css ?>">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <?php echo show_icon( 'lock', 'icon-lg' ) ?>
                            <h5>Password</h5>
                            <div class="text-smaller">Login password management</div>
                            <div class="text-smaller text-muted mb-3">Last change: <?php showdate( 'shortdate', $info['user_password_changed'] ) ?></div>
                            <a href="<?php echo $info['link']['password'] ?>" class="btn btn-primary-soft btn-sm mt-auto">Change</a>
                        </div>
                    </div>
                </div>
                <?php if ( isset( $info['link']['admin_password'] ) ) : ?>
                    <div class="col-xs-12 col-sm-<?php echo $col_css ?>">
                        <div class="card">
                            <div class="card-body d-flex flex-column">
                                <?php echo show_icon( 'lock', 'icon-lg' ) ?>
                                <h6>Admin Password</h6>
                                <div class="text-smaller">Admin password management</div>
                                <a href="<?php echo $info['link']['admin_password'] ?>" class="btn btn-primary-soft btn-sm mt-auto">Change</a>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
                <div class="col-xs-12 col-sm-<?php echo $col_css ?>">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <?php echo show_icon( 'verified', 'icon-lg' ) ?>
                            <h6>Account Management</h6>
                            <div class="text-smaller">Freeze or delete account</div>
                            <a href="<?php echo $info['section']['close']['link'] ?>" class="btn btn-primary-soft btn-sm mt-auto">Change</a>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-<?php echo $col_css ?>">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <?php echo show_icon( 'secure', 'icon-lg' ) ?>
                            <h6>Google account binding</h6>
                            <div class="text-smaller">Log in to <?php echo $settings['sitename'] ?> with a third-party account</div>
                            <a href="<?php echo $info['link']['google'] ?>" class="btn btn-primary-soft btn-sm mt-auto">Change</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="profile-settings mb-5">
            <h5 class="mb-4">Login Status Management</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead class="small">
                    <tr>
                        <th class="col-sm-2">Time</th>
                        <th class="col-sm-3">Device</th>
                        <th class="col-sm-2">IP</th>
                        <th class="col-sm-3">Location</th>
                        <th class="col-sm-2">Operation</th>
                    </tr>
                    </thead>
                    <tbody class="small">
                    <tr>
                        <td>2013-12-07 09:53:46</td>
                        <td>PC / Windows 10 / Edge 119.0.0</td>
                        <td>175.137.171.150</td>
                        <td>Malaysia Selangor Shah Alam</td>
                        <td><a href="">Log out</a></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="profile-settings mb-5">
            <h5 class="mb-4">Login History</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead class="small">
                    <tr>
                        <th class="col-sm-2">Time</th>
                        <th class="col-sm-3">Device</th>
                        <th class="col-sm-2">IP</th>
                        <th class="col-sm-5">Location</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    <?php endif; ?>
        <!--editprofile_sub_idx-->
        <?php
    }

    // Close account template
    function display_up_close( array $info ) {
        $settings = fusion_get_settings();
        // we need a template storage and store it to db.
        Panels::getInstance()->hidePanel( 'RIGHT' );
        Panels::addPanel( 'navigation_panel', navigation_panel( $info['section'] ), 1, USER_LEVEL_MEMBER, 1 );

        opentable( 'Deactivating or deleting your account' );
        ?>
        <div class="text-start">If you wish to take a break from <?php echo $settings['sitename'] ?> you can temporarily deactivate this account. If you want to permanently delete your account, please take note that all data will be permanently lost and cannot be recovered once you confirm your deletion.</div>.
        <h6 class="strong">Before you go</h6>
        <ol>
        <li>Download a backup of your data <a href="" class="strong">here</a></li>
        <li>All your data will be <strong>permanently erased</strong> and you will lose all your data as a result</li>
        </ol>
        <?php echo $info['close_openform'] ?>
        <div class="list-group">
        <?php echo $info['close_options'] ?>
        </div>
        <div class="spacer-sm">
        <a href="<?php echo BASEDIR.'edit_profile.php' ?>" class="btn btn-default">Cancel</a>
        <?php echo $info['close_button'] ?>
        </div>
        <?php echo $info['close_closeform'] ?>

        <?php
        closetable();
    }

    function display_up_privacy( array $info ) {
        $settings = fusion_get_settings();
        // we need a template storage and store it to db.
        Panels::getInstance()->hidePanel( 'RIGHT' );
        Panels::addPanel( 'navigation_panel', navigation_panel( $info['section'] ), 1, USER_LEVEL_MEMBER, 1 );
    }

}


/**
 * Profile display view
 *
 * @param $info (array) - prepared responsive fields
 *              To get information of the current raw userData
 *              global $userFields; // profile object at profile.php
 *              $current_user_info = $userFields->getUserData(); // returns array();
 *              print_p($current_user_info); // debug print
 */
if ( !function_exists( 'display_user_profile' ) ) {
    function display_user_profile( $info ) {
        $locale = fusion_get_locale();

        add_to_css( '.cat-field .field-title > img{max-width:25px;}' );

        opentable( '' );
        echo '<section id="user-profile">';
        echo '<div class="row m-b-20">';
        echo '<div class="col-xs-12 col-sm-2">';
        $avatar['user_id'] = $info['user_id'];
        $avatar['user_name'] = $info['user_name'];
        $avatar['user_avatar'] = $info['core_field']['profile_user_avatar']['value'];
        $avatar['user_status'] = $info['core_field']['profile_user_avatar']['status'];
        echo display_avatar( $avatar, '130px', 'profile-avatar', FALSE, 'img-responsive' );

        if ( !empty( $info['buttons'] ) ) {
            echo '<a class="btn btn-success btn-block spacer-sm" href="' . $info['buttons']['user_pm_link'] . '">' . $locale['send_message'] . '</a>';
        }
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-10">';
        if ( !empty( $info['user_admin'] ) ) {
            $button = $info['user_admin'];
            echo '<div class="pull-right btn-group">
                    <a class="btn btn-sm btn-default" href="' . $button['user_susp_link'] . '">' . $button['user_susp_title'] . '</a>
                    <a class="btn btn-sm btn-default" href="' . $button['user_edit_link'] . '">' . $button['user_edit_title'] . '</a>
                    <a class="btn btn-sm btn-default" href="' . $button['user_ban_link'] . '">' . $button['user_ban_title'] . '</a>
                    <a class="btn btn-sm btn-default" href="' . $button['user_suspend_link'] . '">' . $button['user_suspend_title'] . '</a>
                    <a class="btn btn-sm btn-danger" href="' . $button['user_delete_link'] . '">' . $button['user_delete_title'] . '</a>
                </div>';
        }

        echo '<h2 class="m-0">' . $info['core_field']['profile_user_name']['value'] . '</h2>';
        echo $info['core_field']['profile_user_level']['value'];

        if ( !empty( $info['core_field'] ) ) {
            echo '<hr>';
            foreach ( $info['core_field'] as $field_id => $field_data ) {
                switch ( $field_id ) {
                    case 'profile_user_group':
                        echo '<div class="row cat-field">';
                        echo '<div class="col-xs-12 col-sm-3"><strong class="field-title">' . $locale['u057'] . '</strong></div>';
                        echo '<div class="col-xs-12 col-sm-9">';
                        if ( !empty( $field_data['value'] ) && is_array( $field_data['value'] ) ) {
                            $i = 0;
                            foreach ( $field_data['value'] as $group ) {
                                echo $i > 0 ? ', ' : '';
                                echo '<a href="' . $group['group_url'] . '">' . $group['group_name'] . '</a>';
                                $i++;
                            }
                        } else {
                            echo !empty( $locale['u117'] ) ? $locale['u117'] : $locale['na'];
                        }
                        echo '</div>';
                        echo '</div>';
                        break;
                    case 'profile_user_avatar':
                        $avatar['user_avatar'] = $field_data['value'];
                        $avatar['user_status'] = $field_data['status'];
                        break;
                    case 'profile_user_name':
                    case 'profile_user_level':
                        break;
                    default:
                        if ( !empty( $field_data['value'] ) ) {
                            echo '<div id="' . $field_id . '" class="row cat-field">';
                            echo '<div class="col-xs-12 col-sm-3"><strong class="field-title">' . $field_data['title'] . '</strong></div>';
                            echo '<div class="col-xs-12 col-sm-9">' . $field_data['value'] . '</div>';
                            echo '</div>';
                        }
                }
            }
        }

        echo '</div>';
        echo '</div>'; // .row

        if ( !empty( $info['section'] ) ) {
            $tab_title = [];
            foreach ( $info['section'] as $page_section ) {
                $tab_title['title'][$page_section['id']] = $page_section['name'];
                $tab_title['id'][$page_section['id']] = $page_section['id'];
                $tab_title['icon'][$page_section['id']] = $page_section['icon'];
            }

            $tab_active = tab_active( $tab_title, get( 'section' ) );

            echo '<div class="profile-section">';
            echo opentab( $tab_title, get( 'section' ), 'profile_tab', TRUE, 'nav-tabs', 'section', ['section'] );
            echo opentabbody( $tab_title['title'][get( 'section' )], $tab_title['id'][get( 'section' )], $tab_active, TRUE );

            if ( $tab_title['id'][get( 'section' )] == $tab_title['id'][1] ) {
                if ( !empty( $info['group_admin'] ) ) {
                    $group = $info['group_admin'];

                    echo '<div class="well m-t-10">';
                    echo $group['ug_openform'];
                    echo '<div class="row">';
                    echo '<div class="col-xs-12 col-sm-2">' . $group['ug_title'] . '</div>';
                    echo '<div class="col-xs-12 col-sm-8">' . $group['ug_dropdown_input'] . '</div>';
                    echo '<div class="col-xs-12 col-sm-2">' . $group['ug_button'] . '</div>';
                    echo '</div>';
                    echo $group['ug_closeform'];
                    echo '</div>';
                }
            }

            if ( !empty( $info['user_field'] ) ) {
                foreach ( $info['user_field'] as $category_data ) {
                    if ( !empty( $category_data['fields'] ) ) {
                        if ( isset( $category_data['fields'] ) ) {
                            foreach ( $category_data['fields'] as $field_data ) {
                                $fields[] = $field_data;
                            }
                        }

                        if ( !empty( $fields ) ) {
                            echo '<h4 class="cat-title text-uppercase">' . $category_data['title'] . '</h4>';

                            if ( isset( $category_data['fields'] ) ) {
                                foreach ( $category_data['fields'] as $field_id => $field_data ) {
                                    if ( !empty( $field_data['title'] ) ) {
                                        echo '<div id="field-' . $field_id . '" class="row cat-field m-b-5">';
                                        echo '<div class="col-xs-12 col-sm-3"><strong class="field-title">' . ( !empty( $field_data['icon'] ) ? $field_data['icon'] : '' ) . ' ' . $field_data['title'] . '</strong></div>';
                                        echo '<div class="col-xs-12 col-sm-9">' . $field_data['value'] . '</div>';
                                        echo '</div>';
                                    }
                                }
                            }

                            echo '<hr>';
                        }
                    }
                }
            } else {
                echo '<div class="text-center well">' . $locale['uf_108'] . '</div>';
            }

            echo closetabbody();
            echo closetab();
            echo '</div>';
        }

        echo '</section>';
        closetable();
    }
}


if ( !function_exists( 'display_gateway' ) ) {
    function display_gateway( $info ) {

        $locale = fusion_get_locale();

        if ( $info['showform'] ) {
            opentable( $locale['gateway_069'] );
            echo $info['openform'];
            echo $info['hiddeninput'];
            echo '<h3>' . $info['gateway_question'] . '</h3>';
            echo $info['textinput'];
            echo $info['button'];
            echo $info['closeform'];
            closetable();
        } else if ( !isset( $_SESSION["validated"] ) ) {
            echo '<div class="well text-center"><h3 class="m-0">' . $locale['gateway_068'] . '</h3></div>';
        }

        if ( isset( $info['incorrect_answer'] ) && $info['incorrect_answer'] == TRUE ) {
            opentable( $locale['gateway_069'] );
            echo '<div class="well text-center"><h3 class="m-0">' . $locale['gateway_066'] . '</h3></div>';
            echo '<input type="button" value="' . $locale['gateway_067'] . '" class="text-center btn btn-info spacer-xs" onclick="location=\'' . BASEDIR . 'register.php\'"/>';
            closetable();
        }
    }
}
