<?php

function google_auth_button() {
    $client_id = '434148485097-20ld8fv0i01u20ich7v406fgd8moq1kn.apps.googleusercontent.com';
    add_to_footer( '<script src="https://apis.google.com/js/platform.js" defer></script>' );
    add_meta( 'google-signin-client_id', $client_id );
    add_to_css( "
    .abcRioButton {
        max-width:100%;
        min-width:100%;
        border-radius: 8px !important;
        overflow: hidden !important;
        padding: 1px !important;
        box-shadow:none !important;
    }
    .abcRioButton:hover {
        background: #5194ee !important;
    }
    .abcRioButtonContentWrapper {
        border-radius: 8px;
        overflow: hidden;
    }
    " );

    return '<div class="g-signin2 w-100" data-height="40" data-longtitle="true" data-scope="profile email" data-theme="dark" data-onsuccess="googleSuccess" data-onfailure="googleFailure"></div>';
}

/**
 * @uses google_auth_button()
 */
fusion_add_hook('fusion_login_connectors', 'google_auth_button');
