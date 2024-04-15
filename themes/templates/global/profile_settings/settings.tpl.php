<?php
// Edit profile account settings
use PHPFusion\Panels;
Panels::getInstance()->hidePanel('RIGHT');
Panels::addPanel('navigation_panel', navigation_panel($info['section']), 1, USER_LEVEL_MEMBER, 1);

// Notification template
function display_up_notification($info) {
    echo fusion_get_template('up_notify', $info);
}

// Privacy template
function display_up_privacy(array $info) {

    if (!empty($info['ref'])) {
        switch ($info['ref']) {
            case 'authenticator':
                $info['privacy_form'] = fusion_get_template('up_privacy_twofactor', $info);
                break;
            case 'pm_options':
                $info['privacy_form'] = fusion_get_template('up_privacy_pm', $info);
                break;
            case 'privacy':
                $info['privacy_form'] = fusion_get_template('up_data', $info);
                break;
            case 'login':
                $info['privacy_form'] = fusion_get_template('up_privacy_login', $info);
                break;
            case 'blacklist':
                $info['privacy_form'] = fusion_get_template('up_privacy_blacklist', $info);
                break;
        }
    }

    echo fusion_get_template('up_privacy', $info);
}

// Close template
function display_up_close(array $info) {
    echo fusion_get_template('up_close', $info);
}

function display_up_settings(array $info) {

    switch ($info['ref']) {
        case 'details':
            $info['home_form'] = fusion_get_template('up_home_details', $info);
            break;
        case 'password':
            $info['home_form'] = fusion_get_template('up_home_password', $info);
            break;
        case 'admin_password':
            $info['home_form'] = fusion_get_template('up_home_adm_password', $info);
            break;
    }

    echo fusion_get_template('up_home', $info);

}
