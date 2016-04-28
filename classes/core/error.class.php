<?php
/**
 * Created for phpFUS
 * User: Danny Hearnah
 * Author: ChubbyNinja
 *
 * Date: 2/17/2016
 * Time: 8:30 AM
 */

namespace phpFUS\Classes\Core;


class Error
{

    public static function shutdown() {
        $error = error_get_last();

        // no error so just return
        if( $error == NULL ) { return; }

        if ( ( $error['type'] == 1 || $error['type'] == 4 ) ) {

            $msg = 'ERROR' . "\n" . '=======' . "\n";
            foreach ( $error as $key => $val ) {
                $msg .= $key . ': ' . $val . "\n";
            }

            $msg .= 'GET' . "\n" . '=======' . "\n";
            foreach ( $_GET as $key => $val ) {
                $msg .= $key . ': ' . $val . "\n";
            }

            $msg .= "\n\n" . 'POST' . "\n" . '=======' . "\n";
            foreach ( $_POST as $key => $val ) {
                $msg .= $key . ': ' . $val . "\n";
            }

            $msg .= "\n\n" . 'SERVER' . "\n" . '=======' . "\n";
            foreach ( $_SERVER as $key => $val ) {
                $msg .= $key . ': ' . $val . "\n";
            }

            print($msg);
        }
    }

    public static function customError( $title, $message, $die = false ) {

        printf('<h1>%s</h1>', $title);
        printf('<p>%s</p>', $message);


        if( $die ) {
            exit(0);
        }
    }

}