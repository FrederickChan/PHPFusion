<?php

use PHPFusion\Errors;

/**
 * @return array
 * response code:
 * 400 - access error
 * 300 - token error
 * 100 - data error
 * 200 - no error
 */
function errorlogs_action() {
    $response = ['fusion_error_id' => 0, 'from' => 0, 'to'=>0, 'status' => 100];
    if (iADMIN && checkrights('ERRO')) {
        if (fusion_safe()) {
            $error_id = post('id', FILTER_VALIDATE_INT);
            $action = post('action', FILTER_VALIDATE_INT);
            if ($error_id) {
                $res = dbquery("SELECT * FROM " . DB_ERRORS . " WHERE error_id=:id", [':id' => $error_id]);
                if (dbrows($res)) {
                    $data = dbarray($res);
                    switch ($action) {
                        case 4:  // Delete action
                            $result = dbquery("DELETE FROM " . DB_ERRORS . " WHERE error_id=:id", [
                                ':id' => $error_id,
                            ]);
                            if ($result) {
                                return [
                                    'error_id' => $error_id,
                                    'from' => Errors::getInstance()->getErrorStatus($data['error_status']),
                                    'to' => 999,
                                    'status' => 999,
                                ];
                            }
                            break;
                        case '2': //ignored
                        case '1': //solved
                        case '0': // new
                            // Update Error Status
                            $result = dbquery("UPDATE " . DB_ERRORS . " SET error_status=:status WHERE error_id=:id", [
                                ':id' => (int)$error_id,
                                ':status' => (int)$action,
                            ]);
                            if ($result) {
                                return [
                                    'error_id' => $error_id,
                                    'from' => Errors::getInstance()->getErrorStatus($data['error_status']),
                                    'to' => Errors::getInstance()->getErrorStatus($action),
                                    'status' => 200,
                                ];
                            }
                            break;
                        default:
                    }
                }
            }
        } else {
            $response['status'] = 300;
        }
    } else {
        $response['status'] = 400;
    }

    return $response;
}


header('Content-Type: application/json'); // set json response headers
echo json_encode(errorlogs_action()); // return json data
exit(); // terminate
