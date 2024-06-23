<?php
// Settings page
defined('IN_FUSION') || exit;

$contents = array(
    'view'        => 'pf_view',
    'js'          => 'pf_js',
    'link'        => ($admin_link ?? ''),
    'title'       => 'General Settings',
    'description' => '',
);

require_once __DIR__."/contents/views/settings.php";
