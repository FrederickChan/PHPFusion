<?php

function adminpassword_up_settings( $info ) {
    ?>
    <!--    Adminpassword_page-->
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
    <?php closeside();
}
