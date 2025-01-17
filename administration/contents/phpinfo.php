<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: phpinfo.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\BreadCrumbs;

defined('IN_FUSION') || exit;

//$_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0;

$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/phpinfo.php');

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_submit',
    'js'          => 'pf_js',
    'link'        => ( $admin_link ?? '' ),
    'settings'    => TRUE,
    'title'       => $locale['400'],
    'description' => '',
    'actions'     => [ 'post' => [ 'purge' => 'purgeFrm', 'purgefile' => 'purgeFrm', 'savesettings' => 'errorFrm' ] ]
];


function pf_view() {
    $locale = fusion_get_locale();

    $allowed_section = [ 'general', 'phpsettings', 'folderpermission', 'details' ];
    $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'],
                                                           $allowed_section) ? $_GET['section'] : 'general';

    $master_tab_title['title'][] = $locale['401'];
    $master_tab_title['id'][] = 'general';
    $master_tab_title['icon'][] = '';
    $master_tab_title['title'][] = $locale['420'];
    $master_tab_title['id'][] = 'phpsettings';
    $master_tab_title['icon'][] = '';
    $master_tab_title['title'][] = $locale['440'];
    $master_tab_title['id'][] = 'folderpermission';
    $master_tab_title['icon'][] = '';
    $master_tab_title['title'][] = $locale['450'];
    $master_tab_title['id'][] = 'details';
    $master_tab_title['icon'][] = '';

    $section = get('section');

    if ( $section ) {
        switch ( $_GET['section'] ) {
            case 'phpsettings':
                add_breadcrumb([ 'link' => FUSION_REQUEST, 'title' => $locale['420'] ]);
                break;
            case 'folderpermission':
                add_breadcrumb([ 'link' => FUSION_REQUEST, 'title' => $locale['440'] ]);
                break;
            case 'details':
                add_breadcrumb([ 'link' => FUSION_REQUEST, 'title' => $locale['450'] ]);
                break;
            default:
                break;
        }
    }

    echo opentab($master_tab_title, $section, 'general', TRUE, 'nav-tabs m-b-15');

    switch ($section ) {
        case 'phpsettings':
            phpsettings();
            break;
        case 'folderpermission':
            folderpermission();
            break;
        case 'details':
            details();
            break;
        default:
            general();
            break;
    }
    echo closetab();
    // closetable();
}

//General info
function general() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
    $settings = fusion_get_settings();
    $phpinfo = "<div class='table-responsive'><table class='table' id='folders'>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['402']."</td><td class='text-right'>".php_uname()."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['403']."</td><td class='text-right'>".$_SERVER['SERVER_SOFTWARE']."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['404']."</td><td class='text-right'>".phpversion()."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['405']."</td><td class='text-right'>".php_sapi_name()."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['406']."</td><td class='text-right'>".dbconnection()->getServerVersion()."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['406a']."</td><td class='text-right'>".str_replace('\\PHPFusion\\Database\Driver\\', '', \PHPFusion\Database\DatabaseFactory::getDriverClass())."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['407']."</td><td class='text-right'>".$settings['version']."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['408']."</td><td class='text-right'>".DB_PREFIX."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['409']."</td><td class='text-right'>".COOKIE_PREFIX."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['410']."</td><td class='text-right'>".stripinput($_SERVER['HTTP_USER_AGENT'])."</td></tr>\n";
    if (LANGUAGE !== 'English') {
        $phpinfo .= "<tr>\n<td colspan='2'>".$locale['411']."</td></tr>\n";
    }
    $phpinfo .= "</table>\n</div>";
    echo $phpinfo;
}

function phpsettings() {

    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
    //Check GD version
    if (function_exists('gd_info')) {
        $gd_ver = gd_info();
        preg_match('/[0-9]+.[0-9]+/', $gd_ver['GD Version'], $gd_ver);
    } else {
        $gd_ver = '';
    }
    $phpinfo = "<div class='table-responsive'><table class='table' id='folders'>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['423']."</td><td class='text-right'>".(ini_get('safe_mode') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['424']."</td><td class='text-right'>".(ini_get('register_globals') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['425']." GD (".$locale['431'].")</td><td class='text-right'>".(extension_loaded('gd') ? $locale['yes']." (".$gd_ver[0].")" : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['425']." zlib</td><td class='text-right'>".(extension_loaded('zlib') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['425']." Magic_quotes_gpc</td><td class='text-right'>".(ini_get('magic_quotes_gpc') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['426']."</td><td class='text-right'>".(ini_get('file_uploads') ? $locale['yes']." (".ini_get('upload_max_filesize')."B)" : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['428']."</td><td class='text-right'>".(ini_get('display_errors') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['429']."</td><td class='text-right'>".(ini_get('disable_functions') ? str_replace(',', ', ', ini_get('disable_functions')) : $locale['430'])."</td></tr>\n";
    $phpinfo .= "</table>\n</div>";
    echo $phpinfo;
}

function folderpermission() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
    $status = '';
    $folders = [
        //path => have to be writeable or not
        'administration/db_backups/' => TRUE,
        'images/'                    => TRUE,
        'images/imagelist.js'        => TRUE,
        'images/avatars/'            => TRUE,
        'images/smiley/'             => TRUE,
        'robots.txt'                 => TRUE,
        'config.php'                 => FALSE
    ];

    $infusions = \PHPFusion\Admins::getInstance()->getFolderPermissions();
    foreach ($infusions as $value) {
        $folders += $value;
    }

    add_to_head("<style type='text/css'>.passed {color:green;} .failed {color:red; text-transform: uppercase; font-weight:bold;}</style>\n");
    //Check file/folder writeable
    $i = 0;
    foreach ($folders as $folder => $writeable) {
        $status .= "<tr>\n<td style='width:50%'><i class='fa fa-folder fa-fw'></i> ".$folder."</td><td class='text-right'>";
        if (is_writable(BASEDIR.$folder) == TRUE) {
            $status .= "<span class='".($writeable == TRUE ? "passed" : "failed")."'>".$locale['441']."</span>";
        } else {
            $status .= "<span class='".($writeable == TRUE ? "failed" : "passed")."'>".$locale['442']."</span>";
        }
        $status .= " (".substr(sprintf('%o', fileperms(BASEDIR.$folder)), -4).")</td></tr>\n";
        $i++;
    }
    $phpinfo = "<div class='table-responsive'><table class='table' id='folders'>\n";
    $phpinfo .= $status;
    $phpinfo .= "</table>\n</div>";
    echo $phpinfo;
}

function details() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
    if (!stristr(ini_get('disable_functions'), "phpinfo")) {
        //Generating new phpinfo style, compatible with PHPFusion styles
        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        $phpinfo = preg_replace('%<h1.*>.*</h1>%', "<h3 class='tbl2'>$2</h3>", $phpinfo);
        $phpinfo = preg_replace('%<h2><a name="(.*)">(.*)</a></h2>%', "<h4 class='phpinfo forum-caption'>$2</h4>", $phpinfo);
        $phpinfo = preg_replace('%<h2>(.*)</h2>%', "<div class='forum-caption'>$1</div>", $phpinfo);
        $phpinfo = preg_replace('%<th colspan="2">(.*)</th>%', "<th colspan='2'><h5>$1</h5></th>", $phpinfo);
        $phpinfo = str_replace('<table>', '<table class="table">', $phpinfo);
        $phpinfo = str_replace("<h3 class='tbl2'></h3>", '', $phpinfo);
        $phpinfo = str_replace('class="h"', "class='tbl2 center'", $phpinfo);
        $phpinfo = str_replace('class="e"', "class='tbl2'", $phpinfo);
        $phpinfo = str_replace('class="v"', "class='tbl1'", $phpinfo);
    } else {
        $phpinfo = "<div class='admin-message'>".$locale['451']."</div>\n";
    }
    echo $phpinfo;
}
