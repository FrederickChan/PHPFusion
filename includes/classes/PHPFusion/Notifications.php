<?php
namespace PHPFusion;


class Notifications {

    public static function get_notification_events() {

        $notifications_type = [
            'COMMENT' => 'Comments',
            'MENTIONS' => 'Mentions',
            'NEWSLETTER' => 'Newsletters',
            'BDAYS' => 'Birthdays',
            'GROUPS' => 'Groups',
            'EVENTS' => 'Events',
            'PM' => 'Messaging',
            'UPDATES' => 'Updates',
        ];

        return $notifications_type;
    }

    static $instance = NULL;
    static $type = [];
    static $icons = [];

    /**
     * Add instance
     *
     * @return static
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
            if (empty(self::$type)) {
                $filter = fusion_filter_hook('fusion_register_notifications');
                if (!empty($filter)) {
                    foreach ($filter as $val) {
                        self::$type[$val['type']] = $val['title'];
                        self::$icons[$val['type']] = $val['icon'];
                    }
                }
            }
        }

        return self::$instance;
    }

    public function getTypes() {
        return self::$type;
    }

    public function getIcons() {
        return self::$icons;
    }

    public function selectTypes($key = NULL) {
        return self::$type[$key] ?? '';
    }

    public function selectIcons($key = NULL) {
        return self::$icons[$key] ?? '';
    }

}