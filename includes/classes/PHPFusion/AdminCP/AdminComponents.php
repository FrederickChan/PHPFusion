<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Jupiter Admin
| Filename: AdminComponents.php
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
namespace PHPFusion\AdminCP;

class AdminComponents {

    /* Components aside open & collapse variation */
    public function openSide($value, $collapse = FALSE, $class = '') {
        echo fusion_render(THEMES."templates/acp/",
                           'components.twig',
                           [
                               'macro'    => 'openside',
                               'value'    => $value,
                               'class'    => $class,
                               'collapse' => $collapse
                           ],
                           TRUE);
    }

    /* Components aside close */
    public function closeSide() {

        echo fusion_render(THEMES."templates/acp/", 'components.twig', [ 'macro' => 'closeside' ], TRUE);
    }

    /* Components table open */
    public function openTable($value) {

        echo fusion_render(THEMES."templates/acp/", 'components.twig', [ 'macro' => 'opentable', 'value' => $value ], TRUE);
    }

    /* Components table close */
    public function closeTable() {

        echo fusion_render(THEMES."templates/acp/", 'components.twig', [ 'macro' => 'closetable' ], TRUE);
    }

    /* Components grid open */
    public function openGrid($count, $class = '') {

        echo fusioN_render(THEMES."templates/acp/",
                           'components.twig',
                           [ 'macro' => 'opengrid', 'value' => $count, 'class' => $class ],
                           TRUE);
    }

    /* Components grid close */
    public function closeGrid() {

        echo fusioN_render(THEMES."templates/acp/", 'components.twig', [ 'macro' => 'closegrid' ], TRUE);
    }

}
