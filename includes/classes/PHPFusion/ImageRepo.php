<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ImageRepo.php
| Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion;

/**
 * A class to handle imagepaths
 */
class ImageRepo {
    // Flaws: Not having images in the theme will break the site. Even the files format are different. Developers have no options for CSS buttons.
    // If we change this now, it will break all the themes on main site repository. Only solution is to address this in a new version to force deprecate old themes.
    /**
     * @var string[]
     */
    /**
     * All cached paths
     *
     * @var string[]
     */
    private static $image_paths = [];

    /**
     * The state of the cache
     *
     * @var boolean
     */
    private static $cached = FALSE;

    /**
     * Cache installed smiley images from database
     *
     * @return array|null
     */
    private static $smiley_cache = NULL;

    /**
     * We will go with Font Awesome
     *
     * @var string[]
     */
    private static $glyphicons = [
        'plus' => 'fa-solid fa-plus',
        'minus' => 'fa-solid fa-minus',
        'up' => 'fa-solid fa-arrow-up',
        'down' => 'fa-solid fa-arrow-down',
        'left' => 'fa-solid fa-arrow-left',
        'right' => 'fa-solid fa-arrow-right',
        'caret-up' => 'fa-solid fa-caret-up',
        'caret-down' => 'fa-solid fa-caret-down',
        'caret-left' => 'fa-solid fa-caret-left',
        'caret-right' => 'fa-solid fa-caret-right',
        'angle-up' => 'fa-solid fa-angle-up',
        'angle-down' => 'fa-solid fa-angle-down',
        'angle-left' => 'fa-solid fa-angle-left',
        'angle-right' => 'fa-solid fa-angle-right',
        'apply' => 'fa-solid fa-check',
        'cancel' => 'fa-solid fa-ban',
        'reset' => 'fa-solid fa-rotate-left',
        'undo' => 'fa-solid fa-rotate-left',
        'back' => 'fa-solid fa-rotate-left',
        'redo' => 'fa-solid fa-rotate-right',
        'reply' => 'fa-solid fa-reply',
        'forward' => 'fa-solid fa-share-from-square',
        'first' => 'fa-solid fa-angle-left',
        'last' => 'fa-solid fa-angle-right',
        'next' => 'fa-solid fa-angle-double-right',
        'previous' => 'fa-solid fa-angle-double-left',
        'edit' => 'fa-solid fa-edit',
        'delete' => 'fa-solid fa-trash',
        'view' => 'fa-solid fa-eye',
        'more' => 'fa-solid fa-ellipsis-v',
        'filter' => 'fa-solid fa-filters',
        'asc' => 'fa-solid fa-sort-up',
        'desc' => 'fa-solid fa-sort-down',
        'move' => 'fa-solid fa-up-down-left-right',
        'maximize' => 'fa-solid fa-maximize',
        'minimize' => 'fa-solid fa-down-left-and-up-right-to-center',
        'user' => 'fa-solid fa-user',
        'admin' => 'fa-solid fa-user-secret',
        'profile' => 'fa-solid fa-id-badge',
        'user-groups' => 'fa-solid fa-users-rectangle',
        'user-active' => 'fa-solid fa-user-check',
        'user-joined' => 'fa-solid fa-calendar-circle-user',
        'user-banned' => 'fa-solid fa-user-xmark',
        'user-inactive' => 'fa-solid fa-user-clock',
        'forum-post' => 'fa-solid fa-messages',
        'forum-spam' => 'fa-solid fa-message-xmark',
        'forum-sticky' => 'fa-solid fa-message-arrow-up',
        'forum-question' => 'fa-solid fa-message-question',
        'forum-answer' => 'fa-solid fa-message-check',
        'forum-quote' => 'fa-solid fa-message-quote',
        'forum-attachments' => 'fa-solid fa-message-image',
        'forum-warning' => 'fa-solid fa-message-exclamation',
        'forum-reputation' => 'fa-solid fa-hundred-points',
        'forum-upvoted' => 'fa-solid fa-message-arrow-up',
        'forum-downvoted' => 'fa-solid fa-message-arrow-down',
        'vote' => 'fa-solid fa-check-to-slot',
        'unvote' => 'fa-solid fa-xmark-to-slot',
        'note' => 'fa-solid fa-note-sticky',
        'auto-bot' => 'fa-solid fa-message-bot',
        'comments' => 'fa-solid fa-comments',
        'comment' => 'fa-solid fa-comment',
        'poll' => 'fa-solid fa-square-poll-vertical',
        'games' => 'fa-solid fa-dice',
        'print' => 'fa-solid fa-print',
        'bill' => 'fa-solid fa-file-invoice',
        'items' => 'fa-solid fa-box-heart',
        'security' => 'fa-solid fa-shield-cross',
        'infusion' => 'fa-solid fa-magnet',
        'collection' => 'fa-solid fa-gift',
        'coins' => 'fa-solid fa-sack',
        'location' => 'fa-solid fa-location-dot',
        'code' => 'fa-solid fa-brackets-curly',
        'star' => 'fa-regular fa-star',
        'half-star' => 'fa-solid fa-half-star',
        'flag' => 'fa-regular fa-flag',
        'clock' => 'fa-regular fa-clock',
        'success' => 'fa-solid fa-badge-check',
        'warning' => 'fa-solid fa-triangle-exclamation',
        'danger' => 'fa-solid fa-light-emergency-on',
        'donation' => 'fa-solid fa-hands-holding-dollar',
        'import' => 'fa-solid fa-up-to-line',
        'export' => 'fa-solid fa-down-from-line',
        'time' => 'fa-solid fa-fa-clock',
        'duration' => 'fa-solid fa-clock-rotate-left',
        'locked' => 'fa-solid fa-lock',
        'unlocked' => 'fa-solid fa-lock-open',
        'login' => 'fa-solid fa-right-to-bracket',
        'logout' => 'fa-solid fa-right-from-bracket',
        'tech-support' => 'fa-solid fa-headset',
        'maintenance' => 'fa-solid fa-helmet-safety',
        'site-links' => 'fa-solid fa-sitemap',
        'bug' => 'fa-solid fa-bug',
        'contact' => 'fa-solid fa-square-phone',
        'covid' => 'fa-solid fa-virus-covid',
        'settings' => 'fa-solid fa-screwdriver-wrench',
        'download' => 'fa-solid fa-download',
        'pen' => 'fa-solid fa-pen',
    ];

    /**
     * Get all imagepaths
     *
     * @return string[]
     */
    public static function getImagePaths() {
        self::cache();

        return self::$image_paths;
    }

    /**
     * Fetch and cache all off the imagepaths
     */
    private static function cache() {
        if (self::$cached) {
            return;
        }

        self::$cached = TRUE;

        $settings = fusion_get_settings();

        //<editor-fold desc="imagePaths">
        // You need to + sign it, so setImage will work.
        self::$image_paths += [
            //A
            'add' => IMAGES . 'icons/add.svg',
            "app_store" => IMAGES . "icons/apple.svg",
            'archive' => IMAGES . 'icons/archive.svg',
            'auto_mode' => IMAGES . 'icons/auto_mode.svg',
            //B
            'birthdays' => IMAGES . 'icons/birthdays.svg',
            'bbcode_bold' => INCLUDES . 'bbcodes/images/b.svg',
            'bbcode_big' => INCLUDES . 'bbcodes/images/big.svg',
            'bbcode_i' => INCLUDES . 'bbcodes/images/i.svg',
            'bbcode_u' => INCLUDES . 'bbcodes/images/u.svg',
            'bbcode_smiley' => INCLUDES . 'bbcodes/images/smiley.svg',
            'bbcode_url' => INCLUDES . 'bbcodes/images/url.svg',
            'bbcode_small' => INCLUDES . 'bbcodes/images/small.svg',
            'bbcode_mail' => INCLUDES . 'bbcodes/images/mail.svg',
            'bbcode_img' => INCLUDES . 'bbcodes/images/img.svg',
            'bbcode_aleft' => INCLUDES . 'bbcodes/images/left.svg',
            'bbcode_acenter' => INCLUDES . 'bbcodes/images/center.svg',
            'bbcode_aright' => INCLUDES . 'bbcodes/images/right.svg',
            'bbcode_code' => INCLUDES . 'bbcodes/images/code.svg',
            'bbcode_quote' => INCLUDES . 'bbcodes/images/quote.svg',
            'block' => IMAGES . 'icons/block.svg',
            'brush' => IMAGES . 'icons/brush.svg',
            //C
            'certified' => IMAGES . 'icons/certified.svg',
            'close' => IMAGES . 'icons/deactivate.svg',
            'cross' => IMAGES . 'icons/cross.svg',
            'comments' => IMAGES . 'icons/comments.svg',
            'celebrations' => IMAGES . 'icons/celebrations.svg',
            'copy' => IMAGES . 'icons/copy.svg',

            //D
            "down" => IMAGES . "icons/down.svg",
            'delete' => IMAGES . 'icons/delete.svg',
            'download' => IMAGES . 'icons/download.svg',
            'dark_mode' => IMAGES . 'icons/dark_mode.svg',

            //E
            'events' => IMAGES . 'icons/events.svg',
            'email' => IMAGES . 'icons/email.svg',

            //F
            //G
            "google_play" => IMAGES . "icons/google-play.svg",

            //H
            //I
            'inbox' => IMAGES . 'icons/inbox.svg',
            "imagenotfound" => IMAGES . "imagenotfound.jpg",
            //J
            //K
            'ko' => IMAGES . 'icons/cancel.svg',
            //L
            "left" => IMAGES . "icons/left.svg",
            "logo" => $settings['sitebanner'],
            'light_mode' => IMAGES . 'icons/light_mode.svg',

            //M
            'mentions' => IMAGES . 'icons/mentions.svg',
            'messages_unread' => IMAGES . 'icons/unread.svg',
            'minus' => IMAGES . 'icons/minus.svg',
            'mail-read' => IMAGES . 'icons/envelope-read.svg',
            'mail-unread' => IMAGES . 'icons/envelope.svg',
            //N
            "noavatar" => IMAGES . "avatars/no-avatar.jpg",
            "no-avatar" => IMAGES . "avatars/no-avatar.jpg",
            "no-cover" => IMAGES . "covers/no-cover.png",
            'notification' => IMAGES . 'icons/notifications.svg',
            //O
            'ok' => IMAGES . 'icons/check_circle.svg',
            'outbox' => IMAGES . 'icons/outbox.svg',
            //P
            'password' => IMAGES . 'icons/password.svg',
            'passkey' => IMAGES . 'icons/passkey.svg',
            "panel_on" => IMAGES . "icons/panel_on.gif",
            "panel_off" => IMAGES . "icons/panel_off.gif",
            'privacy' => IMAGES . 'icons/privacy.svg',
            'profile' => IMAGES . 'icons/profile.svg',
            'posts' => IMAGES . 'icons/comment.svg',
            //Q
            'qrcode' => IMAGES . 'icons/qr.svg',
            //R
            'right' => IMAGES . 'icons/right.svg',

            //S
            'secured' => IMAGES . 'icons/secured.svg',
            'subscriptions' => IMAGES . 'icons/subscriptions.svg',
            //T
            //U
            'up' => IMAGES . "icons/up.svg",
            'updates' => IMAGES . 'icons/updates.svg',
            'upgrades' => IMAGES . 'icons/upgrades.svg',


            'user' => IMAGES . 'icons/profile.svg',
            'users' => IMAGES . 'icons/users.svg',
            'user_pending' => IMAGES . 'icons/find_user.svg',
            'user_admin' => IMAGES . 'icons/admin_user.svg',
            'user_add' => IMAGES . 'icons/add_user.svg',
            'user_archive' => IMAGES . 'icons/archive_user.svg',
            'user_deactivate' => IMAGES . 'icons/deactivate.svg',
            'user_ban' => IMAGES . 'icons/ban_user.svg',
            //V
            'visible' => IMAGES . 'icons/visible.svg',
            'non_visible' => IMAGES . 'icons/non_visible.svg',
            'lock_visible' => IMAGES . 'icons/visibility_lock.svg',

            //W
            'warning' => IMAGES . 'icons/warning.svg',
            //X
            //Y
            //Z
            'zoom' => IMAGES . 'icons/zoom.svg',
        ];
        //</editor-fold>
        $installedTables = [
            'blog' => defined('BLOG_EXISTS'),
            'news' => defined('NEWS_EXISTS'),
        ];

        $selects = "SELECT admin_image as image, admin_rights as name, 'ac_' as prefix FROM " . DB_ADMIN;
        $result = dbquery($selects);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $image = file_exists(ADMIN . "images/" . $data['image']) ? ADMIN . "images/" . $data['image'] : (file_exists(INFUSIONS . $data['image']) ? INFUSIONS . $data['image'] : ADMIN . "images/infusion_panel.png");
                if (empty(self::$image_paths[$data['prefix'] . $data['name']])) {
                    self::$image_paths[$data['prefix'] . $data['name']] = $image;
                }
            }
        }

        //smiley
        foreach (cache_smileys() as $smiley) {
            // set image
            if (empty(self::$image_paths["smiley_" . $smiley['smiley_text']])) {
                self::$image_paths["smiley_" . $smiley['smiley_text']] = IMAGES . "smiley/" . $smiley['smiley_image'];
            }
        }

        $selects_ = [];
        if ($installedTables['blog']) {
            $selects_[] = "SELECT blog_cat_image as image, blog_cat_name as name, 'bl_' as prefix FROM " . DB_BLOG_CATS . " " . (multilang_table("BL") ? " where " . in_group('blog_cat_language', LANGUAGE) : "");
        }

        if ($installedTables['news']) {
            $selects_[] = "SELECT news_cat_image as image, news_cat_name as name, 'nc_' as prefix FROM " . DB_NEWS_CATS . " " . (multilang_table("NS") ? " where " . in_group('news_cat_language', LANGUAGE) : "");
        }

        if (!empty($selects_)) {
            $union = implode(' union ', $selects_);
            $result = dbquery($union);
            while ($data = dbarray($result)) {
                switch ($data['prefix']) {
                    case 'nc_':
                    default :
                        $image = file_exists(INFUSIONS . 'news/news_cats/' . $data['image']) ? INFUSIONS . 'news/news_cats/' . $data['image'] : IMAGES . "imagenotfound.jpg";
                        break;
                    case 'bl_':
                        $image = file_exists(INFUSIONS . 'blog/blog_cats/' . $data['image']) ? INFUSIONS . 'blog/blog_cats/' . $data['image'] : IMAGES . "imagenotfound.jpg";
                        break;
                }
                // Set image
                if (empty(self::$image_paths[$data['prefix'] . $data['name']])) {
                    self::$image_paths[$data['prefix'] . $data['name']] = $image;
                }
            }
        }
    }

    /**
     * Get all registered icons
     *
     * @return string[]
     */
    public static function getIconList() {
        return self::$glyphicons;
    }

    /**
     * Get all registered images
     *
     * @return string[]
     */
    public static function getImageList() {
        return self::$image_paths;
    }

    /**
     * Get the imagepath or the html "img" tag
     *
     * @param string $image The name of the image.
     * @param string $alt "alt" attribute of the image
     * @param string $style "style" attribute of the image
     * @param string $title "title" attribute of the image
     * @param string $atts Custom attributes of the image
     *
     * @return string The path of the image if the first argument is given,
     * but others not. Otherwise, the html "img" tag
     */
    public static function getImage($image, $alt = "", $style = "", $title = "", $atts = "") {
        self::cache();
        $url = self::$image_paths[$image] ?? IMAGES . "icons/image_not_found.svg";
        if ($style) {
            $style = " style='$style'";
        }
        if ($title) {
            $title = " title='" . $title . "'";
        }

        if (strchr($url, '.svg')) {
            $icon = file_get_contents($url);
            return ($style or $title or $atts) ? "<span" . $style . $title . " " . $atts . ">" . $icon . "</span>" : $icon;
        }

        return ($alt or $style or $title or $atts)
            ? "<img src='" . $url . "' alt='" . $alt . "'" . $style . $title . " " . $atts . " />" :
            $url;
    }

    /**
     * @param        $name
     * @param string $class
     *
     * @return string
     */
    public static function showIcon(string $name, string $class = "", string $tooltip = "") {
        $icon = (self::$glyphicons[$name]) ?? $name;
        $tooltip = $tooltip ? 'data-toggle="tooltip" title="' . $tooltip . '"' : '';

        return '<i class="' . $icon . whitespace($class) . '" ' . $tooltip . '></i>';
    }

    public static function setIcon($name, $value) {
        self::$glyphicons[$name] = $value;
    }


    /**
     * Set a path of an image
     *
     * @param string $name
     * @param string $path
     */
    public static function setImage($name, $path) {
        self::$image_paths[$name] = $path;
    }

    /**
     * Replace a part in each path
     *
     * @param string $source
     * @param string $target
     */
    public static function replaceInAllPath($source, $target) {
        self::cache();
        foreach (self::$image_paths as $name => $path) {
            self::$image_paths[$name] = str_replace($source, $target, $path);
        }
    }

    /**
     * Given a path, returns an array of all files
     *
     * @param string $path
     *
     * @return array
     */
    public static function getFileList($path) {
        $image_list = [];
        if (is_dir($path)) {
            $image_files = makefilelist($path, ".|..|index.php", TRUE);
            foreach ($image_files as $image) {
                $image_list[$image] = $image;
            }
        }

        return $image_list;
    }

    public static function cacheSmileys() {
        if (self::$smiley_cache === NULL) {
            self::$smiley_cache = [];
            $result = cdquery('smileys_cache', "SELECT smiley_code, smiley_image, smiley_text FROM " . DB_SMILEYS);
            while ($data = cdarray($result)) {
                self::$smiley_cache[] = [
                    'smiley_code' => $data['smiley_code'],
                    'smiley_image' => $data['smiley_image'],
                    'smiley_text' => $data['smiley_text'],
                ];
            }
        }

        return self::$smiley_cache;
    }
}
