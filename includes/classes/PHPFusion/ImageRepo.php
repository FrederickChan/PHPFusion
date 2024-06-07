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
    ];

    /**
     * Get all imagepaths
     *
     * @return string[]
     */
    public static function getImagePaths() {
        self::cacheImages();

        return self::$image_paths;
    }

    /**
     * Fetch and cache all off the imagepaths
     */
    public static function cacheImages() {
        if (self::$cached) {
            return;
        }

        self::$cached = TRUE;

        $settings = fusion_get_settings();

        // You need to + sign it, so setImage will work.
        self::$image_paths += [
            //A
            "add" => IMAGES . "icons/add.svg",
            "app_store" => IMAGES . "icons/apple.svg",
            "archive" => IMAGES . "icons/archive.svg",
            "auto_mode" => IMAGES . "icons/auto_mode.svg",
            "admin" => IMAGES."icons/admin.svg",
            "admin_pass" => IMAGES."icons/adminpass.svg",

            //B
            "birthdays" => IMAGES . "icons/birthdays.svg",
            "bbcode_bold" => INCLUDES . "bbcodes/images/b.svg",
            "bbcode_big" => INCLUDES . "bbcodes/images/big.svg",
            "bbcode_i" => INCLUDES . "bbcodes/images/i.svg",
            "bbcode_u" => INCLUDES . "bbcodes/images/u.svg",
            "bbcode_smiley" => INCLUDES . "bbcodes/images/smiley.svg",
            "bbcode_url" => INCLUDES . "bbcodes/images/url.svg",
            "bbcode_small" => INCLUDES . "bbcodes/images/small.svg",
            "bbcode_mail" => INCLUDES . "bbcodes/images/mail.svg",
            "bbcode_img" => INCLUDES . "bbcodes/images/img.svg",
            "bbcode_aleft" => INCLUDES . "bbcodes/images/left.svg",
            "bbcode_acenter" => INCLUDES . "bbcodes/images/center.svg",
            "bbcode_aright" => INCLUDES . "bbcodes/images/right.svg",
            "bbcode_code" => INCLUDES . "bbcodes/images/code.svg",
            "bbcode_quote" => INCLUDES . "bbcodes/images/quote.svg",
            "block" => IMAGES . "icons/block.svg",
            "brush" => IMAGES . "icons/brush.svg",
            "bonus" => IMAGES . "icons/bonus.svg",
            "bookmark" => IMAGES . "icons/bookmark.svg",
            "blacklist" => IMAGES."icons/blacklist.svg",

            //C
            "cart" => IMAGES . "icons/cart.svg",
            "calendar" => IMAGES . "icons/calendar.svg",
            "calenda_today" => IMAGES . "icons/calendar_today.svg",
            "cake" => IMAGES . "icons/cake.svg",
            "certified" => IMAGES . "icons/certified.svg",
            "close" => IMAGES . "icons/deactivate.svg",
            "cross" => IMAGES . "icons/cross.svg",
            "comments" => IMAGES . "icons/comments.svg",
            "celebrations" => IMAGES . "icons/celebrations.svg",
            "copy" => IMAGES . "icons/copy.svg",
            "cog" => IMAGES . "icons/cog.svg",
            "crypto" => IMAGES . "icons/crypto.svg",
            "cash" => IMAGES . "icons/money.svg",
            "countdown" => IMAGES . "icons/countdown.svg",


            //D
            "down" => IMAGES . "icons/down.svg",
            "delete" => IMAGES . "icons/delete.svg",
            "delivery" => IMAGES . "icons/delivery.svg",
            "download" => IMAGES . "icons/download.svg",
            "dark_mode" => IMAGES . "icons/dark_mode.svg",
            "depart" => IMAGES . "icons/depart.svg",

            //E
            "events" => IMAGES . "icons/events.svg",
            "email" => IMAGES . "icons/email.svg",
            "edit" => IMAGES . "icons/pencil.svg",

            //F
            //G
            "google_play" => IMAGES . "icons/google-play.svg",
            "gender_male" => IMAGES . "icons/male.svg",
            "gender_female" => IMAGES . "icons/female.svg",
            "gender_trans" => IMAGES . "icons/trans.svg",

            "groups" => IMAGES . "icons/usergroups.svg",
            //H
            "heart" => IMAGES . "icons/heart.svg",
            "home" => IMAGES . "icons/home.svg",

            //I
            "inbox" => IMAGES . "icons/inbox.svg",
            "ip" => IMAGES . "icons/ip.svg",
            "imagenotfound" => IMAGES . "imagenotfound.jpg",
            //J
            "job" => IMAGES . "icons/work.svg",

            //K
            "ko" => IMAGES . "icons/cancel.svg",
            //L
            "left" => IMAGES . "icons/left.svg",
            "logo" => $settings["sitebanner"],
            "light_mode" => IMAGES . "icons/light_mode.svg",
            "location" => IMAGES . "icons/location.svg",
            "lock" => IMAGES . "icons/lock.svg",
            "like" => IMAGES . "icons/like.svg",

            //M
            "mentions" => IMAGES . "icons/mentions.svg",
            "messages_unread" => IMAGES . "icons/unread.svg",
            "minus" => IMAGES . "icons/minus.svg",

            "misc" => IMAGES . "icons/misc.svg",
            "mail-read" => IMAGES . "icons/envelope-read.svg",
            "mail-unread" => IMAGES . "icons/envelope.svg",
            "more" => IMAGES . "icons/hellip-h.svg",
            "more-h" => IMAGES . "icons/hellip-v.svg",
            "mood" => IMAGES . "icons/mood.svg",

            //N
            "noavatar" => IMAGES . "avatars/no-avatar.jpg",
            "no-avatar" => IMAGES . "avatars/no-avatar.jpg",
            "no-cover" => IMAGES . "covers/no-cover.png",
            "notification" => IMAGES . "icons/notifications.svg",
            //O
            "ok" => IMAGES . "icons/check_circle.svg",
            "outbox" => IMAGES . "icons/outbox.svg",
            //P
            "pin" => IMAGES . "icons/pin.svg",
            "password" => IMAGES . "icons/password.svg",
            "passkey" => IMAGES . "icons/passkey.svg",
            "panel_on" => IMAGES . "icons/panel_on.gif",
            "panel_off" => IMAGES . "icons/panel_off.gif",
            "privacy" => IMAGES . "icons/privacy.svg",
            "profile" => IMAGES . "icons/profile.svg",
            "posts" => IMAGES . "icons/comment.svg",
            "photos" => IMAGES . "icons/photo.svg",
            //Q
            "qrcode" => IMAGES . "icons/qr.svg",
            "preview" => IMAGES . "icons/preview.svg",
            "question" => IMAGES . "icons/question.svg",
            //R
            "right" => IMAGES . "icons/right.svg",
            "restart" => IMAGES . "icons/restart.svg",
            "remove" => IMAGES . "icons/eraser.svg",
            "registration" => IMAGES . "icons/registration.svg",
            //S
            "secured" => IMAGES . "icons/secured.svg",
            "subscriptions" => IMAGES . "icons/subscriptions.svg",
            "shop" => IMAGES . "icons/shop.svg",
            "school" => IMAGES . "icons/school.svg",
            "share" => IMAGES . "icons/share.svg",
            "settings" => IMAGES . "icons/settings.svg",
            "security" => IMAGES . "icons/security.svg",

            //T
            "tag" => IMAGES . "icons/label.svg",
            "trip" => IMAGES . "icons/luggage.svg",
            "totp" => IMAGES . "icons/totp.svg",
            "tour" => IMAGES . "icons/tour.svg",
            "timedate" => IMAGES . "icons/timedate.svg",

            //U
            "up" => IMAGES . "icons/up.svg",
            "updates" => IMAGES . "icons/updates.svg",
            "upgrades" => IMAGES . "icons/upgrades.svg",
            "user" => IMAGES . "icons/profile.svg",
            "users" => IMAGES . "icons/users.svg",
            "usergroups" => IMAGES."icons/usergroups.svg",
            "user_fields" => IMAGES."icons/userfields.svg",
            "user_logs" => IMAGES."icons/log.svg",
            "user_pending" => IMAGES . "icons/find_user.svg",
            "user_admin" => IMAGES . "icons/admin_user.svg",
            "user_add" => IMAGES . "icons/add_user.svg",
            "user_archive" => IMAGES . "icons/archive_user.svg",
            "user_deactivate" => IMAGES . "icons/deactivate.svg",
            "user_ban" => IMAGES . "icons/ban_user.svg",
            "user_level" => IMAGES . "icons/user_level.svg",
            "user_lastvisit" => IMAGES . "icons/last_visit.svg",
            "user_settings" => IMAGES . "icons/useradmin.svg",
            "unlock" => IMAGES . "icons/unlock.svg",
            //V
            "visible" => IMAGES . "icons/visible.svg",
            "non_visible" => IMAGES . "icons/non_visible.svg",
            "lock_visible" => IMAGES . "icons/visibility_lock.svg",

            //W
            "warning" => IMAGES . "icons/warning.svg",
            //X
            //Y
            //Z
            "zoom" => IMAGES . "icons/zoom.svg",
        ];

        //<editor-fold desc="imagePaths">
        // You need to + sign it, so setImage will work.
        self::$image_paths += [
            //A
            'add' => IMAGES . 'icons/add.svg',
            "app_store" => IMAGES . "icons/apple.svg",
            'archive' => IMAGES . 'icons/archive.svg',
            'auto_mode' => IMAGES . 'icons/auto_mode.svg',
            'alert' => IMAGES . 'icons/alert.svg',
            'at' => IMAGES . 'icons/at.svg',
            //B
            'ban' => IMAGES . 'icons/block.svg',
            "banner" => IMAGES."icons/banner.svg",
            'birthdays' => IMAGES . 'icons/birthdays.svg',
            "bbcode" => IMAGES . "icons/bbcode.svg",
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
            'bookmark' => IMAGES . 'icons/bookmark.svg',
            'beach' => IMAGES . 'icons/beach.svg',
            //C
            'certified' => IMAGES . 'icons/certified.svg',
            'close' => IMAGES . 'icons/deactivate.svg',
            'cross' => IMAGES . 'icons/cross.svg',
            'comments' => IMAGES . 'icons/comments.svg',
            'celebrations' => IMAGES . 'icons/celebrations.svg',
            'copy' => IMAGES . 'icons/copy.svg',
            'cafe' => IMAGES . 'icons/cafe.svg',


            //D
            "down" => IMAGES . "icons/down.svg",
            'delete' => IMAGES . 'icons/delete.svg',
            'download' => IMAGES . 'icons/download.svg',
            'dark_mode' => IMAGES . 'icons/dark_mode.svg',
            "db" => IMAGES . "icons/db.svg",

            //E
            'events' => IMAGES . 'icons/events.svg',
            'email' => IMAGES . 'icons/email.svg',
            'explore' => IMAGES . 'icons/explore.svg',
            "errors" => IMAGES."icons/errors.svg",
            //F
            'follow' => IMAGES . 'icons/follow.svg',
            'flag' => IMAGES . 'icons/flag.svg',
            'fruit' => IMAGES . 'icons/fruit.svg',
            'food' => IMAGES . 'icons/rice.svg',
            //G
            "google_play" => IMAGES . "icons/google-play.svg",

            //H
            'hide' => IMAGES . 'icons/hide.svg',
            'history' => IMAGES . 'icons/history.svg',
            //I
            'inbox' => IMAGES . 'icons/inbox.svg',
            "imagenotfound" => IMAGES . "imagenotfound.jpg",
            'icecream' => IMAGES . 'icons/icecream.svg',
            "infusion" => IMAGES."icons/infusion.svg",
            //J
            //K
            'ko' => IMAGES . 'icons/cancel.svg',
            //L
            "left" => IMAGES . "icons/left.svg",
            "logo" => $settings['sitebanner'],
            'light_mode' => IMAGES . 'icons/light_mode.svg',
            "language" => IMAGES . "icons/language.svg",

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
            'objects' => IMAGES . 'icons/objects.svg',

            //P
            'password' => IMAGES . 'icons/password.svg',
            'passkey' => IMAGES . 'icons/passkey.svg',
            "panel_on" => IMAGES . "icons/panel_on.gif",
            "panel_off" => IMAGES . "icons/panel_off.gif",
            'privacy' => IMAGES . 'icons/privacy.svg',
            'profile' => IMAGES . 'icons/profile.svg',
            'posts' => IMAGES . 'icons/comment.svg',
            'pet' => IMAGES . 'icons/pet.svg',
            "phpinfo" => IMAGES . "icons/phpinfo.svg",

            //Q
            'qrcode' => IMAGES . 'icons/qr.svg',
            //R
            'right' => IMAGES . 'icons/right.svg',
            'restart' => IMAGES . 'icons/restart.svg',
            "robot" => IMAGES."icons/robot.svg",

            //S
            'secured' => IMAGES . 'icons/secured.svg',
            'subscriptions' => IMAGES . 'icons/subscriptions.svg',
            'sports' => IMAGES . 'icons/sports.svg',
            'store' => IMAGES . 'icons/store.svg',
            "smileys" => IMAGES . "icons/smileys.svg",
            "security" => IMAGES . "icons/security.svg",

            //T
            'trophy' => IMAGES . 'icons/trophy.svg',
            //U
            'up' => IMAGES . "icons/up.svg",
            'updates' => IMAGES . 'icons/updates.svg',
            'upgrades' => IMAGES . 'icons/upgrades.svg',
            'unfollow' => IMAGES . 'icons/unfollow.svg',

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
     * @param string $alt   "alt" attribute of the image
     * @param string $style "style" attribute of the image
     * @param string $title "title" attribute of the image
     * @param string $atts  Custom attributes of the image
     *
     * @return string The path of the image if the first argument is given,
     * but others not. Otherwise, the html "img" tag
     */
    public static function getImage($image, $alt = "", $style = "", $title = "", $atts = "") {

        self::cacheImages();

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
        self::cacheImages();
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

    /**
     * @return array|null
     */
    public static function cacheSmileys() {
        if (self::$smiley_cache === NULL) {
            self::$smiley_cache = [];
            $result = dbquery("SELECT smiley_code, smiley_image, smiley_text FROM " . DB_SMILEYS);
            while ($data = dbarray($result)) {
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
