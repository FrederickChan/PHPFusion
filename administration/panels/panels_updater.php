<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: panels_updater.php
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
require_once __DIR__ . '/../../maincore.php';

$locale = fusion_get_locale("", LOCALE . LOCALESET . "admin/panels.php");
pageAccess('P');

if ( isset($_GET['listItem']) && is_array($_GET['listItem']) ) {

    $side = get('side', FILTER_VALIDATE_INT);
    $id = get('id', FILTER_VALIDATE_INT);
    $list = get([ 'listItem' ]);

    $sql_status = ", panel_status=0";
    if ( $side > 0 ) {
        $sql_status = ", panel_status=1";
    }

    foreach ( $list as $position => $item ) {

        if ( isnum($position) && isnum($item) ) {

            $sql_cond = ( $item == $id ) ? $sql_status : '';

            $sql = 'UPDATE ' . DB_PANELS . ' SET panel_order="' . ( $position + 1 ) . '", panel_side="'.$side.'" ' . $sql_cond . ' WHERE panel_id=' . $item;

            dbquery($sql);
        }
    }

    header("Content-Type: text/html; charset=" . $locale['charset'] . "\n");
    echo "<div id='close - message'><div class='admin-message alert alert-info m-t-10'>" . $locale['488'] . "</div></div>";
}
