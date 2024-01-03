<?php

//const FLOATING_LABEL = TRUE;


function display_login_form( $info ) {

    $settings = fusion_get_settings();
    $locale = fusion_get_locale();

    if ( iMEMBER ) :

        redirect( BASEDIR . $settings['opening_page'] );

    else:

        define( 'LUNA_BODY_CLASS', 'login' );


        echo "<!--login_pre_idx-->";
        ?>
        <!--    login_pre_idx-->
        <div class="px-5">
            <h4 class="text-center mb-5"><?php echo $locale['global_100'] ?></h4>
            <div class="card mb-3">
                <div class="card-body">
                    <?php
                    echo openform( 'loginFrm', 'POST' ) .
                        '<div class="mb-3">' . $info['user_name'] . '</div>' .
                        '<div class="mb-3">
                        <span class="text-smaller bold pull-right">' . $info['forgot_password_link'] . '</span>
                        ' . $info['user_pass'] . '
                        </div>' .
                        $info['remember_me'] .
                        $info['login_button'] .
                        closeform();
                    // Facebook, Google Auth, etc.
                    if ( !empty( $info['connect_buttons'] ) ) :
                        ?>
                        <hr>
                        <?php
                        foreach ( $info['connect_buttons'] as $social_buttons ) :

                            echo $social_buttons;

                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
            <!--login_sub_idx-->
            <p class="text-center"><?php echo $info['registration_link'] ?></p>
        </div>
    <?php
    endif;

}
