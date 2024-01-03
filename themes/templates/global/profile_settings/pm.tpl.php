<?php

function pm_up_settings( $info ) {

    $locale = fusion_get_locale();

    opentable( $locale['u610'] );
    ?>
    <?php echo $info['pm_openform'] ?>
    <div class="d-flex align-items-center ps-3 pe-3 gap-3">
        <?php echo $info['pm_email'] ?>
    </div>
    <div class="d-flex align-items-center ps-3 pe-3 gap-3">
        <?php echo $info['pm_save_sent'] ?>
    </div>
    <div class="spacer-sm text-end">
        <?php echo $info['pm_button'] ?>
    </div>
    <?php echo $info['pm_closeform'] ?>
    <?php
    closetable();
}
