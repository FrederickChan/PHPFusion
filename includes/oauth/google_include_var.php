<?php

function google_auth_button() {
    $client_id = '110588727681-6akp7fbrfl8idm27h0gradg38d6ee7ds.apps.googleusercontent.com';

    add_to_footer('<script src="https://accounts.google.com/gsi/client" async defer></script>');
    add_to_jquery("
    // In your script section
    window.googleLogin = (response) => {
        $.post('" . INCLUDES . "oauth/google_include_auth.php', response).done(function(e) {
            var r = $.parseJSON(e);        
            if (r['response']) {
                if (r['response'] === 200) {
                    document.location.href = '" . BASEDIR . fusion_get_settings('opening_page') . "';
                } else if (r['response'] === 300) {
                    document.location.href = '" . BASEDIR . "login.php?auth=security_pin';
                } else {
                    document.location.href = '" . BASEDIR . "login.php';
                }
            }
        });
    };    
    ");


    return '
       <div id="g_id_onload"
     data-client_id="' . $client_id . '"
     data-context="signin"
     data-ux_mode="popup"     
     data-nonce=""
     data-auto_select="true" data-callback="googleLogin" data-itp_support="true">
</div>
    <div class="g_id_signin"
     data-type="standard"
     data-shape="rectangular"
     data-theme="filled_blue"
     data-text="continue_with"
     data-size="large"
     allow="identity-credentials-get"
     data-logo_alignment="left">
</div>
    ';
}


function google_login() {

    //fusion_login_registers


}

function google_logout() {
    $client_id = '110588727681-6akp7fbrfl8idm27h0gradg38d6ee7ds.apps.googleusercontent.com';
    add_to_footer('<script src="https://apis.google.com/js/platform.js" defer></script>');
    add_meta('google-signin-client_id', $client_id);

    add_to_jquery("
    function signOut() {
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function () {
      console.log('User signed out.');
    });
   
  }
  
  signOut();
  
    ");
}

//google_logout();

/**
 * @uses google_auth_button()
 */
fusion_add_hook('fusion_login_connectors', 'google_auth_button');
fusion_add_hook('fusion_login_registers', 'google_login');