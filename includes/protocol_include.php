<?php

/**
 * Redirect to internal or external URL.
 *
 * @param string $location Desintation URL.
 * @param bool $delay meta refresh delay.
 * @param bool $script True if you want to redirect via javascript.
 * @param int $code HTTP status code to send.
 */
function redirect($location, $delay = FALSE, $script = FALSE, $code = 200) {
    if (!defined('STOP_REDIRECT')) {
        if (isnum($delay)) {
            $ref = "<meta http-equiv='refresh' content='$delay; url=" . $location . "' />";
            add_to_head($ref);
        } else {
            if ($script == FALSE && !headers_sent()) {
                set_status_header($code);
                header("Location: " . str_replace("&amp;", "&", $location));
            } else {
                echo "<script type='text/javascript'>document.location.href='" . str_replace("&amp;", "&", $location) . "'</script>\n";
            }
            exit;
        }
    }
}

/**
 * Generate a clean Request URI.
 *
 * @param mixed $request_addition 'page=1&ref=2' or array('page' => 1, 'ref' => 2)
 * @param array $filter_array array('aid','page', ref')
 * @param bool $keep_filtered True to keep filter, false to remove filter from FUSION_REQUEST.
 *                                If remove is true, to remove everything and keep $requests_array and $request
 *                                addition. If remove is false, to keep everything else except $requests_array
 *
 * @return string
 */
function clean_request($request_addition = '', $filter_array = [], $keep_filtered = TRUE) {

    $fusion_query = [];

    if (fusion_get_settings("site_seo") && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
        global $filepath;

        $url['path'] = $filepath;
        if (!empty($_GET)) {
            $fusion_query = $_GET;
        }
    } else {

        $url = ((array)parse_url(htmlspecialchars_decode($_SERVER['REQUEST_URI']))) + ['path' => '', 'query' => ''];

        if ($url['query']) {
            parse_str($url['query'], $fusion_query); // this is original.
        }
    }

    if ($keep_filtered) {
        $fusion_query = array_intersect_key($fusion_query, array_flip($filter_array));
    } else {
        $fusion_query = array_diff_key($fusion_query, array_flip($filter_array));
    }

    if ($request_addition) {

        $request_addition_array = [];

        if (is_array($request_addition)) {
            $fusion_query = $fusion_query + $request_addition;
        } else {
            parse_str($request_addition, $request_addition_array);
            $fusion_query = $fusion_query + $request_addition_array;
        }
    }

    $prefix = $fusion_query ? '?' : '';

    return $url['path'] . $prefix . http_build_query($fusion_query, 'flags_', '&amp;');
}


/**
 * Set HTTP status header.
 *
 * @param int $code Status header code.
 *
 * @return bool Whether header was sent.
 */
function set_status_header($code = 200) {

    if (headers_sent()) {
        return FALSE;
    }

    $protocol = $_SERVER['SERVER_PROTOCOL'];

    if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
        $protocol = 'HTTP/1.0';
    }

    $desc = [
        100 => 'Continue', 101 => 'Switching Protocols', 102 => 'Processing', 200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 207 => 'Multi-Status', 226 => 'IM Used', 300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 306 => 'Reserved', 307 => 'Temporary Redirect', 400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Timeout', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Long', 415 => 'Unsupported Media Type', 416 => 'Requested Range Not Satisfiable', 417 => 'Expectation Failed', 422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 426 => 'Upgrade Required', 500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported', 506 => 'Variant Also Negotiates', 507 => 'Insufficient Storage', 510 => 'Not Extended',
    ];

    $desc = !empty($desc[$code]) ? $desc[$code] : '';

    header("$protocol $code $desc");

    return TRUE;
}

/**
 * Get HTTP response code.
 *
 * @param string $url URL.
 *
 * @return false|string
 */
function get_http_response_code($url) {

    if (function_exists('curl_init')) {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($handle);
        $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return $http_code;
    } else {
        stream_context_set_default(['ssl' => ['verify_peer' => FALSE, 'verify_peer_name' => FALSE],]);

        $headers = @get_headers($url);

        return substr($headers[0], 9, 3);
    }
}

/**
 * Get current URL.
 *
 * @return string
 */
function get_current_url() {

    $s = (empty($_SERVER["HTTPS"]) ? "" : ($_SERVER["HTTPS"] == "on")) ? "s" : "";
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80" || ($_SERVER['SERVER_PORT'] == "443" && $s == "s")) ? "" : (":" . $_SERVER["SERVER_PORT"]);

    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . (str_replace(basename(cleanurl($_SERVER['PHP_SELF'])), "", $_SERVER['REQUEST_URI']));
}


/**
 * Send a cookie.
 *
 * @param string $name The name of the cookie.
 * @param string $value The value of the cookie.
 * @param int $expires The time the cookie expires.
 * @param string $path The path on the server in which the cookie will be available on.
 * @param string $domain The (sub)domain that the cookie is available to.
 * @param bool $secure Whether the client should send back the cookie only over HTTPS or null to auto-enable this when the request is already using
 *                              HTTPS.
 * @param bool $httponly Whether the cookie will be made accessible only through the HTTP protocol.
 * @param string|null $samesite Whether the cookie will be available for cross-site requests. Possible value: none | lax | strict
 */
function fusion_set_cookie($name, $value, $expires, $path, $domain, $secure = FALSE, $httponly = FALSE, $samesite = NULL) {

    $samesite = in_array($samesite, ['lax', 'none', 'strict', NULL]) ? $samesite : NULL;

    if (PHP_VERSION_ID < 70300) {
        if (!headers_sent()) {
            if ($value !== '') {
                $expires = $expires !== 0 ? ' expires=' . $expires . ';' : '';
                $domain = $domain ? 'domain=' . $domain . ';' : '';
                $secure = $secure ? 'secure;' : '';
                $httponly = $httponly ? 'httponly;' : '';
                $samesite = $samesite !== NULL ? 'samesite=' . $samesite : '';

                header("Set-Cookie: $name=$value; $expires path=$path; $domain $secure $httponly $samesite");
            } else {
                setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
            }
        } else {
            setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
        }
    } else {
        setcookie($name, $value, ['expires' => $expires, 'path' => $path, 'domain' => $domain, 'secure' => $secure, 'httponly' => $httponly, 'samesite' => $samesite]);
    }
}


/**
 * Recursively remove folder and all files/subdirectories.
 *
 * @param string $dir Path to the folder.
 */
function rrmdir($dir) {

    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (filetype($dir . '/' . $object) == 'dir') {
                    rrmdir($dir . '/' . $object);
                } else {
                    unlink($dir . '/' . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/**
 * Checks whether a string is JSON or not.
 *
 * @param string $string The string to be checked.
 *
 * @return bool
 */
function is_json($string) {

    json_decode($string);

    return (json_last_error() == JSON_ERROR_NONE);
}
