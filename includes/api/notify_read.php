<?php
header('Content-Type: application/json');

function notify_read() {
    if (iMEMBER) {
        if (check_post('id', FILTER_VALIDATE_INT)) {
            $id = post('id', FILTER_VALIDATE_INT);
            $user_id = fusion_get_userdata('user_id');
            dbquery("UPDATE " . DB_USER_NOTIFICATIONS . " SET notify_read=1 WHERE notify_id=:id AND notify_user=:uid", [
                ':id' => $id,
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
fusion_add_hook('fusion_filters', 'notify_read');