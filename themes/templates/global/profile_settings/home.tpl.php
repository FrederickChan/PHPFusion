<?php

function home_up_settings( $info ) {

    $userdata = fusion_get_userdata();
    $settings = fusion_get_settings();

    ?>

    <!--profile_settings_home-->
    <div class="profile-idx pt-3 mb-2">
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

    <div class="list-group">
        <!--            Email-->
        <div class="list-group-item">
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
                    <button id="email_change" class="btn btn-sm btn-block btn-primary-soft">Change</button>
                </div>
            </div>
        </div>
        <!--            Profile Info-->
        <div class="list-group-item">
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
        <div class="list-group-item">
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

    <div class="profile-settings mt-3 mb-5">
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
                            <h5>Admin password</h5>
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
                        <h5>Account</h5>
                        <div class="text-smaller">Freeze or delete account</div>
                        <a href="<?php echo $info['section']['close']['link'] ?>" class="btn btn-primary-soft btn-sm mt-auto">Change</a>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-<?php echo $col_css ?>">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <?php echo show_icon( 'secure', 'icon-lg' ) ?>
                        <h5>Social accounts</h5>
                        <div class="text-smaller">Log in to <?php echo $settings['sitename'] ?> with a third-party account</div>
                        <a href="<?php echo $info['link']['google'] ?>" class="btn btn-primary-soft btn-sm mt-auto">Change</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--editprofile_sub_idx-->
    <?php
}
