<?php

use PHPFusion\Notifications;

function notification_menu() {

    $user_id = fusion_get_userdata('user_id');

    $notice_class = Notifications::getInstance();

    $res = dbquery("SELECT * FROM " . DB_USER_NOTIFICATIONS . " WHERE notify_user=:uid AND notify_read=0 ORDER BY notify_datestamp DESC LIMIT 0,15", [
        ':uid' => $user_id,
    ]);

    $count = dbcount("(notify_id)", DB_USER_NOTIFICATIONS, "notify_user=:uid AND notify_read=0", [':uid' => $user_id]);
    //' . ($rows['icon'] ?? get_image('notification', '')) . '

    if (dbrows($res)) {
        while ($rows = dbarray($res)) {
            $c_arr['items'][] = '
            <li><a class="dropdown-item nav-notification-list p-2" href="' . BASEDIR . 'notifications.php?type=' . $rows['notify_type'] . '">
            <div class="d-flex gap-2">
               <span class="ps-2">' . ($notice_class->selectIcons($rows['notify_type']) ?? get_image('notification', '')) . '</span>
                <div class="d-block">
                    <h6 class="mb-0">' . $rows['notify_subject'] . '</h6>
                    <div class="text-nowrap fs-5">' . fusion_first_words($rows['notify_message'], 6) . '</div>            
                    <div class="fs-6">' . showdate('forumdate', $rows['notify_datestamp']) . '</div>
                </div>            
            </div>
            </a></li>';
        }

        $c_arr['items'][] = '
        <li class="p-2"><div class="pt-2 pb-2 d-flex align-items-center justify-content-center w-100">
        <a href="#" class="btn ps-5 pe-5 btn-primary-soft" data-trigger="notify-readall"><span class="me-2">' . get_image('brush') . '</span>Mark all as read</a></div>
        </li>
        ';

    } else {
        $c_arr['items'][] = '
        <li class="p-2 justify-content-center"><div class="pt-2 pb-2">No new notifications</div></li>
        ';
    }
    /*
     *  '<li>
                <div class="list-group-item list-group-item-action unread rounded d-flex border-0 mb-1 p-3">
                <div class="avatar -text-center d-none d-sm-inline-block">' . display_avatar($userdata, '48px') . '</div>
                <div class="ms-sm-3">
                    <div class="d-flex">
                        <p class="small mb-2"><strong>Judy Ngyuen</strong> sent you a friend request.</p><p class="small ms-3 text-nowrap">Just now</p>
                    </div>
                    <div class="d-flex">
                        <button class="btn btn-sm py-1 btn-primary me-2">Accept</button>
                        <button class="btn btn-sm py-1 btn-danger-soft">Delete </button>
                    </div>
                </div>
            </div>
        </li>' .
    '<li>
            <a href="#" class="list-group-item list-group-item-action unread rounded d-flex border-0 mb-1 p-3">
              <div class="avatar text-center d-none d-sm-inline-block">
                ' . display_avatar($userdata, '48px', 'rounded-circle', FALSE, 'rounded-circle') . '
              </div>
              <div class="ms-sm-3">
                <div class="d-flex">
                  <p class="small mb-2">Webestica has 15 like and 1 new activity</p>
                  <p class="small ms-3">1hr</p>
                </div>
              </div>
            </a>
        </li>' .
                '<li>
            <a href="#" class="list-group-item rounded d-flex border-0 p-3 mb-1">
        ' . display_avatar($userdata, '48px', 'rounded-circle', FALSE, 'rounded-circle overflow-hide') . '
              <div class="ms-sm-3 d-flex">
                <p class="small mb-2"><b>Bootstrap in the news:</b> The search giant’s parent company, Alphabet, just joined an exclusive club of tech stocks.</p>
                <p class="small ms-3">4hr</p>
              </div>
            </a>
          </li>
     */
    $form_id = random_string();
    $token = fusion_get_token($form_id);
    add_to_jquery("
    $('a[data-trigger=\"notify-readall\"]').on('click', function(e) {
        console.log('clicked');
        $.post('" . INCLUDES . "api/?api=notify-readall', {type:'all', fusion_token:'$token', form_id:'$form_id'}, function(e) {
            console.log(e);
        });    
    });    
    ");

    if ($count) {
        return [
            'n1' => [
                'link_id' => 'n1',
                'link_item_class' => 'p0',
                // Add new method to super-menu rendering
                'link_content' => '<div class="card" style="min-width:350px;">' .
                    '<div class="card-header pt-3 pb-2">' .
                    '<div class="d-flex flex-row">
                    <h6><strong>' . number_format($count) . '</strong> <span class="small">notifications</span></h6>
                    <span class="ms-auto fs-5"><a href="' . BASEDIR . 'notifications.php">View more ' . get_image('right') . '</a></span></div>' .
                    '</div><div class="card-body p-0">' .
                    '<ul class="list-group list-group-flush list-unstyled" style="max-height:350px;overflow-y:auto;">' .
                    implode('', $c_arr['items']) .
                    '</ul>' .
                    '</div></div>',
            ],
        ];
    }
}
