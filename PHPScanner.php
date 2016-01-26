<?php

/*
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: ChubbyNinja
 * URL: https://github.com/ChubbyNinja/PHPScanner
 */


    if( !isset($_SERVER['PHPSC_DISABLE'] ) ) {
        define('PHPSC_ROOT', rtrim(dirname(__FILE__), '/'));

        require PHPSC_ROOT.'/class/PHPScanner.php';
        $PHPScanner = new PHPScanner();
    }

