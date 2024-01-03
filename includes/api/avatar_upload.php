<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: username_validation.php
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

use Defender\ImageValidation;

defined( 'IN_FUSION' ) || exit;

if ( !empty( $_FILES ) ) {

    function upload_avatar() {
        $preview = $config = $errors = [];
        $input = 'user_avatar'; // the input name for the fileinput plugin
        $site_path = rtrim( fusion_get_settings( 'siteurl' ), '/' ) . '/';
        if ( empty( $_FILES[$input] ) ) {
            return [];
        }
        $total = count( $_FILES[$input]['name'] ); // multiple files
        $path = IMAGES . '/avatars/'; // your upload path

        $userdata = fusion_get_userdata();

        if ( !empty( $userdata['user_avatar'] ) && file_exists( $path . $userdata['user_avatar'] ) ) {
            @unlink( $path . $userdata['user_avatar'] );
        }

        if ( !file_exists( $site_path . 'images/avatars/' ) ) {
            mkdir( $site_path . 'images/avatars/', 0755 );
        }


        $tmpFilePath = $_FILES[$input]['tmp_name'][0]; // the temp file path
        $target_name = $_FILES[$input]['name'][0]; // the file name
        $image_size = $_FILES[$input]['size'][0]; // the file size

        //Make sure we have a file path
        if ( $tmpFilePath != "" ) {
            //Setup our new file path
            $newFilePath = $path . $target_name;
            $newFileUrl = $site_path . 'images/avatars/' . $target_name;

            $filter_config = get_field_config( 'user_avatar' );
            $valid_ext = explode(',', $filter_config['valid_ext']);
            $max_size = $filter_config['max_byte'];
            $target_width = $filter_config['max_width'];
            $target_height = $filter_config['max_height'];

            // Filter image name from its file extension.
            if ( $target_name != "" && !preg_match( "/[^a-zA-Z0-9_-]/", $target_name ) ) {
                $image_name = $target_name;
            } else {
                $image_name = stripfilename( substr( $target_name, 0, strrpos( $target_name, "." ) ) );
            }

            $image_ext = strtolower( strrchr( $target_name, "." ) );

            $image_res = [];
            if ( filesize( $tmpFilePath ) > 10 && @getimagesize( $tmpFilePath ) ) {
                $image_res = @getimagesize( $tmpFilePath );
            }

            switch ( $image_ext ) {
                case '.gif':
                    $filetype = 1;
                    break;
                case '.jpg':
                    $filetype = 2;
                    break;
                case '.png':
                    $filetype = 3;
                    break;
                case '.webp':
                    $filetype = 4;
                    break;
                default:
                    $filetype = NULL;
            }

            $error_code = 0;

            if ( $tmpFilePath > $max_size ) {
                // Invalid file size
                $error_code = 1;

            } else if ( !$filetype || !verify_image( $tmpFilePath ) ) {
                // Unsupported image type
                $error_code = 2;

            } else if ( $image_res[0] > $target_width || $image_res[1] > $target_height ) {
                // Invalid image resolution
                $error_code = 3;

            } else if ( !in_array( $image_ext, $valid_ext ) ) {

                $error_code = 4;

            } else if ( fusion_get_settings( 'mime_check' ) && ImageValidation::mimeCheck( $tmpFilePath, $image_ext, ['.jpg', '.jpeg', '.png', '.png', '.svg', '.gif', '.bmp', '.webp'] ) === FALSE ) {

                $error_code = 5;

            }

            if ( !move_uploaded_file( $tmpFilePath, $newFilePath ) ) {

                $error_code = 6;
            }

        } else {
            $error_code = 7;
        }

        if ( isset( $image_name ) && isset( $newFileUrl ) && isset( $image_size ) && isset( $image_ext ) ) {

            $fileId = $image_name . '0'; // some unique key to identify the file

            $preview[] = $newFileUrl;

            $config[] = [
                'key'         => $fileId,
                'caption'     => $image_name . $image_ext,
                'size'        => $image_size,
                'downloadUrl' => $newFileUrl, // the url to download the file
            ];

            dbquery( "UPDATE " . DB_USERS . " SET user_avatar=:filename WHERE user_id=:uid", [
                ':uid'      => $userdata['user_id'],
                ':filename' => $image_name . $image_ext,
            ] );

            $out = ['initialPreview' => $preview, 'initialPreviewAsData' => TRUE, 'imageSize' => $image_size, 'imageName' => $image_name . $image_ext];

        } else if ( isset( $image_name ) && isset( $error_code ) ) {

            $img = 'file "' . $image_name . '"';

            $out['error'] = 'Oh snap! We could not upload the ' . $img . ' now. Please try again later. (Error ' . $error_code . ')';
        }

        return $out ?? [];
    }

    header( 'Content-Type: application/json' ); // set json response headers
    $outData = upload_avatar(); // a function to upload the bootstrap-fileinput files
    echo json_encode( $outData ); // return json data
    exit(); // terminate

}
