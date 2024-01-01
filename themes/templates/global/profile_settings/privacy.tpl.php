<?php

use PHPFusion\Panels;

function display_up_privacy( array $info ) {

    // we need a template storage and store it to db.
    Panels::getInstance()->hidePanel( 'RIGHT' );
    Panels::addPanel( 'navigation_panel', navigation_panel( $info['section'] ), 1, USER_LEVEL_MEMBER, 1 );

    if ( !empty( $info['ref'] ) ) {
        switch ( $info['ref'] ) {
            case 'login':
                if ( !function_exists( 'login_up_settings' ) ) {
                    require_once __DIR__ . '/login.tpl.php';
                }
                login_up_settings( $info );
                break;
            case 'blacklist':
                if ( !function_exists( 'blacklist_up_settings' ) ) {
                    require_once __DIR__ . '/blacklist.tpl.php';
                }
                blacklist_up_settings( $info );
                break;
        }
    } else {

        opentable( 'Privacy and safety' );
        ?>
        See information about your account, download an archive of your data, or learn about your account deactivation options.

        <div class="list-group spacer-md">

            <div class="list-group-item d-flex align-items-center">
                <div class="pe-3"><h6 class="mb-0">Use two-factor authentication</h6>
                    <p class="small mb-0">
                        Add an extra layer of protection to your account by requiring password and a verification code sent to your smartphone device.
                    </p>
                </div>
                <div class="ms-auto">
                    <a class="btn btn-primary" href="<?php echo $info['link']['totp'] ?>">Change</a>
                </div>
            </div>
            <div class="list-group-item d-flex align-items-center">
                <div class="pe-3"><h6 class="mb-0">Manage your privacy settings</h6>
                    <p class="small mb-0">
                        Control how much personal information you share with others.
                    </p>
                </div>
                <div class="ms-auto">
                    <a class="btn btn-primary" href="<?php echo $info['link']['privacy'] ?>">Change</a>
                </div>
            </div>
            <div class="list-group-item d-flex align-items-center">
                <div class="pe-3"><h6 class="mb-0">Login acitivty</h6>
                    <p class="small mb-0">
                        Manage your login activity across all devices
                    </p>
                </div>
                <div class="ms-auto">
                    <a class="btn btn-primary" href="<?php echo $info['link']['login'] ?>">View</a>
                </div>
            </div>
            <div class="list-group-item d-flex align-items-center">
                <div class="pe-3"><h6 class="mb-0">Blacklist</h6>
                    <p class="small mb-0">
                        Manage and block users from further interaction with your account
                    </p>
                </div>
                <div class="ms-auto">
                    <a class="btn btn-primary" href="<?php echo $info['link']['blacklist'] ?>">View</a>
                </div>
            </div>
        </div>
        <?php
        closetable();
    }


}
