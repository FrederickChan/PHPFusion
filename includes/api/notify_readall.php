<?php
header('Content-Type: application/json');

function notify_readall() {
    if (iMEMBER && fusion_safe()) {

        if (post('type') == 'all') {

            $user_id = fusion_get_userdata('user_id');

            dbquery("UPDATE " . DB_USER_NOTIFICATIONS . " SET notify_read=1 WHERE notify_user=:uid", [
                ':uid' => $user_id,
            ]);

            echo json_encode([
                'response' => 200,
                'title' => 'Updated',
                'text' => 'Actions have been performed successfully.',
            ]);
        } else {
            echo json_encode([
                'response' => 302,
                'title' => 'Illegal access detected.',
                'text' => 'Actions could not be performed due to illegal access.']);
        }
    } else {
        echo json_encode([
            'response' => 302,
            'title' => 'Illegal access detected.',
            'text' => 'Actions could not be performed due to illegal access.']);
    }
}


/**
 * @uses notify_read()
 */
fusion_add_hook('fusion_filters', 'notify_readall');