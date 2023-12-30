<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: email_code.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Authenticate;
use PHPFusion\EmailAuth;

defined( 'IN_FUSION' ) || exit;

if ( iMEMBER ) {

    if ( check_post( 'user_id' ) && check_post( 'user_hash' ) ) {

        $user_id = post( 'user_id', FILTER_VALIDATE_INT );

        $user_hash = post( 'user_hash' );

        if ( $user_id && $user_hash ) {

            $userdata = fusion_get_userdata();

            if ( $userdata['user_id'] == $user_id && $userdata['user_password'] == $user_hash ) {

                $email = new EmailAuth();

                $email->setEmail( $userdata['user_email'] );

                $email->sendCode();

                echo json_encode( $email->getResponse() );
            } else {
                echo json_encode( ['response' => 301,
                                   'title'    => 'Illegal access detected.',
                                   'text'     => 'Actions could not be performed due to illegal access.'] );
            }

        } else {

            echo json_encode( ['response' => 301,
                               'title'    => 'Illegal access detected.',
                               'text'     => 'Actions could not be performed due to illegal access.'] );
        }

    } else {

        echo json_encode( ['response' => 302,
                           'title'    => 'Illegal access detected.',
                           'text'     => 'Actions could not be performed due to illegal access.'] );
    }

} else {

    echo json_encode( ['response' => 302,
                       'title'    => 'Illegal access detected.',
                       'text'     => 'Actions could not be performed due to illegal access.'] );
}


