<?php

use Defender\Token;
use PHPFusion\Notifications;
use PHPFusion\Panels;

require_once __DIR__ . '/maincore.php';

require_once FUSION_HEADER;

$notice_class = Notifications::getInstance();

$title = 'All Notifications';

if ($c_arr['notification_types'] = $notice_class->getTypes()) {

    $table_break = fusion_get_function('tablebreak');
    $table_close = fusion_get_function('closeside');


    $output = fusion_get_function('openside', 'Notifications');
    $output .= '<ul class="nav flex-column fw-bold gap-2 border-0" role="tablist">';
    $output .= ' <li class="nav-item" role="presentation">
                <a class="nav-link d-flex mb-0 active" href="' . BASEDIR . 'notifications.php" aria-selected="false" role="tab">
                <span class="me-2">' . get_image('notification', '') . '</span>All Notifications</a></li>';
    foreach ($c_arr['notification_types'] as $key => $val) {
        $count = dbcount("('notify_id')", DB_USER_NOTIFICATIONS, '');
        $output .= '<li class="nav-item" role="presentation">
        <a class="nav-link d-flex mb-0'.(get('type') == $key ? ' active' : '').'" href="' . BASEDIR . 'notifications.php?type=' . $key . '" aria-selected="false" role="tab">
        <span class="me-2">' . ($notice_class->selectIcons($key) ?? get_image('notification', '')) . '</span>' . $val. '</a></li>';

        if (get('type') == $key) {
            $title = $val;
        }
    }

    $output .= '</ul>';
    $output .= $table_close;

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
    $i = 1;
    while ($rows = dbarray($res)) {
        $c_arr['items'][] = '<div class="item">
            <div class="d-flex">
                <div class="mb-2">              
                    <a data-id="' . $rows['notify_id'] . '" data-bs-toggle="offcanvas" href="#offCanvas" class="h5 card-title notify-title">
                    <span class="me-2 icon-n">' . get_image($rows['notify_read'] == 0 ? 'mail-unread' : 'mail-read') . '</span>
                    <strong>' . $rows['notify_subject'] . '</strong>
                    </a>
                </div>
                <span class="ms-auto fs-6">' . showdate('shortdate', $rows['notify_datestamp']) . '</span>
            </div>
            <div class="mb-2"><span class="badge bg-primary-soft">' . $notice_class->selectTypes($rows['notify_type']) . '</span></div>
            <p class="text-smaller">' . fusion_first_words($rows['notify_message'], '30') . '</p>
            <span class="d-none" style="display:none;">' . $rows['notify_message'] . '<div class="mt-2 small text-lighter">' . showdate('forumdate', $rows['notify_datestamp']) . '</div></span>
        </div>
        ' . ($i == count($rows) ? '' : '<hr>') . '
        ';
        $i++;
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