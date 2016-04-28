<?php
namespace phpFUS;

date_default_timezone_set('Europe/London');

define('BASE_PATH', realpath(dirname(__FILE__)) . '/');


require 'inc/config.php';
require 'inc/autoload.php';

use phpFUS\Classes\Core\Site;

register_shutdown_function( 'phpFUS\Classes\Core\Error::shutdown' );

Site::load();