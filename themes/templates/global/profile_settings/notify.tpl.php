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
        <div class="d-flex list-group-item align-items-center ps-3 pe-3 gap-3">
            <?php echo get_image( 'comments', '', '', '', 'me-2' ) ?>
            <?php echo $info['comments'] ?>
        </div>
        <div class="d-flex list-group-item align-items-center ps-3 pe-3 gap-3">
            <?php echo get_image( 'mentions', '', '', '', 'me-2' ) ?>
            <?php echo $info['mentions'] ?>
        </div>
        <div class="d-flex list-group-item align-items-center ps-3 pe-3 gap-3">
            <?php echo get_image( 'subscriptions', '', '', '', 'me-2' ) ?>
            <?php echo $info['subscriptions'] ?>
        </div>
        <div class="d-flex list-group-item align-items-center ps-3 pe-3 gap-3">
            <?php echo get_image( 'birthdays', '', '', '', 'me-2' ) ?>
            <?php echo $info['birthdays'] ?>
        </div>
        <div class="d-flex list-group-item align-items-center ps-3 pe-3 gap-3">
            <?php echo get_image( 'users', '', '', '', 'me-2' ) ?>
            <?php echo $info['groups'] ?>
        </div>
        <div class="d-flex list-group-item align-items-center ps-3 pe-3 gap-3">
            <?php echo get_image( 'events', '', '', '', 'me-2' ) ?>
            <?php echo $info['events'] ?>
        </div>
        <div class="d-flex list-group-item align-items-center ps-3 pe-3 gap-3">
            <?php echo get_image( 'messages_unread', '', '', '', 'me-2' ) ?>
            <?php echo $info['messages'] ?>
        </div>
        <div class="d-flex list-group-item align-items-center ps-3 pe-3 gap-3">
            <?php echo get_image( 'updates', '', '', '', 'me-2' ) ?>
            <?php echo $info['updates'] ?>
        </div>
        <div class="spacer-sm text-end">
            <?php echo $info['notify_button'] ?>
        </div>
        <?php echo $info['notify_closeform'] ?>
    </div>
    <?php
    closetable();
}
