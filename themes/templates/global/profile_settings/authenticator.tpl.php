<?php

function twofactor_up_settings( $info ) {

    if ( !isset( $info['totp_code_field'] ) ) :
        openside( 'Unbind two factor authenticator' ) ?>
        <!--TOTP_page-->
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-9">
                <?php echo $info['totp_form_open'] ?>
                <?php echo $info['totp_email_field'] ?>
                <?php echo $info['totp_submit_button'] ?>
                <?php echo $info['totp_form_close'] ?>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3">
                <div class="border border-start border-top-0 border-bottom-0 border-end-0 p-3">
                    For your account safety, we will not show any public key used previously to setup your account
                    two factor authentication smartphone. If you wish to setup two factor authentication again, you are required to unbind the previous key first.
                </div>
            </div>
        </div>
        <?php closeside() ?>

    <?php else: ?>

        <?php openside( 'Setup two factor authenticator' ) ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-9">
                <h6>Step 1: Download a two factor authenticator app to your smartphone.</h6>
                <div class="d-flex equal-height spacer-md w-100">
                    <div class="d-inline-block me-2">
                        <a href="<?php echo $info['link']['appstore'] ?>" target="_blank" class="btn btn-default d-flex align-content-between align-items-center gap-2">
                            <?php echo get_image( 'app_store', 'App Store', 'height:24px;' ) ?>
                            <div class="ms-2 d-flex flex-column justify-content-start">
                                <div class="strong text-start">App Store</div>
                                <small class="text-muted">Download from</small>
                            </div>
                        </a>
                    </div>
                    <a href="#" id="appstore-qr" class="btn btn-default d-flex align-items-center me-2">
                        <?php echo get_image( 'qrcode', 'Scan Code' ) ?>
                    </a>
                    <?php echo openmodal( 'scan-apple', 'Scan for Apple Appstore downloads', [
                        'button_id' => 'appstore-qr'
                    ] ) ?>
                    <div class="text-center">
                        <?php echo show_qrcode( $info['link']['appstore'] ) ?>
                    </div>
                    <?php echo closemodal() ?>

                    <div class="d-inline-block me-2">
                        <a href="<?php echo $info['link']['playstore'] ?>" target="_blank" class="btn btn-default d-flex align-content-between align-items-center gap-2">
                            <?php echo get_image( 'google_play', 'Google Play', 'height:24px;' ) ?>
                            <div class="ms-2 d-flex flex-column justify-content-start">
                                <div class="strong text-start">Google Play</div>
                                <small class="text-muted">Download from</small>
                            </div>
                        </a>
                    </div>
                    <a href="#" id="playstore-qr" class="btn btn-default d-flex align-items-center me-2">
                        <?php echo get_image( 'qrcode', 'Scan Code' ) ?>
                    </a>
                    <?php echo openmodal( 'scan-playstore', 'Scan for Google Playstore downloads', [
                        'button_id' => 'playstore-qr'
                    ] ) ?>
                    <div class="text-center">
                        <?php echo show_qrcode( $info['link']['playstore'] ) ?>
                    </div>
                    <?php echo closemodal() ?>
                </div>
                <h6>Step 2: Setup "Google Authenticator".</h6>
                <span class="text-warning">Note: Please properly keep the Google verification key.</span>
                <div class="spacer-md">
                    <img src="<?php echo $info['totp_qr_image'] ?>" alt="Scan with authenticator">
                    <p>Key: <code><?php echo $info['totp_key'] ?></code></p>
                </div>

                <h6>Step 3: Input the 6-digits dynamic code from your google authenticator.</h6>
                <p>In the Google Authenticator, Click + to add new account. You may scan the QR code or enter provided key to add your account on Google Authenticator.
                </p>
                <div class="spacer-md card p-3">
                    <?php echo $info['totp_form_open'] ?>
                    <?php echo $info['totp_email_field'] ?>
                    <?php echo $info['totp_code_field'] ?>
                    <div class="text-end">
                        <?php echo $info['totp_submit_button'] ?>
                    </div>
                    <?php echo $info['totp_form_close'] ?>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3">
                <div class="border border-start border-top-0 border-bottom-0 border-end-0 p-3">
                    <h5>Notes:</h5>
                    <p>Do not delete the Google verification code account in the Google Authenticator app, otherwise you will be restricted from account operations. If you are unable to enter the Google verification code due to phone loss, software uninstallation, and other similar reasons, please contact the site support admin.</p>
                    <p>If it keeps prompting wrong Google verification code, please check and calibrate the phone time. The current server time: <?php echo showdate( 'Y-m-d h:i:s Z', time() ) ?></p>
                    <p>Google authentication adds a second layer of protection to your account's safety. After enabling this feature, you will be required to enter the Google verification code everytime you log in, or do password changes. This feature is currently available for IOS and Android devices.</p>
                </div>
            </div>
        </div>
        <?php closeside();

    endif;
}
