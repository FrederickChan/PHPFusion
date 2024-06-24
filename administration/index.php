<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: index.php
| Author: meangczac
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\AdminCP\AdminPanel;

require_once __DIR__ . '/../maincore.php';

fusion_load_script(INCLUDES . "jquery/admin.js");

define('ADMIN_DIR', ADMIN . fusion_get_aidlink());

define('ADMIN_CURRENT_DIR', ADMIN_DIR . (check_get('p') ? '&p=' . get('p') : ''));

const ADMIN_ERROR_DIR = ADMIN_DIR . '&p=404';

require_once __DIR__ . '/../themes/templates/admin_header.php'; // Load the theme

/* Reverse load breadcrumb support*/
require_once INCLUDES . 'breadcrumbs.php';

/* Loads up administration file */
$contents = load_administration(); // Load the file

// Load external files
if (isset($contents['files'])) {
    if (is_array($contents['files'])) {
        foreach ($contents['files'] as $file_path) {
            require_once $file_path;
        }
    } else if (is_file($contents['files'])) {
        require_once $contents['files'];
    }
}

/* Run breadcrumbs */
if (isset($contents['title'])) {

    if (isset($contents['settings'])) {
        add_breadcrumb(['link' => ADMIN . fusion_get_aidlink() . '&s=settings', 'title' => 'Settings']);
    }

    add_breadcrumb(array(
        'link' => (!empty($contents['link']) ? $contents['link'] : FUSION_SELF),
        'title' => $contents['title'],
    ));


}

/* Run data callback hook */
if ($filter = fusion_filter_hook('pf_admin_data')) {
    if (isset($filter[0])) {
        $filter = $filter[0];
    }
}

handle_admin_post($contents);
handle_admin_js($contents);


/* Run view hook -- echo */
if (isset($contents['view'])) {
    call_user_func($contents['view']);
}

require_once __DIR__ . '/../themes/templates/footer.php';

/**
 * @param       $s
 * @param false $use_forwarded_host
 *
 * @return string
 */
function url_origin($s, bool $use_forwarded_host = FALSE) {

    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : NULL);
    $host = $host ?? $s['SERVER_NAME'] . $port;

    return $protocol . '://' . $host;
}

/**
 * @param       $s
 * @param false $use_forwarded_host
 *
 * @return string
 */
function full_url($s, bool $use_forwarded_host = FALSE): string {

    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

/**
 * Do administration exit to 404 due to error
 *
 * @return void
 */
function admin_exit() {

    if (defined('FP_DEV') && FP_DEV === TRUE) {

        add_notice('info',
            '<pre style="background:none;border:0;color:#fff;">' . fusion_get_function('debug_print_backtrace',
                DEBUG_BACKTRACE_IGNORE_ARGS) . '</pre>');

        add_notice('danger', 'Whops! The page has encountered an error. Please refresh the page.');

    } else {
        redirect(ADMIN_ERROR_DIR);
    }
}

/**
 * Checks current post.
 *
 * @param $key
 *
 * @return bool
 */
function admin_post($key): bool {

    return (post('form_action') === $key);
}

/**
 * Register page hooks
 * @return array
 */
function load_administration(): array {

    $contents = [];

    if ($s = get('s')) {

        $acceptable = [
            'settings' => 'settings.php',
            'dashboard' => 'dashboard.php',
        ];

        if (isset($acceptable[$s])) {
//            $admin_link = ADMIN . $aidlink . '&s=' . $acceptable[$s];

            include ADMIN . $acceptable[$s];


        } else {

            include ADMIN . "contents/error_na.php";
        }

    } else if ($p = get('p')) {

        $result = dbquery("SELECT admin_rights, admin_title, admin_link, admin_page 
        FROM " . DB_ADMIN . " WHERE admin_rights=:page",
            [':page' => strtoupper($p)]);

        if (dbrows($result)) {

            $data = dbarray($result);

            $c_admin_link = $data['admin_link'];

            // Admin title no longer uses the DB entry as it is not multilocale.
            // $admin_title = $locale[$data['admin_rights']] ?? $data['admin_title'];

            $locale = fusion_get_locale();

            //            add_to_title($locale['global_201'] . $admin_title);

            if ($data['admin_page'] != '5') {
                $c_admin_link = ADMIN . 'contents/' . $data['admin_link'];
            }

            if (is_file($c_admin_link)) {

                // with the p
//                $admin_link = ADMIN_CURRENT_DIR;

                include $c_admin_link;

                // run page access
                pageaccess($data['admin_rights']);

                set_title('Settings' . $locale['global_201'] . $contents['title']);

            } else {
                // show page 404
                include ADMIN . 'contents/error_na.php';
            }
        } else {
            // show page 404
            include ADMIN . 'contents/error_na.php';
        }

    } else {
        // show page 404
        include ADMIN . 'contents/error_na.php';
    }

    if (isset($contents['actions']['post'])) {

        $_js = '';
        if (is_array($contents['actions']['post'])) {

            $button_names = [];

            if ($notices = getnotices(FUSION_SELF, FALSE)) {
                $notices = array_keys($notices);
                $button_class = 'btn-danger';
                $button_text = get_image('warning') . 'Error!';
                if (in_array('success', $notices)) {
                    $button_class = 'btn-success';
                    $button_text = get_image('success') . 'Success!';
                }
            }

            foreach ($contents['actions']['post'] as $button_name => $form) {

                $form_id = $form['id'];

                $button_name_js = 'button[name=\"' . $button_name . '\"]';

                $_js .= /** @lang JavaScript */
                    '$(document).on("click", "' . $button_name_js . '", function(event) {
                    event.preventDefault();
                    let button = $(this),                   
                        button_text = button.text(),
                        button_name = "' . $button_name . '",
                        event_url = "' . ADMIN . 'api/?api=nts",
                        form = $("#' . $form_id . '"),
                        form_action = $("input[name=\'form_action\'");
                        button.html("..."+ button_text);
                        button.prop("disabled", true);
                        
                        if (form.length) {
                            
                            // the $_POST name
//                            form_action.val("' . $button_name . '");
                            // add button to session
                            // $.post(event_url, {"name": "' . $button_name . '"});
                            
                            form.trigger("submit");
                            
                        } else {
                            alert("The form could not be submitted.");
                        }
                });';

                $button_names['name'][] = $button_name;
            }

            if (isset($button_class) && isset($button_text)) {
                $_js .= /** @lang JavaScript */
                    '$.post("' . ADMIN . 'api/?api=ntt", ' . json_encode($button_names) . ', function(response) {
                    
                    let button = $("button[name=\'"+ response +"\']");

                    if (button.length) {

                        let button_class = button.attr("class"),
                            button_ct = button.children("span"),
                            button_text = button_ct.text();

                             console.log(button_class);
                        
                            button.addClass("' . $button_class . '");
                            button_ct.html("' . $button_text . '");
                    
                            setTimeout(function(){
                                button.removeClass("' . $button_class . '");
                                button_ct.html(button_text);
                        }, 1300);
                    }
                });';
            }

            add_to_jquery($_js);
        }
    }

    $ap = AdminPanel::getInstance();
    // Set Admin Properties

    if (isset($contents['left_nav']) && is_callable($contents['left_nav'])) {
        $ap->setPageNav($contents['left_nav']);
    }

    if (isset($contents['fullwidth']) && $contents['fullwidth']) {
        $ap->setFullWidth($contents['fullwidth']);
    }

    if (!empty($contents['js']) && !is_callable($contents['js'])) {
        unset($contents['js']);
    }

    if (!empty($contents['button']) && is_callable($contents['button'])) {
        AdminPanel::getInstance()->setButtons(fusion_get_function($contents['button']));
    }

    if (!empty($contents['view']) && !is_callable($contents['view'])) {
        unset($contents['view']);
    }

    return $contents;
}

function handle_admin_post($contents) {
    if (!empty($contents['actions']['post'])) {
        if (is_array($contents['actions']['post'])) {
            foreach ($contents['actions']['post'] as $button_name => $form) { // savesettings, clearcache
                if (!empty($form['id']) && !empty($form['callback'])) {
                    if (post('form_id') == $form['id']) {
                        if (is_callable($form['callback'])) {
                            call_user_func($form['callback']);
                        }
                    }
                }
            }
        }
    }
}

function handle_admin_js($contents) {
    /* Run js hook */
    if (isset($contents['js'])) {
        if ($js = call_user_func($contents['js'])) {
            add_to_jquery($js);
        }
    }

}

/**
 * @param $data
 *
 * @return string
 */
function avatar_src($data): string {

    $avatar_image_path = IMAGES . 'avatars/no-avatar.jpg';
    if ($data['user_avatar'] && is_file(IMAGES . 'avatars/' . $data['user_avatar'])) {
        $avatar_image_path = IMAGES . 'avatars/' . $data['user_avatar'];
    }

    return $avatar_image_path;
}

/**
 * @param $data
 *
 * @return string
 */
function cover_src($data): string {

    $cover_image_path = IMAGES . 'default-cover.jpg';
    if ($data['user_cover'] && is_file(IMAGES . 'covers/' . $data['user_cover'])) {
        $cover_image_path = IMAGES . 'covers/' . $data['user_cover'];
    }

    return $cover_image_path;
}
