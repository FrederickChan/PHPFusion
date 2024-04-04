<?php

function home_up_settings($info) {

    $userdata = fusion_get_userdata();
    $settings = fusion_get_settings();
    ?>

    <!--profile_settings_home-->
    <div class="profile-header pt-3 mb-5">
        <div class="row align-items-center">
            <div class="col-xs-12 col-sm-8 col-md-8">
                <div class="d-flex align-items-center">
                    <div class="profile-avatar me-3">
                        <?php
                        echo display_avatar($userdata, '120px', 'rounded overflow-hide', FALSE, 'circle') ?>
                    </div>
                    <div class="profile-meta">
                        <div class="d-flex align-items-center">
                            <h4 class="mb-0 me-3"><?php
                                echo $userdata['user_name'] ?></h4>
                        </div>
                        <div>
                            <span class="text-lighter me-3">Email:</span><span class="text-body-emphasis"><?php
                                echo censortext($userdata['user_email']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4">
                        <span class="badge rounded-pill bg-light text-dark text-normal me-3 p-1">
                            <span class="badge bg-primary-subtle badge-circle">
                            <?php
                            echo get_image('ok', '', '', '', 'class="text-primary"') ?>
                            </span>
                            <span class="fs-6 text-normal">Verified</span>
                        </span>
                <span class="badge rounded-pill bg-light text-dark text-normal me-3 p-1">
                            <span class="badge bg-warning-subtle badge-circle">
                                <?php
                                echo get_image('certified', '', '', '', 'class="text-warning"') ?>
                            </span>
                            <span class="fs-6 text-normal"><?php
                                echo getuserlevel($userdata['user_level']) ?></span>
                        </span>
            </div>
        </div>
    </div>

    <?php
    echo openmodal('emailChange', 'Are you sure to change?', ['button_id' => 'email_change', 'static' => TRUE]) .
        '<div class="spacer-sm d-flex justify-content-center"><div class="circle bg-warning-soft p-3 d-inline-block">
                ' . get_image('warning') . '
            </div></div>' .
        'For security reasons, you are required to set up TOTP to change your email address.' .
        //modalfooter( '<button id="confirmEmailChange" data-bs-dismiss="modal" class="btn btn-primary">Continue</a>' ) .
        modalfooter('<button id="confirmEmailChange" data-bs-dismiss="modal" class="btn btn-primary">Continue</a>', TRUE) .
        closemodal() .
        openmodal('emailForm', 'Change Email', ['button_id' => 'confirmEmailChange', 'hidden' => TRUE, 'static' => TRUE]) .
        $info['user_email_form_open'] .
        $info['user_email'] .
        $info['user_email_change'] .
        modalfooter($info['user_email_submit'], TRUE) .
        $info['user_email_form_close'] .
        closemodal();
    ?>

    <div class="list-group">
        <!--            Email-->
        <div class="list-group-item">
            <div class="row align-items-center">
                <div class="col-xs-12 col-sm-4">
                    <div class="d-flex align-items-center">
                        <?php
                        echo get_image('email', '', '', '', 'class="me-3"') ?>
                        <h6 class="m-0 bold">Email</h6>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <?php
                    echo get_image('inbox', '', '', '', 'class="me-3"') ?>
                    <?php
                    echo censortext($userdata['user_email']) ?>
                </div>
                <div class="col-xs-12 col-sm-2">
                    <button id="email_change" class="btn btn-block btn-primary">Change</button>
                </div>
            </div>
        </div>
        <!--            Profile Info-->
        <div class="list-group-item">
            <div class="row align-items-center">
                <div class="col-xs-12 col-sm-4">
                    <div class="d-flex align-items-center">
                        <?php
                        echo get_image('profile', '', '', '', 'class="me-3"') ?>
                        <h6 class="m-0 bold">Profile Information</h6>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="d-flex align-items-center">
                        <?php
                        echo display_avatar($userdata, '30px', 'rounded me-2 overflow-hide', TRUE, 'rounded') ?>
                        <?php
                        echo display_name($userdata, 'profile-link', TRUE) ?>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-2">
                    <a href="<?php
                    echo $info['link']['details'] ?>" class="btn btn-block btn-primary">Change</a>
                </div>
            </div>
        </div>
        <!--            Account Field -->
        <div class="list-group-item">
            <div class="row align-items-center">
                <div class="col-xs-12 col-sm-4">
                    <div class="d-flex align-items-center">
                        <?php
                        echo get_image('qrcode', '', '', '', 'class="me-3"') ?>
                        <h6 class="m-0 bold">TOTP</h6>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <?php
                    echo get_image('ok', '', '', '', 'class="me-3 ' . ($info['user_totp_status'] ? '' : 'text-muted') . '"') ?>
                    <?php
                    echo $info['user_totp_status'] ? 'Activated' : 'Not Set' ?>
                </div>
                <div class="col-xs-12 col-sm-2">
                    <a href="<?php
                    echo $info['link']['totp'] ?>" class="btn btn-block btn-primary">Change</a>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-settings mt-3 mb-5">
        <h5 class="spacer-md">Advanced Settings</h5>
        <?php
        $col_css = 12 / (isset($info['link']['admin_password']) ? 4 : 3);
        ?>

        <div class="row equal-height">
            <div class="col-xs-12 col-sm-<?php
            echo $col_css ?>">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <?php
                        echo get_image('secured', '', '', '', 'class="mb-3"') ?>
                        <h5>Password</h5>
                        <div class="small mb-3">Login password management</div>
                        <div class="small mb-3">Last change: <?php
                            showdate('shortdate', $info['user_password_changed']) ?></div>
                        <a href="<?php
                        echo $info['link']['password'] ?>" class="btn btn-secondary  mt-auto">Change</a>
                    </div>
                </div>
            </div>
            <?php
            if (isset($info['link']['admin_password'])) : ?>
                <div class="col-xs-12 col-sm-<?php
                echo $col_css ?>">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <?php
                            echo get_image('secured', '', '', '', 'class="mb-3"') ?>
                            <h5>Admin password</h5>
                            <div class="mb-3">Admin password management</div>
                            <div class="small mb-3">Last change: <?php
                                showdate('shortdate', $info['user_admin_password_changed']) ?></div>
                            <a href="<?php
                            echo $info['link']['admin_password'] ?>" class="btn btn-secondary  mt-auto">Change</a>
                        </div>
                    </div>
                </div>
            <?php
            endif ?>
            <div class="col-xs-12 col-sm-<?php
            echo $col_css ?>">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <?php
                        echo get_image('user_deactivate', '', '', '', 'class="mb-3"') ?>
                        <h5>Account</h5>
                        <div class="small mb-3">Freeze or delete account</div>
                        <a href="<?php
                        echo $info['section']['close']['link'] ?>" class="btn btn-secondary  mt-auto">Change</a>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-<?php
            echo $col_css ?>">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <?php
                        echo get_image('passkey', '', '', '', 'class="mb-3"') ?>
                        <h5>Social accounts</h5>
                        <div class="small mb-3">Log in to <?php
                            echo $settings['sitename'] ?> with a third-party account
                        </div>
                        <a href="<?php
                        echo $info['link']['google'] ?>" class="btn btn-secondary disabled mt-auto">In development</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--editprofile_sub_idx-->
    <?php
}
