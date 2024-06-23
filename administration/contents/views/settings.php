<?php

use PHPFusion\Admins;

/**
 * @param $icon_class
 * @param $color
 *
 * @return mixed
 */
function add_icon_color($icon_class, $color) {
    add_to_css('.'.$icon_class.' { background:'.$color.'!important;transition: all 0.3s cubic-bezier(0.1, 0.7, 1, 1 )} a.pf-settings-group:hover > .'.$icon_class.' {filter: brightness(50%);}');

    return $icon_class;
}

function pf_view() {
    // get the admin class and get the icons for the page
    $admin_pages = (new Admins())->getAdminPages();

    echo '<div class="pf-settings-title">System Settings</div>';
    opengrid(3);
    if (!empty($admin_pages[4])) {
        foreach ($admin_pages[4] as $page) {
            echo '<a href="'.$page['admin_link'].'" class="pf-settings-group">';
            echo '<span class="'.add_icon_color($page['admin_rights'].'-icon', $page['admin_icon_color']).'">'.get_image($page['admin_icon']).'</span>';
            echo '<div><strong>'.$page['admin_title'].'</strong><p>'.$page['admin_description'].'</p></div>';
            echo '</a>';
        }
    }
    closegrid();
    echo '<div class="pf-settings-title">System Preferences</div>';
    opengrid(3);
    if (!empty($admin_pages[3])) {
        foreach ($admin_pages[3] as $page) {
            echo '<a href="'.$page['admin_link'].'" class="pf-settings-group">';
            echo '<span class="'.add_icon_color($page['admin_rights'].'-icon', $page['admin_icon_color']).'">'.get_image($page['admin_icon']).'</i></span>';
            echo '<div><strong>'.$page['admin_title'].'</strong><p>'.$page['admin_description'].'</p></div>';
            echo '</a>';
        }
    }
    closegrid();
    echo '<div class="pf-settings-title">Membership</div>';
    opengrid(3);
    if (!empty($admin_pages[2])) {
        foreach ($admin_pages[2] as $page) {
            echo '<a href="'.$page['admin_link'].'" class="pf-settings-group">';
            echo '<span class="'.add_icon_color($page['admin_rights'].'-icon', $page['admin_icon_color']).'">'.get_image($page['admin_icon']).'</span>';
            echo '<div><strong>'.$page['admin_title'].'</strong><p>'.$page['admin_description'].'</p></div>';
            echo '</a>';
        }
    }
    closegrid();
    echo '<div class="pf-settings-title">PHPFusion</div>';
    opengrid(3);
    if (!empty($admin_pages[6])) {
        foreach ($admin_pages[6] as $page) {
            echo '<a href="'.$page['admin_link'].'" class="pf-settings-group">';
            echo '<span class="'.add_icon_color($page['admin_rights'].'-icon', $page['admin_icon_color']).'">'.get_image($page['admin_icon']).'</span>';
            echo '<div><strong>'.$page['admin_title'].'</strong><p>'.$page['admin_description'].'</p></div>';
            echo '</a>';
        }
    }
    closegrid();
}
