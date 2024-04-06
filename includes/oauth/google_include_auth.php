<?php
require_once __DIR__ . '/../../maincore.php';
require_once INCLUDES . 'vendor/autoload.php';

$client_id = '110588727681-6akp7fbrfl8idm27h0gradg38d6ee7ds.apps.googleusercontent.com';

$response_code = 400;

$client = new Google_Client(['client_id' => $client_id]);  // Specify the CLIENT_ID of the app that accesses the backend

$payload = $client->verifyIdToken(post('credential'));

if ($payload) {

    $user_id = $payload['sub'];
    $user_email = $payload['email'];
    $user_firstname = $payload['given_name'];
    $user_lastname = $payload['family_name'];
    $user_avatar = $payload['picture'];

    $auth = new Authenticate();

    \PHPFusion\Authenticate::logOut();

    if (dbcount("(user_id)", DB_USERS, "user_google=:googleId", ['googleId' => $user_id])) {
        // Login
        $auth->validate($user_id, 'user_google', TRUE);

        if ($userdata = $auth->getUserData()) {
            $response_code = 200;

            if ($auth->authRedirection()) {
                $response_code = 300;
            }
        }

    } else if (dbcount("(user_id)", DB_USERS, "user_email=:googleEmail", [':googleEmail' => $user_email])) {

        $auth->validate($user_email, 'user_email', TRUE);

        if ($userdata = $auth->getUserData()) {

            $response_code = 200;

            $cond[] = "user_google=:googleId";
            $arr[':googleId'] = $user_id;

            if (!$userdata['user_firstname']) {
                $cond[] = "user_firstname=:name01";
                $arr[':name01'] = stripinput($user_firstname);
            }

            if (!$userdata['user_lastname']) {
                $cond[] = "user_lastname=:name02";
                $arr[':name02'] = stripinput($user_lastname);
            }

            if (!$userdata['user_avatar']) {
                if (!empty($user_avatar)) {
                    $file_url = @file_get_contents($user_avatar);
                    $filename = stripinput(basename($file_url));
                    if ($filename != "") {
                        if (@copy($user_avatar, IMAGES_U . $filename)) {
                            $cond[] = "user_avatar=:picture";
                            $arr[':picture'] = $filename;
                        }
                    }
                }
            }

            $param = array_merge($arr, [':uid' => $userdata['user_id']]);
            $cond = implode(', ', $cond);

            dbquery("UPDATE " . DB_USERS . " SET $cond WHERE user_id=:uid", $param);

            if ($auth->authRedirection()) {
                $response_code = 300;
            }
        }

    } else {

        // Create user account and then depending on system to login or not
        /*
         *     [iss] =&gt; https://accounts.google.com
            [azp] =&gt; 110588727681-6akp7fbrfl8idm27h0gradg38d6ee7ds.apps.googleusercontent.com
            [aud] =&gt; 110588727681-6akp7fbrfl8idm27h0gradg38d6ee7ds.apps.googleusercontent.com
            [sub] =&gt; 116584795707196984254
            [email] =&gt; meangczac.chan@gmail.com
            [email_verified] =&gt; true
            [nbf] =&gt; 1712124730
            [name] =&gt; meang czac
            [picture] =&gt; https://lh3.googleusercontent.com/a/ACg8ocIP9EzOFQ688A4-JCHXHNHI37xdJ_MSuopVigYmsApX=s96-c
            [given_name] =&gt; meang
            [family_name] =&gt; czac
            [iat] =&gt; 1712125030
            [exp] =&gt; 1712128630
            [jti] =&gt; 65a12ee2a1b786827d7d7845ddfcaf73c035847d
        )
         */

        $response_code = 200;
    }

}

echo json_encode(['response' => $response_code]);
