<?php

function delete_avatar() {
    if (check_post('user_hash') && check_post('user_id')) {
        $userdata = fusion_get_user(post('user_id', FILTER_VALIDATE_INT));

        if ($userdata['user_id'] == fusion_get_userdata('user_id') || (iADMIN && checkrights('M') && post('user_hash') == $userdata['user_hash'])) {

            if (file_exists(IMAGES.'avatars/'.$userdata['user_avatar'])) {
                unlink(IMAGES.'avatars/'.$userdata['user_avatar']);
                dbquery("UPDATE ".DB_USERS." SET user_avatar='' WHERE user_id=:uid", [':uid'=>$userdata['user_id']]);
            }
            return [
                'response' => 200,
            ];
        }
    }
    return [
        'response' => 300,
    ];
}

header( 'Content-Type: application/json' ); // set json response headers
echo json_encode( delete_avatar() ); // return json data
exit(); // terminate
