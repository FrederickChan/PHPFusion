<?php


/*
 * Post Non-js
 */
function main_post() {

    $locale = fusion_get_locale();

    $inputData = [
        'siteintro'          => form_sanitizer(addslashes($_POST['siteintro']), '', 'siteintro'),
        'sitename'           => sanitizer("sitename", "", "sitename"),
        'sitebanner'         => form_sanitizer($_POST['sitebanner'], '', 'sitebanner'),
        'siteemail'          => form_sanitizer($_POST['siteemail'], '', 'siteemail'),
        'siteusername'       => form_sanitizer($_POST['siteusername'], '', 'siteusername'),
        'footer'             => form_sanitizer(addslashes($_POST['footer']), '', 'footer'),
        'site_protocol'      => form_sanitizer($_POST['site_protocol'], '', 'site_protocol'),
        'site_host'          => form_sanitizer($_POST['site_host'], '', 'site_host'),
        'site_path'          => form_sanitizer($_POST['site_path'], '', 'site_path'),
        'site_port'          => form_sanitizer($_POST['site_port'], '', 'site_port'),
        'description'        => form_sanitizer($_POST['description'], '', 'description'),
        'keywords'           => form_sanitizer($_POST['keywords'], '', 'keywords'),
        'opening_page'       => form_sanitizer($_POST['opening_page'], '', 'opening_page'),
        'default_search'     => form_sanitizer($_POST['default_search'], '', 'default_search'),
        'exclude_left'       => form_sanitizer($_POST['exclude_left'], '', 'exclude_left'),
        'exclude_upper'      => form_sanitizer($_POST['exclude_upper'], '', 'exclude_upper'),
        'exclude_aupper'     => form_sanitizer($_POST['exclude_aupper'], '', 'exclude_aupper'),
        'exclude_lower'      => form_sanitizer($_POST['exclude_lower'], '', 'exclude_lower'),
        'exclude_blower'     => form_sanitizer($_POST['exclude_blower'], '', 'exclude_blower'),
        'exclude_right'      => form_sanitizer($_POST['exclude_right'], '', 'exclude_right'),
        'exclude_user1'      => form_sanitizer($_POST['exclude_user1'], '', 'exclude_user1'),
        'exclude_user2'      => form_sanitizer($_POST['exclude_user2'], '', 'exclude_user2'),
        'exclude_user3'      => sanitizer('exclude_user3', '', 'exclude_user3'),
        'exclude_user4'      => sanitizer('exclude_user4', '', 'exclude_user4'),
        'logoposition_xs'    => sanitizer('logoposition_xs', '', 'logoposition_xs'),
        'logoposition_sm'    => sanitizer('logoposition_sm', '', 'logoposition_sm'),
        'logoposition_md'    => sanitizer('logoposition_md', '', 'logoposition_md'),
        'logoposition_lg'    => sanitizer('logoposition_lg', '', 'logoposition_lg'),
        'domain_server'      => sanitizer('domain_server', '', 'domain_server'),
        'privacy_policy'     => form_sanitizer($_POST['privacy_policy'], '', 'privacy_policy', count(fusion_get_enabled_languages()) > 1),
        'license_agreement'  => form_sanitizer($_POST['license_agreement'], '', 'license_agreement', count(fusion_get_enabled_languages()) > 1),
        'license_lastupdate' => ($_POST['license_agreement'] != fusion_get_settings('license_agreement') ? time() : fusion_get_settings('license_lastupdate'))
    ];

    $inputData += getServerConfig($inputData);

    if (fusion_safe()) {

        foreach ($inputData as $settings_name => $settings_value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                ':settings_value' => $settings_value,
                ':settings_name'  => $settings_name
            ]);
        }
        addnotice('success', $locale['900']);
        redirect(FUSION_REQUEST);
    }

}
