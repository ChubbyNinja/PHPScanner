<?php
/**
 * Created for phpFUS
 * User: Danny Hearnah
 * Author: ChubbyNinja
 *
 * Date: 2/25/2016
 * Time: 3:48 PM
 */

namespace phpFUS\Classes\Core;


class debug
{

    function __construct()
    {

    }

    public static function log( $message ) {

        if( DEBUG ) {
            $date = new \DateTime( );
            echo '[' . $date->format ( 'd-m-Y H:i:s' ) . '] ' . $message . "\n";
        }

    }

}