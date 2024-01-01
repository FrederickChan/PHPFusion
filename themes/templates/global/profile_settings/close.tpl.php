<?php

use PHPFusion\Panels;

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
