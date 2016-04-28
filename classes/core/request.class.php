<?php
/**
 * Created for phpFUS
 * User: Danny Hearnah
 * Author: ChubbyNinja
 *
 * Date: 2/25/2016
 * Time: 3:19 PM
 */

namespace phpFUS\Classes\Core;


class request
{

    static $element = '<div class="message-box %s"><p>%s</p></div>';

    function __construct()
    {

    }

    /**
     * @return string
     */
    public static function getElement()
    {
        return self::$element;
    }


    public static function get( $key, $default = NULL ) {

        if( array_key_exists( $key, $_GET ) ) {
            return self::parseValue($_GET[$key]);
        }

        return $default;

    }


    public static function post( $key, $default = NULL, $parse = true ) {

        if( array_key_exists( $key, $_POST ) ) {
            return ($parse) ? self::parseValue($_POST[$key]) : $_POST[$key];
        }

        return $default;

    }


    public static function cookie( $key, $default = NULL ) {

        if( array_key_exists( $key, $_COOKIE ) ) {
            return self::parseValue($_COOKIE[$key]);
        }

        return $default;

    }


    public static function file( $key, $default = NULL ) {

        if( array_key_exists( $key, $_FILES ) ) {
            return $_FILES[$key];
        }

        return $default;

    }


    private function parseValue( $value ) {
        return htmlentities( $value );
    }

}