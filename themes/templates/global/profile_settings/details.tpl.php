<?php

function details_up_settings($info) {

    opentable( 'Public profile' );?>

    <!-- Profile_page-->
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
    <?php closetable();

}
