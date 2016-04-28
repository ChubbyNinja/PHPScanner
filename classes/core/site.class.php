<?php
/**
 * Created for phpFUS
 * User: Danny Hearnah
 * Author: ChubbyNinja
 *
 * Date: 2/25/2016
 * Time: 3:15 PM
 */

namespace phpFUS\Classes\Core;

use phpFUS\Classes\Library\Db;
use phpFUS\Classes\Library\Object;

class site
{

    static $methods = [];
    static $object = NULL;

    function __construct()
    {

    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return self::$methods;
    }

    /**
     * @param array $methods
     */
    public function setMethods($methods)
    {
        self::$methods = $methods;
    }



    public static function register( $method ) {

        self::setMethods( $method );

    }

    public static function load() {

        $method = self::parseMethod();

        Debug::log( 'Loading object');
        self::$object = new Object();
        self::$object->load();


        Debug::log( 'Loading module ' . $method );
        switch( $method ) {
            case 'template':
                Template::load();
                break;


        }

    }


    private function parseMethod() {

        if( empty( Request::get('method') ) ) {
            Error::customError('Method not loaded', 'No method set');
        }

        return Request::get('method');
    }


}