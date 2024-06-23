<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Jupiter Admin
| Filename: acp_theme.php
| Author: meangczac
|+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\AdminCP\AdminPanel;
use PHPFusion\AdminCP\AdminComponents;
use PHPFusion\OutputHandler;

defined('IN_FUSION') || exit;

const BOOTSTRAP = 5;
const FONTAWESOME = TRUE;
const ADMIN_THEME_LOCALE = LOCALE . LOCALESET . "admin/admin_theme.php";

define('THEME_BODY', admin_theme_body());


/* Admin Panel */
function render_admin_panel() {
    echo AdminPanel::getInstance()->viewTheme();
}

/* Admin Login */
function render_admin_login() {
    AdminPanel::getInstance()->viewLogin();
}
/* Dashboard */
function render_admin_dashboard() {
    return AdminPanel::getInstance()->viewDashboard();
}

/* Gets admin theme body definition */
function admin_theme_body() {
    if (!check_admin_pass('')) {
        return '<body class="hold-transition lockscreen">';
    }
    return '<body class="hold-transition skin-blue sidebar-mini">';
}

/* Side */
function openside($value = FALSE, $collapse = FALSE, $class = '') {
    (new AdminComponents())->openSide($value, $collapse, $class);
}

/* Closeside */
function closeside() {
    (new AdminComponents())->closeSide();
}

/* Table */
function opentable($title = NULL, $class = NULL, $bg = TRUE) {
    //Firstcamp\AdminPanel::openTable($title, $class, $bg);
}

function closetable($bg = TRUE) {
    //Firstcamp\AdminPanel::closeTable($bg);
}

function opengrid($value = 3, $class= NULL) {
    (new AdminComponents())->openGrid($value, $class);
}

function closegrid() {
    (new AdminComponents())->closeGrid();
}

OutputHandler::addHandler(function ($output = '') {
    $color = !check_admin_pass('') ? 'd2d6de' : '3c8dbc';
    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#'.$color.'"/>', $output);
});

