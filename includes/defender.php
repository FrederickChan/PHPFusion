<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: defender.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use Defender\Validation;

/**
 * Class Defender
 */
class Defender {
    public static $input_errors = [];
    private static $debug = FALSE;
    private static $defender_instance = NULL;

    private static $input_name = '';
    private static $input_error_text = [];
    private static $page_hash = '';
    public $ref = [];

    // Declared by Form Sanitizer
    public $field = [];
    public $field_name = '';
    public $field_value = '';
    public $field_default = '';
    public $field_config = [
        'type'        => '',
        'value'       => '',
        //'default' => '',
        'name'        => '',
        //'id' => '',
        'safemode'    => '',
        'path'        => '',
        'thumbnail_1' => '',
        'thumbnail_2' => '',
    ];

    /**
     * Generates and return class instance
     * Eliminates global usage in functions
     *
     * @return null|static
     */
    public static function getInstance() {
        if ( self::$defender_instance === NULL ) {
            self::$defender_instance = new static();
        }

        return self::$defender_instance;
    }

    /**
     * Serialize an array securely
     *
     * @param array $array
     *
     * @return string
     */
    public static function serialize( array $array = [] ) {
        $return_default = '';
        if ( is_array( $array ) ) {
            return base64_encode( serialize( $array ) );
        }

        return $return_default;
    }

    /**
     * @param string|array $value
     *
     * @return string
     */
    public static function encode( $value ) {
        return base64_encode( json_encode( $value ) );
    }

    /**
     * @param string $value
     *
     * @return mixed
     */
    public static function decode( $value ) {
        return json_decode( base64_decode( $value ), TRUE );
    }

    /**
     * Read serialized array
     *
     * @param $string string serialized string
     *
     * @return array|mixed
     */
    public static function unserialize( $string ) {

        $return_default = [];
        if ( !empty( $string ) ) {
            $array = unserialize( base64_decode( $string ) );
            if ( !empty( $array ) ) {
                return $array;
            }
        }

        return $return_default;
    }

    /**
     * @param array $array
     */
    public static function add_field_session( array $array ) {
        $_SESSION['form_fields'][self::pageHash()][$array['input_name']] = $array;
    }

    /**
     * Hash a token to prevent unauthorized access
     *
     * @return string
     */
    public static function pageHash() {
        if ( !defined( 'SECRET_KEY' ) ) {
            $chars = ['abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ', '123456789'];
            $count = [( strlen( $chars[0] ) - 1 ), ( strlen( $chars[1] ) - 1 )];
            $key = '';
            for ( $i = 0; $i < 32; $i++ ) {
                $type = mt_rand( 0, 1 );
                $key .= substr( $chars[$type], mt_rand( 0, $count[$type] ), 1 );
            }

            define( 'SECRET_KEY', $key );
        }

        if ( empty( self::$page_hash ) ) {
            self::$page_hash = md5( SECRET_KEY );
        }

        return self::$page_hash;
    }

    public static function unset_field_session() {
        session_remove( 'form_fields' );
    }

    /**
     * @param array $array
     *
     * @return array
     */
    static function sanitize_array( $array ) {
        foreach ( $array as $name => $value ) {
            $array[stripinput( $name )] = trim( stripinput( $value ) );
        }

        return (array)$array;
    }

    /**
     * ID for Session
     * No $userName because it can be changed and tampered via Edit Profile.
     * Using IP address extends for guest
     *
     * @return mixed
     */
    public static function set_sessionUserID() {
        $userdata = fusion_get_userdata();
        return !empty( $userdata['user_id'] ) && !isset( $_POST['login'] ) ? (int)fusion_get_userdata( 'user_id' ) : str_replace( '.', '-', USER_IP );
    }

    /**
     * Checks whether an input was marked as invalid
     *
     * @return array
     */
    public static function getInputErrors() {
        return self::$input_errors;
    }

    /**
     * Set and override default field error text
     *
     * @param string $input_name
     * @param string $text
     */
    public static function setErrorText( $input_name, $text ) {
        self::$input_error_text[$input_name] = $text;
    }

    /**
     * Fetches the latest error text of this input
     * Important! Ensure your applications do not refresh screen for this error to show.
     * Usage fusion_safe(); for conditional redirect.
     *
     * @param string $input_name
     *
     * @return null
     */
    public static function getErrorText( $input_name ) {
        if ( self::inputHasError( $input_name ) ) {
            return self::$input_error_text[$input_name] ?? NULL;
        }

        return NULL;
    }

    /**
     * @param string $input_name
     *
     * @return bool
     */
    public static function inputHasError( $input_name ) {
        if ( isset( self::$input_errors[$input_name] ) ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return array
     */
    public static function get_inputError() {
        return self::$input_errors;
    }

    /**
     * Generate a key
     *
     * @param string $private_key
     *
     * @return string
     */
    public static function get_encrypt_key( $private_key ) {
        return openssl_random_pseudo_bytes( 32, $private_key ); // 256 bits
    }

    /**
     * Encrypts a string securely with a private key
     *
     * @param string $string      The text to encrypt
     * @param string $private_key For better security use \Defender::get_encrypt_key to generate your private key
     *
     * Does not support array encrypt.
     *
     * @return string
     */
    public static function encrypt_string( $string, $private_key = 'phpfusion' ) {
        $ivlen = openssl_cipher_iv_length( $cipher = 'AES-128-CBC' );
        $iv = openssl_random_pseudo_bytes( 16, $ivlen ); // 128 bits
        $string = self::pkcs7_pad( $string, 16 );
        $ciphertext_raw = openssl_encrypt( $string, $cipher, $private_key, OPENSSL_RAW_DATA, $iv );
        $hmac = hash_hmac( 'sha256', $ciphertext_raw, $private_key, TRUE );

        return base64_encode( $iv . $hmac . $ciphertext_raw );
    }

    /**
     * @param string $data
     * @param int    $size
     *
     * @return string
     */
    private static function pkcs7_pad( $data, $size ) {
        $length = $size - strlen( $data ) % $size;
        return $data . str_repeat( chr( $length ), $length );
    }

    /**
     * Decrypts a string securely with a private key
     *
     * @param string $string      The string to decrypt
     * @param string $private_key For better security use \Defender::get_encrypt_key to generate your private key
     *
     * @return null|string
     */
    public static function decrypt_string( $string, $private_key = 'phpfusion' ) {
        $c = base64_decode( $string );
        $ivlen = openssl_cipher_iv_length( $cipher = 'AES-128-CBC' );
        $iv = substr( $c, 0, $ivlen );
        $hmac = substr( $c, $ivlen, $sha2len = 32 );
        $ciphertext_raw = substr( $c, $ivlen + $sha2len );
        $string = openssl_decrypt( $ciphertext_raw, $cipher, $private_key, OPENSSL_RAW_DATA, $iv );
        $string = self::pkcs7_unpad( $string );
        $calcmac = hash_hmac( 'sha256', $ciphertext_raw, $private_key, TRUE );

        if ( !function_exists( 'hash_equals' ) ) {
            function hash_equals( $str1, $str2 ) {
                if ( strlen( $str1 ) != strlen( $str2 ) ) {
                    return FALSE;
                } else {
                    $res = $str1 ^ $str2;
                    $ret = 0;
                    for ( $i = strlen( $res ) - 1; $i >= 0; $i-- ) {
                        $ret |= ord( $res[$i] );
                    }

                    return !$ret;
                }
            }
        }
        if ( hash_equals( $hmac, $calcmac ) ) {//PHP 5.6+ timing attack safe comparison
            return $string;
        }

        return NULL;
    }

    /**
     * @param string $data
     *
     * @return false|string
     */
    private static function pkcs7_unpad( $value ) {

        $pad = ord( $value[strlen( $value ) - 1] );

        if ( $pad > strlen( $value ) ) {
            return FALSE;
        }

        if ( strspn( $value, chr( $pad ), strlen( $value ) - $pad ) != $pad ) {
            return FALSE;
        }

        return substr( $value, 0, -1 * $pad );
    }

    /**
     * Return the current document field session or sessions
     * Use for debug purposes
     *
     * @param string $input_name
     *
     * @return mixed
     */
    public function get_current_field_session( $input_name = '' ) {
        if ( $input_name && isset( $_SESSION['form_fields'][self::pageHash()][$input_name] ) ) {
            //return $_SESSION['form_fields'][self::pageHash()][$input_name];
            return session_get( ['form_fields', self::pageHash(), $input_name] );
        } else {
            if ( $input_name ) {
                return FALSE;
            } else {
                //return $_SESSION['form_fields'];
                return $_SESSION['form_fields'][self::pageHash()];

                return session_get( ['form_fields', self::pageHash()] );
            }
        }
    }

    /**
     * Request whether safe to proceed at all times
     *
     * @return bool
     */
    public static function safe() {
        if ( !defined( 'FUSION_NULL' ) ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param array $array
     */
    public function addHoneypot( array $array ) {
        $_SESSION['honeypots'][self::pageHash()][$array['honeypot']] = $array;
    }

    /**
     * @param string $honeypot
     *
     * @return string
     */
    public function getHoneypot( $honeypot = '' ) {
        if ( $honeypot && isset( $_SESSION['honeypots'][self::pageHash()][$honeypot] ) ) {
            return $_SESSION['honeypots'][self::pageHash()][$honeypot];
        } else {
            if ( $honeypot ) {
                return 'This form contains no honeypots';
            } else {
                return $_SESSION['honeypots'][self::pageHash()];
            }
        }
    }

    /**
     * @param bool|FALSE $value
     */
    public function debug( $value = FALSE ) {
        self::$debug = $value;
    }

    /**
     * Sanitize with input name
     *
     * @param string $key
     * @param string $default
     * @param bool   $input_name
     * @param bool   $is_multiLang
     *
     * @return string
     */
    public function sanitizer( $key, $default = '', $input_name = FALSE, $is_multiLang = FALSE ) {

        $value = $this->filterPostArray( $key );

        return $this->formSanitizer( $value, $default, $input_name, $is_multiLang );
    }

    /**
     * @param mixed $key
     *
     * @return string
     */
    public function filterPostArray( $key ) {
        $flag = FILTER_FLAG_NONE;
        $input_key = $key;
        if ( is_array( $key ) ) {
            // always use key 0 for filter var
            $input_key = $key[0];
            $flag = FILTER_REQUIRE_ARRAY;
        }

        $filtered = post( $input_key, FILTER_DEFAULT, $flag );

        if ( is_array( $key ) ) {
            $input_ref = $key;
            unset( $input_ref[0] );

            // Get the value of the filtered post value using the $key array as map
            return array_reduce(
                $input_ref,
                function ( $value, $key ) {

                    return ( !empty( $value[$key] ) ? $value[$key] : '' );
                },
                $filtered
            );
        }

        return (string)$filtered;
    }

    /**
     * Sanitize
     *
     * @param string|array $value
     * @param string       $default
     * @param bool|FALSE   $input_name
     * @param bool|FALSE   $is_multiLang
     *
     * @return string
     */
    public function formSanitizer( $value, $default = '', $input_name = FALSE, $is_multiLang = FALSE ) {
        $val = [];
        $page_hash = self::pageHash();

        if ( $input_name ) {

            if ( $is_multiLang ) {
                $language = array_keys( fusion_get_enabled_languages() );
                foreach ( $language as $lang ) {
                    $iname = $input_name . '[' . $lang . ']';

                    if ( $this->field_config = $this->get_current_field_session( $input_name ) ) {
                        //$this->field_config = $_SESSION['form_fields'][$page_hash][$iname];
                        $this->field_name = $iname;
                        $this->field_value = $value[$lang];
                        $this->field_default = $default;
                        $val[$lang] = $this->validate();
                    }
                }
                if ( !empty( $this->field_config['required'] ) && ( !$value[LANGUAGE] ) ) {

                    fusion_stop();
                    $iname = $input_name . '[' . LANGUAGE . ']';
                    self::setInputError( $iname );

                    return $default;

                } else {
                    $val_ = [];

                    foreach ( $val as $lang => $value ) {
                        $val_[$lang] = $value;
                    }

                    return serialize( $val_ );
                }
            } else {
                // Make sure that the input was actually defined in code. AND there must be a value to worth the processing power expense!
                if ( $this->field_config = $this->get_current_field_session( $input_name ) ) {

                    $this->field_config = $_SESSION['form_fields'][$page_hash][$input_name];
                    $this->field_name = $input_name;
                    $this->field_value = $value;
                    $this->field_default = $default;
                    /*
                     * These two checks won't be neccesary after we add the options in all inputs
                     * NOTE: Please don't pass 'stripinput' as callback, before we reach a callback
                     * everything is checked and sanitized already. The callback should only check
                     * if certain conditions are met then return TRUE|FALSE and not do any alterations
                     * the value itself
                     */
                    $callback = $this->field_config['callback_check'] ?? FALSE;
                    $regex = $this->field_config['regex'] ?? FALSE;
                    $secured = $this->validate();

                    // If truly FALSE the check failed
                    if ( $secured === FALSE || ( $this->field_config['required'] == 1 && $secured == '' ) ||
                        ( $secured != '' && $regex && !preg_match( '@^' . $regex . '$@i', $secured ) ) || // regex will fail for an imploded array, maybe move this check
                        ( is_callable( $callback ) && !$callback( $secured ) )
                    ) {

                        fusion_stop();
                        self::setInputError( $input_name );

                        // Add regex error message.
                        if ( $secured != '' && $regex && !preg_match( '@^' . $regex . '$@i', $secured ) ) {
                            addnotice( 'danger', sprintf( fusion_get_locale( 'regex_error' ), $this->field_config['title'] ) );
                        }
                        // Add a notice
                        if ( self::$debug ) {
                            addnotice( 'warning', '<strong>' . $input_name . ':</strong>' . ( $this->field_config['safemode'] ? ' is in SAFEMODE and the' : '' ) . ' check failed' );
                        }

                        // Return user's input for correction
                        return $this->field_value;
                    } else {
                        if ( self::$debug ) {
                            addnotice( 'info', $input_name . ' = ' . ( is_array( $secured ) ? 'array' : $secured ) );
                        }

                        return $secured;
                    }
                } else {
                    return $default;
                }
            }
        } else {
            if ( $value ) {
                if ( !is_array( $value ) ) {
                    if ( intval( $value ) ) {
                        return stripinput( $value ); // numbers
                    } else {
                        return stripinput( trim( preg_replace( '/ +/i', ' ', $value ) ) );
                    }
                } else {
                    $secured = [];
                    foreach ( $value as $unsecured ) {
                        if ( (int)$unsecured ) {
                            $secured[] = stripinput( $unsecured ); // numbers
                        } else {
                            $secured[] = stripinput( trim( preg_replace( '/ +/i', ' ', $unsecured ) ) );
                        }
                    }

                    return implode( $this->field_config['delimiter'], $secured );
                }
            } else {
                return $default;
            }
        }

        //set_error(E_USER_NOTICE, "The form sanitizer could not handle the request! (input: $input_name)", "", "");
    }

    /**
     * @return false|string|null
     */
    public function validate() {
        Validation::inputName( $this->field_name );
        Validation::inputDefault( $this->field_default );
        Validation::inputValue( $this->field_value );
        Validation::inputConfig( $this->field_config );

        if ( defined( 'LOCALESET' ) ) {
            $locale = fusion_get_locale( LOCALE . LOCALESET . 'defender.php' );
        } else {
            $locale = fusion_get_locale( LOCALE . 'English/defender.php' );
        }

        // Are there situations were inputs could have leading
        // or trailing spaces? If not then uncomment line below
        //$this->field_value = trim($this->field_value);
        // Don't bother processing and validating empty inputs
        //if ($this->field_value == '') return $this->field_value;
        /**
         * Keep this included in the constructor
         * This solution was needed to load the defender.php.php before
         * defining LOCALESET
         */
        // declare the validation rules and assign them
        // type of fields vs type of validator

        // execute sanitisation rules at point-blank precision using switch
        try {
            if ( !empty( $this->field_config['type'] ) ) {

                if ( empty( $this->field_value ) && ( $this->field_config['type'] !== 'number' ) ) {
                    return $this->field_default;
                }

                return Validation::getValidated();

            } else {
                self::stop();
                addnotice( 'danger', sprintf( $locale['df_406'], self::$input_name ) );
            }
        } catch ( Exception $e ) {
            self::stop();
            addnotice( 'danger', $e->getMessage() );
        }

        return NULL;
    }

    /**
     * Sends a system error declaration.
     *
     * @param string $notice
     *
     * @return null
     */
    public static function stop( $notice = '' ) {
        //debug_print_backtrace();
        if ( !defined( 'FUSION_NULL' ) ) {
            define( 'FUSION_NULL', TRUE );
            if ( $notice ) {
                addnotice( 'danger', $notice );
                define( 'STOP_REDIRECT', TRUE );
            }
            //addNotice('danger', '<strong>'.fusion_get_locale('error_request', LOCALE.LOCALESET.'defender.php').'</strong>');
        }
        return NULL;
    }

    /**
     * @param string $input_name
     */
    public static function setInputError( $input_name ) {
        self::$input_errors[$input_name] = TRUE;
    }

    /**
     * @param string $key
     * @param string $default
     * @param false  $input_name
     *
     * @return array
     */
    public function fileSanitizer( $key, $default = '', $input_name = FALSE ) {
        $upload = (array)$this->formSanitizer( $_FILES[$key], $default, $input_name );
        if ( isset( $upload['error'] ) && $upload['error'] == 0 ) {
            return $upload;
        }

        return [];
    }

}

/**
 * Verify and Sanitize Inputs
 *
 * @param string $value
 * @param string $default
 * @param bool   $input_name
 * @param bool   $is_multiLang
 *
 * @return string|array
 */
function form_sanitizer( $value, $default = '', $input_name = FALSE, $is_multiLang = FALSE ) {
    return Defender::getInstance()->formSanitizer( $value, $default, $input_name, $is_multiLang );
}

/**
 * Verify and Sanitize Inputs with input_name
 * A more secured method
 *
 * @param mixed  $value input_name
 * @param string $default
 * @param bool   $input_name
 * @param bool   $is_multiLang
 *
 * @return string
 */
function sanitizer( $value, $default = '', $input_name = FALSE, $is_multiLang = FALSE ) {
    return Defender::getInstance()->sanitizer( $value, $default, $input_name, $is_multiLang );
}

/**
 * Sanitizes an array
 *
 * @param array $array
 *
 * @return array
 */
function sanitize_array( $array = [] ) {
    return Defender::sanitize_array( $array );
}

/**
 * File sanitize by input_name
 *
 * @param string $value input_name
 * @param string $default
 * @param bool   $input_name
 *
 * @return array
 */
function file_sanitizer( $value, $default = '', $input_name = FALSE ) {
    return Defender::getInstance()->fileSanitizer( $value, $default, $input_name );
}

/**
 * Isset GET
 *
 * @param $key
 *
 * @return bool
 */
function check_get( $key ) {
    if ( is_array( $key ) ) {
        return !empty( array_reduce( $key, function ( $carry, $item ) {
            return ( !empty( $carry[$item] ) ? $carry[$item] : '' );
        }, $_GET ) );
    }
    return isset( $_GET[$key] );
}

/**
 * Isset POST
 *
 * @param $key
 *
 * @return bool
 */
function check_post( $key ) {
    if ( is_array( $key ) ) {
        return !empty( array_reduce( $key, function ( $carry, $item ) {
            return ( !empty( $carry[$item] ) ? $carry[$item] : '' );
        }, $_POST ) );
    }
    return isset( $_POST[$key] );
}

/**
 * @param mixed $key
 * @param int   $type
 * @param mixed $flags
 *
 * @return mixed
 */
function get( $key = NULL, $type = FILTER_DEFAULT, $flags = FILTER_FLAG_NONE ) {

    if ( is_array( $key ) ) {
        // always use key 0 for filter var
        $input_key = $key[0];
        $flag = FILTER_REQUIRE_ARRAY;

        $filtered = get( $input_key, FILTER_DEFAULT, $flag );


        $input_ref = $key;
        unset( $input_ref[0] );

        // Get the value of the filtered post value using the $key array as map
        return array_reduce(
            $input_ref,
            function ( $value, $key ) {

                return ( !empty( $value[$key] ) ? $value[$key] : '' );
            },
            $filtered
        );


        return (string)stripinput( $filtered );
    }

    if ( $flags == FILTER_VALIDATE_INT ) {

        if ( isset( $_GET[$key] ) && isnum( $_GET[$key] ) && ( $_GET[$key] > PHP_INT_MAX ) ) {
            return (int)$_GET[$key];
        }

        return 0;
    }

    if ( filter_has_var( INPUT_GET, $key ) ) {
        $filtered_input = filter_input( INPUT_GET, $key, $type, $flags );
    } else {
        $filtered_input = isset( $_GET[$key] ) ? filter_var( $_GET[$key], $type, $flags ) : NULL;
    }

    return stripinput( $filtered_input );
}

/**
 * Sanitizes $_POST by name
 *
 * @param mixed $key input_name
 * @param int   $type
 * @param mixed $flags
 *
 * @return mixed
 */
function post( $key, $type = FILTER_DEFAULT, $flags = FILTER_FLAG_NONE ) {
    if ( is_array( $key ) ) {
        // always use key 0 for filter var
        $post_key = $key[0];
        $flag = FILTER_REQUIRE_ARRAY;
        $filtered = post( $post_key, FILTER_DEFAULT, $flag );
        $input_ref = $key;
        unset( $input_ref[0] );

        // Get the value of the filtered post value using the $key array as map
        return array_reduce(
            $input_ref,
            function ( $value, $key ) {

                return ( !empty( $value[$key] ) ? $value[$key] : '' );
            },
            $filtered
        );


        return (string)stripinput( $filtered );
    }

    if ( $flags == FILTER_VALIDATE_INT ) {

        if ( isset( $_POST[$key] ) && isnum( $_POST[$key] ) && ( $_POST[$key] > PHP_INT_MAX ) ) {

            return (int)$_POST[$key];
        }
    }

    if ( filter_has_var( INPUT_POST, $key ) ) {
        return filter_input( INPUT_POST, $key, $type, $flags );
    } else {
        return isset( $_POST[$key] ) ? filter_var( $_POST[$key], $type, $flags ) : NULL;
    }
}

/**
 * Sanitizes input
 *
 * @param mixed $key
 *
 * @return array
 */
function post_array( $key ) {
    return (array)Defender::getInstance()->filterPostArray( $key );
}

/**
 * Gets server array
 *
 * @param string $key
 * @param int    $type
 *
 * @return mixed
 */
function server( $key, $type = FILTER_DEFAULT ) {
    if ( filter_has_var( INPUT_SERVER, $key ) ) {
        return filter_input( INPUT_SERVER, $key, $type );
    } else {
        return isset( $_SERVER[$key] ) ? filter_var( $_SERVER[$key], $type ) : NULL;
    }
}

/**
 * Checks if a file is uploaded during upload post event
 *
 * @param mixed $key input name
 *
 * @return bool
 */
function file_uploaded( $key ) {
    if ( !empty( $_FILES ) ) {
        if ( is_array( $key ) ) {
            $files =& $_FILES;
            foreach ( $key as $pkey ) {
                $files =& $files[$pkey];
            }

            return is_uploaded_file( $files['tmp_name'] );
        }

        return is_uploaded_file( $_FILES[$key]['tmp_name'] );
    }

    return FALSE;
}

/**
 * @param string $key
 * @param int    $type
 *
 * @return mixed
 */
function environment( $key, $type = FILTER_DEFAULT ) {
    if ( filter_has_var( INPUT_ENV, $key ) ) {
        return filter_input( INPUT_ENV, $key, $type );
    } else {
        return isset( $_ENV[$key] ) ? filter_var( $_ENV[$key], $type ) : NULL;
    }
}

/***
 * Sets a $_COOKIE
 *
 * @param string $key
 * @param int    $type
 *
 * @return string|string[]
 */
function cookie( $key, $type = FILTER_DEFAULT ) {
    if ( filter_has_var( INPUT_COOKIE, $key ) ) {
        $filtered_input = filter_input( INPUT_COOKIE, $key, $type );
    } else {
        $filtered_input = ( isset( $_COOKIE[$key] ) ? filter_var( $_COOKIE[$key], $type ) : NULL );
    }

    return stripinput( $filtered_input );
}

/**
 * Remove a $_COOKIE
 *
 * @param string $key
 *
 * @return array
 */
function cookie_remove( $key ) {
    unset( $_COOKIE[$key] );
    return $_COOKIE;
}

/**
 * Cleans curent $_SESSION
 */
function session_clean() {
    $_SESSION = [];
}

/**
 * Add a value to $_SESSION
 *
 * @param string $key
 * @param mixed  $value
 *
 * @return mixed
 */
function session_add( $key, $value ) {
    //global $_SESSION;
    if ( is_array( $key ) ) {
        //  print_p($_SESSION);
        $session =& $_SESSION;
        foreach ( $key as $pkey ) {
            $session =& $session[$pkey];
        }
        $session = $value;

        return $session;
    }

    return $_SESSION[$key] = $value;
}

/**
 * Get session
 *
 * @param string|array $key
 *
 * @return mixed
 */
function session_get( $key ) {
    if ( is_array( $key ) ) {
        $session =& $_SESSION;
        foreach ( $key as $i ) {
            $session =& $session[$i];
        }

        return $session;
    }

    return ( isset( $_SESSION[$key] ) ? $_SESSION[$key] : NULL );
}

/**
 * @param string $key
 *
 * @return mixed
 */
function session_remove( $key ) {
    if ( is_array( $key ) ) {
        $temp = &$_SESSION;
        $counter = 1;
        foreach ( $key as $nkey ) {
            if ( $counter == count( $key ) ) {
                unset( $temp[$nkey] );
            }
            $temp = &$temp[$nkey];
            $counter++;
        }

        return $temp;
    }

    unset( $_SESSION[$key] );

    return $_SESSION;
}

/**
 * Converts an array/string to string
 *
 * @param string|array $value
 *
 * @return string
 */
function fusion_encode( $value ) {
    return Defender::encode( $value );
}

/**
 * Converts string to array/string
 *
 * @param string $value
 *
 * @return mixed
 */
function fusion_decode( $value ) {
    return Defender::decode( $value );
}

/**
 * Checks if fusion is safe to proceed next step
 *
 * @return bool
 */
function fusion_safe() {
    return Defender::getInstance()->safe();
}

/**
 * Declares FUSION_NULL constants to safeguard sensitive code execution.
 *
 * @param string $error_message The notification text. If present, will show a notice on page load.
 *
 * @return null
 */
function fusion_stop( $error_message = "" ) {
    return Defender::getInstance()->stop( $error_message );
}

/**
 * Decrypt a string
 *
 * @param string $value
 * @param string $password
 *
 * @return null|string
 */
function fusion_decrypt( $value, $password ) {
    return Defender::decrypt_string( $value, $password );
}

/**
 * Encrypts a string
 *
 * @param string $value
 * @param string $password
 *
 * @return string
 */
function fusion_encrypt( $value, $password ) {
    return Defender::encrypt_string( $value, $password );
}


/**
 * @param $input_name
 *
 * @return bool
 */
function input_has_error( $input_name ) {
    return Defender::inputHasError( $input_name );
}

/**
 * @param $input_name
 *
 * @return null
 */
function input_error_text( $input_name ) {
    return Defender::getErrorText( $input_name );
}

/**
 * @param        $input_name
 * @param string $error_text
 */
function set_input_error( $input_name, $error_text = '' ) {
    Defender::setInputError( $input_name );
    if ( !empty( $error_text ) ) {
        Defender::setErrorText( $input_name, $error_text );
    }
}

/**
 * @param $options
 *
 * @return array [error_class, error_text]
 */
function form_errors( $options ) {
    $locale = fusion_get_locale();

    $class = '';
    // Error messages based on settings
    $text = empty( $options['error_text'] ) ? $locale['error_input_default'] : $options['error_text'];
    if ( $options['template_type'] == 'text' ) {
        if ( $options['type'] == 'password' ) {
            $text = empty( $options['error_text'] ) ? $locale['error_input_password'] : $options['error_text'];
        } else if ( $options['type'] == 'email' ) {
            $text = empty( $options['error_text'] ) ? $locale['error_input_email'] : $options['error_text'];
        } else if ( $options['type'] == 'number' ) {
            $text = empty( $options['error_text'] ) ? $locale['error_input_number'] : $options['error_text'];
        } else if ( $options['type'] == 'url' ) {
            $text = empty( $options['error_text'] ) ? $locale['error_input_url'] : $options['error_text'];
        } else if ( $options['regex'] ) {
            $text = empty( $options['error_text'] ) ? $locale['error_input_regex'] : $options['error_text'];
        } else if ( $options['safemode'] ) {
            $text = empty( $options['error_text'] ) ? $locale['error_input_safemode'] : $options['error_text'];
        } else {
            $text = empty( $options['error_text'] ) ? $locale['error_input_default'] : $options['error_text'];
        }
    }

    if ( input_has_error( $options['input_name'] ) ) {
        $class = " has-error";
        if ( !empty( $options['error_text'] ) ) {
            $text = input_error_text( $options['input_name'] );
            if (!isset($options["add_error_notice"])) {
                debug_print_backtrace();
            }
            if ( $options['add_error_notice'] ) {
                addnotice( "danger", $text );
            }
        }
    }
    return [$class, $text];
}

/**
 * Fetches field configurations
 *
 * @param $field_name
 *
 * @return array|false|mixed|null
 */
function get_field_config( $field_name ) {
    return Defender::getInstance()->get_current_field_session( $field_name );
}

/**
 * Sets field configurations
 *
 * @param $field_config
 */
function set_field_config( $field_config ) {
    Defender::add_field_session( $field_config );
}


/**
 * Clean the URL and prevents entities in server globals.
 *
 * @param string $url URL.
 *
 * @return string $url clean and ready for use XHTML strict and without any dangerous code.
 */
function cleanurl($url) {

    $bad_entities = ["&", "\"", "'", '\"', "\'", "<", ">", "", "", "*"];
    $safe_entities = ["&amp;", "", "", "", "", "", "", "", "", ""];

    return str_replace($bad_entities, $safe_entities, $url);
}

/**
 * Prevents HTML in unwanted places
 *
 * @param string|array $text String or array to be stripped.
 *
 * @return array|string The given string decoded as non HTML text.
 */
function stripinput($text) {

    if (!is_array($text) && !is_null($text)) {
        return str_replace('\\', '&#092;', htmlspecialchars(stripslashes(trim($text)), ENT_QUOTES));
    }

    if (is_array($text) && !is_null($text)) {
        foreach ($text as $i => $item) {
            $text[$i] = stripinput($item);
        }
    }

    return $text;
}

/**
 * Prevent any possible XSS attacks via $_GET.
 *
 * @param array|string $check_url String or array to be stripped.
 *
 * @return bool True if the URL is not secure.
 */
function stripget($check_url) {

    if (is_array($check_url)) {
        foreach ($check_url as $value) {
            if (stripget($value) == TRUE) {
                return TRUE;
            }
        }
    } else {
        $check_url = str_replace(["\"", "\'"], ["", ""], urldecode($check_url));
        if (preg_match("/<[^<>]+>/i", $check_url)) {
            return TRUE;
        }
    }

    return FALSE;
}

/**
 * Strips a given filename from any unwanted characters and symbols.
 *
 * @param string $filename Filename you want to strip. Remember to remove the file extension before parsing it through this function.
 *
 * @return string The filename stripped and ready for use.
 */
function stripfilename($filename) {

    $patterns = ['/\s+/' => '_', '/[^a-z0-9_-]|^\W/i' => '', '/([_-])\1+/' => '$1'];

    return preg_replace(array_keys($patterns), $patterns, strtolower($filename)) ?: (string)time();
}


/**
 * Validate numeric input.
 *
 * @param mixed $value The value to be checked.
 * @param bool $decimal Decimals.
 * @param bool $negative Negative numbers.
 *
 * @return bool True if the value is a number.
 */
function isnum($value, $decimal = FALSE, $negative = FALSE) {

    if ($negative == TRUE) {
        return is_numeric($value);
    } else {
        $float = $decimal ? '(.{0,1})[0-9]*' : '';

        return !is_array($value) and preg_match("/^[0-9]+" . $float . "$/", $value);
    }
}


/**
 * Custom preg_match function.
 *
 * @param string $expression The expression to search for.
 * @param mixed $value The input string.
 *
 * @return bool FALSE when $value is an array
 */
function preg_check($expression, $value) {

    return !is_array($value) and preg_match($expression, $value);
}

/**
 * Sanitize text and remove a potentially dangerous HTML and JavaScript.
 *
 * @param string $text String to be sanitized.
 * @param bool $strip_tags Removes potentially dangerous HTML tags.
 * @param bool $strip_scripts Removes <script> tags.
 *
 * @return string|array Sanitized and safe string.
 */
function descript($text, $strip_tags = TRUE, $strip_scripts = TRUE) {

    if (is_array($text) || is_null($text)) {
        return $text;
    }

    $text = html_entity_decode($text, ENT_QUOTES, fusion_get_locale('charset'));
    $text = preg_replace('/&([a-z0-9]+|#[0-9]{1,6}|#x[0-9a-f]{1,6});/i', '', $text);

    // Convert problematic ascii characters to their true values
    $patterns = ['#(&\#x)([0-9A-F]+);*#si' => '', '#(/\bon\w+=\S+(?=.*>))#is' => '', '#([a-z]*)=([\`\'\"]*)script:#iU' => '$1=$2nojscript...', '#([a-z]*)=([\`\'\"]*)javascript:#iU' => '$1=$2nojavascript...', '#([a-z]*)=([\'\"]*)vbscript:#iU' => '$1=$2novbscript...', '#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU' => "$1>", '#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU' => "$1>"];

    foreach (array_merge(['(', ')', ':'], range('A', 'Z'), range('a', 'z')) as $chr) {
        $patterns["#(&\#)(0*" . ord($chr) . "+);*#si"] = $chr;
    }

    if ($strip_tags) {
        do {
            $count = 0;
            $text = preg_replace('#</*(applet|meta|xml|blink|link|style|script|object|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $text, -1, $count);
        } while ($count);
    }

    $text = preg_replace(array_keys($patterns), $patterns, $text);

    $preg_patterns = [// Fix &entity\n
        '!(&#0+[0-9]+)!' => '$1;', '/(&#*\w+)[\x00-\x20]+;/u' => '$1;>', '/(&#x*[0-9A-F]+);*/iu' => '$1;', // Remove any attribute starting with "on" or xml name space
        '#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu' => '$1>', // Remove any xss injected without a closing tag
        '#(<[^>]+?\s*[\x00-\x20"\'\\\\\/])((?:on|xmlns)+[=\w\d()]*+)#iu' => '$1>', // javascript: and VB script: protocols
        '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu' => '$1=$2nojavascript...', '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu' => '$1=$2novbscript...', '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u' => '$1=$2nomozbinding...', // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i' => '$1>', '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu' => '$1>', // namespace elements
        '#</*\w+:\w[^>]*+>#i' => '',
    ];

    if ($strip_scripts) {
        $preg_patterns += ['#<script(.*?)>(.*?)</script>#is' => ''];
    }

    foreach ($preg_patterns as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text);
    }

    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', FALSE);
}

/**
 * Prints human-readable information about a variable.
 *
 * @param mixed $data The expression to be printed.
 * @param bool $modal Dump info in the modal.
 * @param bool $print Dump info in <pre> tag.
 *
 * @return string The value of the variable.
 */
function print_p($data, $modal = FALSE, $print = TRUE) {

    ob_start();
    echo htmlspecialchars(print_r($data, TRUE), ENT_QUOTES, 'utf-8');
    $debug = ob_get_clean();
    if ($modal == TRUE) {
        $modal = openmodal('Debug', 'Debug');
        $modal .= "<pre class='printp' style='white-space:pre-wrap !important;'>";
        $modal .= $debug;
        $modal .= "</pre>\n";
        $modal .= closemodal();
        PHPFusion\OutputHandler::addToFooter($modal);

        return FALSE;
    }
    if ($print == TRUE) {
        echo "<pre class='printp' style='white-space:pre-wrap !important;'>";
        echo $debug;
        echo "</pre>\n";
    }

    return $debug;
}


/**
 * Replaces special characters in a string with their "non-special" counterpart.
 *
 * @param string $value String to normalize.
 *
 * @return string
 */
function normalize($value) {

    $table = [
        '&amp;' => 'and', '@' => 'at', '©' => 'c', '®' => 'r', 'À' => 'a', '(' => '', ')' => '', '.' => '', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae', 'Ç' => 'c', 'È' => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o', 'Ø' => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y', 'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'p', 'ÿ' => 'y', 'Ā' => 'a', 'ā' => 'a', 'Ă' => 'a', 'ă' => 'a', 'Ą' => 'a', 'ą' => 'a', 'Ć' => 'c', 'ć' => 'c', 'Ĉ' => 'c', 'ĉ' => 'c', 'Ċ' => 'c', 'ċ' => 'c', 'Č' => 'c', 'č' => 'c', 'Ď' => 'd', 'ď' => 'd', 'Đ' => 'd', 'đ' => 'd', 'Ē' => 'e', 'ē' => 'e', 'Ĕ' => 'e', 'ĕ' => 'e', 'Ė' => 'e', 'ė' => 'e', 'Ę' => 'e', 'ę' => 'e', 'Ě' => 'e', 'ě' => 'e', 'Ĝ' => 'g', 'ĝ' => 'g', 'Ğ' => 'g', 'ğ' => 'g', 'Ġ' => 'g', 'ġ' => 'g', 'Ģ' => 'g', 'ģ' => 'g', 'Ĥ' => 'h', 'ĥ' => 'h', 'Ħ' => 'h', 'ħ' => 'h', 'Ĩ' => 'i', 'ĩ' => 'i', 'Ī' => 'i', 'ī' => 'i', 'Ĭ' => 'i', 'ĭ' => 'i', 'Į' => 'i', 'į' => 'i', 'İ' => 'i', 'ı' => 'i', 'Ĳ' => 'ij', 'ĳ' => 'ij', 'Ĵ' => 'j', 'ĵ' => 'j', 'Ķ' => 'k', 'ķ' => 'k', 'ĸ' => 'k', 'Ĺ' => 'l', 'ĺ' => 'l', 'Ļ' => 'l', 'ļ' => 'l', 'Ľ' => 'l', 'ľ' => 'l', 'Ŀ' => 'l', 'ŀ' => 'l', 'Ł' => 'l', 'ł' => 'l', 'Ń' => 'n', 'ń' => 'n', 'Ņ' => 'n', 'ņ' => 'n', 'Ň' => 'n', 'ň' => 'n', 'ŉ' => 'n', 'Ŋ' => 'n', 'ŋ' => 'n', 'Ō' => 'o', 'ō' => 'o', 'Ŏ' => 'o', 'ŏ' => 'o', 'Ő' => 'o', 'ő' => 'o', 'Œ' => 'oe', 'œ' => 'oe', 'Ŕ' => 'r', 'ŕ' => 'r', 'Ŗ' => 'r', 'ŗ' => 'r', 'Ř' => 'r', 'ř' => 'r', 'Ś' => 's', 'ś' => 's', 'Ŝ' => 's', 'ŝ' => 's', 'Ş' => 's', 'ş' => 's', 'Š' => 's', 'š' => 's', 'Ţ' => 't', 'ţ' => 't', 'Ť' => 't', 'ť' => 't', 'Ŧ' => 't', 'ŧ' => 't', 'Ũ' => 'u', 'ũ' => 'u', 'Ū' => 'u', 'ū' => 'u', 'Ŭ' => 'u', 'ŭ' => 'u', 'Ů' => 'u', 'ů' => 'u', 'Ű' => 'u', 'ű' => 'u', 'Ų' => 'u', 'ų' => 'u', 'Ŵ' => 'w', 'ŵ' => 'w', 'Ŷ' => 'y', 'ŷ' => 'y', 'Ÿ' => 'y', 'Ź' => 'z', 'ź' => 'z', 'Ż' => 'z', 'ż' => 'z', 'Ž' => 'z', 'ž' => 'z', 'ſ' => 'z', 'Ə' => 'e', 'ƒ' => 'f', 'Ơ' => 'o', 'ơ' => 'o', 'Ư' => 'u', 'ư' => 'u', 'Ǎ' => 'a', 'ǎ' => 'a', 'Ǐ' => 'i', 'ǐ' => 'i', 'Ǒ' => 'o', 'ǒ' => 'o', 'Ǔ' => 'u', 'ǔ' => 'u', 'Ǖ' => 'u', 'ǖ' => 'u', 'Ǘ' => 'u', 'ǘ' => 'u', 'Ǚ' => 'u', 'ǚ' => 'u', 'Ǜ' => 'u', 'ǜ' => 'u', 'Ǻ' => 'a', 'ǻ' => 'a', 'Ǽ' => 'ae', 'ǽ' => 'ae', 'Ǿ' => 'o', 'ǿ' => 'o', 'ə' => 'e', 'Ё' => 'jo', 'Є' => 'e', 'І' => 'i', 'Ї' => 'i', 'А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ж' => 'zh', 'З' => 'z', 'И' => 'i', 'Й' => 'j', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n', 'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u', 'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'sch', 'Ъ' => '-', 'Ы' => 'y', 'Ь' => '-', 'Э' => 'je', 'Ю' => 'ju', 'Я' => 'ja', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '-', 'ы' => 'y', 'ь' => '-', 'э' => 'je', 'ю' => 'ju', 'я' => 'ja', 'ё' => 'jo', 'є' => 'e', 'і' => 'i', 'ї' => 'i', 'Ґ' => 'g', 'ґ' => 'g', 'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd', 'ה' => 'h', 'ו' => 'v', 'ז' => 'z', 'ח' => 'h', 'ט' => 't', 'י' => 'i', 'ך' => 'k', 'כ' => 'k', 'ל' => 'l', 'ם' => 'm', 'מ' => 'm', 'ן' => 'n', 'נ' => 'n', 'ס' => 's', 'ע' => 'e', 'ף' => 'p', 'פ' => 'p', 'ץ' => 'C', 'צ' => 'c', 'ק' => 'q', 'ר' => 'r', 'ש' => 'w', 'ת' => 't', '™' => 'tm', 'ء' => 'a', 'ا' => 'a', 'آ' => 'a', 'ب' => 'b', 'پ' => 'p', 'ت' => 't', 'ث' => 's', 'ج' => 'j', 'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ر' => 'r', 'ز' => 'z', 'ژ' => 'zh', 'س' => 's', 'ص' => 's', 'ض' => 'z', 'ط' => 't', 'ظ' => 'z', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q', 'ک' => 'k', 'گ' => 'g', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'و' => 'w', 'ه' => 'h', 'ی' => 'y ',
    ];

    return strtr($value, $table);
}

/**
 * Converts all applicable characters to HTML entities.
 * htmlentities is too agressive so we use this function.
 *
 * @param string $text The input string.
 *
 * @return string Encoded string.
 */
function phpentities($text) {

    return str_replace('\\', '&#092;', htmlspecialchars($text, ENT_QUOTES));
}


require_once __DIR__ . '/defender/validation.php';
require_once __DIR__ . '/defender/token.php';
require_once __DIR__ . '/defender/mimecheck.php';
