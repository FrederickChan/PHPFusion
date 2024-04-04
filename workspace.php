<?php
require_once __DIR__.'/maincore.php';

require_once FUSION_HEADER;

$user_id = '116584795707196984254';

if (dbcount("(user_id)", DB_USERS, "user_google=:googleId", ['googleId' => $user_id])) {

    $auth = new \PHPFusion\Authenticate();

    // Login
    $auth->validate($user_id, 'user_google', TRUE);

    if ($userdata = $auth->getUserData()) {


        if ($auth->authRedirection()) {
            $response_code = 300;
        }

    }
}




require_once FUSION_FOOTER;