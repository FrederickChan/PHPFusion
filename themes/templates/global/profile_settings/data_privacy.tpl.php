<?php

function privacy_up_settings( $info ) {

    openside( 'Manage privacy settings' );
    ?>
    <div class="row">
        <?php echo $info['privacy_openform'] ?>
        <?php echo $info['user_hide_email_input'] ?>
        <?php echo $info['user_hide_phone_input'] ?>
        <?php echo $info['user_hide_birthdate_input'] ?>
        <?php echo $info['user_hide_location_input'] ?>
        <div class="text-end">
            <?php echo $info['privacy_button'] ?>
        </div>
        <?php echo $info['privacy_closeform'] ?>
    </div>
    <?php
    closeside();

}
