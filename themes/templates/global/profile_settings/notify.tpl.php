<?php

use PHPFusion\Panels;

function display_up_notification( $info ) {

    // we need a template storage and store it to db.
    Panels::getInstance()->hidePanel( 'RIGHT' );
    Panels::addPanel( 'navigation_panel', navigation_panel( $info['section'] ), 1, USER_LEVEL_MEMBER, 1 );

    opentable( 'Notification Settings' );
    ?>
    <?php echo fusion_get_settings( 'sitename' ) ?> may still send you important notifications about your account and content outside of your preferred notification settings scope.

    <div class="list-group spacer-md">
        <?php echo $info['notify_openform'] ?>
        <?php echo $info['comments'] ?>
        <?php echo $info['mentions'] ?>
        <?php echo $info['subscriptions'] ?>
        <?php echo $info['birthdays'] ?>
        <?php echo $info['groups'] ?>
        <?php echo $info['events'] ?>
        <?php echo $info['messages'] ?>
        <?php echo $info['updates'] ?>
        <div class="spacer-sm text-end">
            <?php echo $info['notify_button'] ?>
        </div>
        <?php echo $info['notify_closeform'] ?>
    </div>
    <?php
    closetable();
}
