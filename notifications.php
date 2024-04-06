<?php

use Defender\Token;
use PHPFusion\Panels;

require_once __DIR__ . '/maincore.php';
require_once FUSION_HEADER;

function get_notification_events() {

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

function get_notification_type($key) {
    static $type = [];
    if (empty($type)) {
        $filter = fusion_filter_hook('fusion_register_notifications');
        if (!empty($filter)) {
            foreach ($filter as $val) {
                $type[$val['type']] = $val['title'];
            }
        }
    }

    return $type[$key] ?? '';
}


//fusion_update_table(DB_USER_NOTIFICATIONS);
//send_notice(1, 'Download Available', 'A new download updates are available now, you can participate in the M-Day free airdrop event. The event will end at 2024-04-06 14:00:00(UTC+08:00). Come and register to join!',
//    'DL', 'EVENTS');

/**
 * Development issues:
 * Add a db - have a tag
 * Registers a prefix with an infusion, have a category
 * for items, each infusion have a item that is undeletable. this way everyone can share and read? no.
 * message everyone with that entry
 */
// Development issues
$title = 'All Notifications';

if ($notification_types = fusion_filter_hook('fusion_register_notifications')) {
    $c_arr['notification_types'] = sorter($notification_types, 'title');
    $table_break = fusion_get_function('tablebreak');
    $table_close = fusion_get_function('closeside');

    if (!empty($c_arr['notification_types'])) {
        $output = fusion_get_function('openside', 'Notifications');
        $output .= '<a href="' . BASEDIR . 'notifications.php"><span class="me-2">' . get_image('notification', '') . '</span>All Notifications</a>';
        $output .= '<hr>';
        foreach ($c_arr['notification_types'] as $rows) {
            $count = dbcount("('notify_id')", DB_USER_NOTIFICATIONS, '');
            $output .= '<a href="' . BASEDIR . 'notifications.php?type=' . $rows['type'] . '"><span class="me-2">' . ($rows['icon'] ?? get_image('notification', '')) . '</span>' . $rows['title'] . '</a>';
            $output .= '<hr>';

            if (get('type') == $rows['type']) {
                $title = $rows['title'];
            }

        }
        $output .= $table_close;
    }

    $content = $output;

    Panels::addPanel('notification_types', $content, 1, USER_LEVEL_MEMBER);
}

$user_id = fusion_get_userdata('user_id');

$limit = 8;
$param = [':uid' => $user_id];
$sql = "SELECT * FROM " . DB_USER_NOTIFICATIONS . " WHERE notify_user=:uid ORDER BY notify_datestamp DESC";
$count_sql = "notify_user=:uid";
if (check_get('type')) {
    $sql = "SELECT * FROM " . DB_USER_NOTIFICATIONS . " WHERE notify_user=:uid AND notify_type=:type";
    $param = [
        ':type' => get('type'),
        ':uid' => $user_id,
    ];
    $count_sql = "notify_user=:uid AND notify_type=:type";
}

$total = dbcount("(notify_id)", DB_USER_NOTIFICATIONS, $count_sql, $param);

$rowstart = get_rowstart('rowstart', $total);

$param += [
    ':rowstart' => $rowstart,
    ':limit' => $limit,
];

$res = dbquery($sql . " LIMIT :rowstart, :limit", $param);

if (dbrows($res)) {

    if ($total > $limit) {
        $c_arr['nav'] = makepagenav($rowstart, $limit, $total, 3, BASEDIR . 'notifications.php?', 'rowstart');

    }

    while ($rows = dbarray($res)) {
        $c_arr['items'][] = '<div class="item">
            <div class="d-flex">
                <div>
                    <h5>
                    <a data-id="' . $rows['notify_id'] . '" data-bs-toggle="offcanvas" href="#offCanvas" class="notify-title text-dark">
                    <span class="me-2 icon-n">' . get_image($rows['notify_read'] == 0 ? 'mail-unread' : 'mail-read') . '</span>
                    ' . $rows['notify_subject'] . '
                    </a>
                    </h5>
                </div>
                <span class="ms-auto fs-6">' . showdate('shortdate', $rows['notify_datestamp']) . '</span>
            </div>
            <div><span class="badge bg-primary-soft">' . get_notification_type($rows['notify_type']) . '</span></div>
            <p>' . fusion_first_words($rows['notify_message'], '30') . '</p>
            <span class="d-none" style="display:none;">' . $rows['notify_message'] . '<div class="mt-2 small text-lighter">' . showdate('forumdate', $rows['notify_datestamp']) . '</div></span>
        </div>';
    }
} else {
    // Demo
    $c_arr['items'][] = '<div class="item text-center">There are no new notifications.</div>';
}

$random_str = random_string(6);
$token = Token::generate_token($random_str);
// Click load notifications
add_to_jquery("
$('a[data-bs-toggle=\"offcanvas\"]').on('click', function(e) {
    $(this).find('.icon-n').html('" . get_image('mail-read') . "');    
    $('.offcanvas-title').text( $(this).text() );
    $('.offcanvas-body').html( $(this).closest('.item').find('.d-none').html() );
    // update db to set read
    $.post('" . INCLUDES . "api/?api=notify-read', {id: $(this).data('id'), fusion_token: '" . $token . "', form_id:'" . $random_str . "' }, function(e) {});
});
");


opentable($title);
?>
    <div class="notifications">
        <?php
        echo implode('', $c_arr['items']);
        if (!empty($c_arr['nav'])) :
            ?>
            <hr class="mt-2">
            <div class="nav d-flex align-items-center justify-content-center">
                <?php
                echo $c_arr['nav'] ?>
            </div>
        <?php
        endif;
        ?>
    </div>
<?php
closetable();

?>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offCanvas" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Offcanvas</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body"></div>
    </div>
<?php


require_once FUSION_FOOTER;